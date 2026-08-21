<?php
require_once('../private/initialize.php');
// $session ถูกสร้างใน initialize.php อยู่แล้วตามโครงระบบคุณ

$fb_app_id     = FB_APP_ID;
$fb_app_secret = FB_APP_SECRET;
$redirect_uri  = FB_REDIRECT_URI;

/**
 * ดึงรูปจาก Social แล้วบันทึกเป็นไฟล์ใน uploads/profile
 * และอัปเดต field profile_image ในโมเดล (Officer หรือ Fisherman)
 */
function save_social_profile_image($userModel, string $picture_url): void {
    if (empty($picture_url) || !$userModel) {
        return;
    }

    // ถ้ามีรูปอยู่แล้ว ไม่ต้องทับ (user อาจอัปโหลดเองทีหลัง)
    if (!empty($userModel->profile_image ?? null)) {
        return;
    }

    // เตรียมโฟลเดอร์เก็บรูป
    $upload_dir = 'uploads/profile/'; // ../uploads/profile จากโฟลเดอร์ public
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // หา extension จาก URL
    $path = parse_url($picture_url, PHP_URL_PATH);
    $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png'])) {
        $ext = 'jpg';
    }

    $prefix   = strtolower((new ReflectionClass($userModel))->getShortName()); // officer / fisherman
    $filename = $prefix . '_' . $userModel->id . '_' . time() . '.' . $ext;
    $target   = $upload_dir . $filename;

    // โหลดรูปด้วย cURL (ให้ไปใช้ค่า CA จาก php.ini)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $picture_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $imageData = curl_exec($ch);
    if (curl_errno($ch) || $imageData === false) {
        curl_close($ch);
        return; // ถ้าดึงรูปไม่สำเร็จ ก็ไม่ต้องทำอะไรต่อ
    }
    curl_close($ch);

    // บันทึกไฟล์
    file_put_contents($target, $imageData);

    // อัปเดตชื่อไฟล์ใน DB
    $userModel->profile_image = $filename;
    $userModel->save();
}

if (isset($_GET['code'])) {

    $code = $_GET['code'];

    $token_url = 'https://graph.facebook.com/v19.0/oauth/access_token?' . http_build_query([
        'client_id'     => $fb_app_id,
        'redirect_uri'  => $redirect_uri,
        'client_secret' => $fb_app_secret,
        'code'          => $code
    ]);

    // ขอ access token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $token_response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
        exit;
    }
    curl_close($ch);

    $token_data   = json_decode($token_response, true);
    $access_token = $token_data['access_token'] ?? null;

    if (!$access_token) {
        echo "ไม่สามารถดึง access token ได้จาก Facebook";
        exit;
    }

    // ขอข้อมูลผู้ใช้จาก Facebook
    $user_info_url = 'https://graph.facebook.com/me?fields=id,name,email,picture.type(large){url}&access_token=' . $access_token;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $user_info_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $user_info = curl_exec($ch);
    curl_close($ch);

    $user_fb = json_decode($user_info, true);

    $facebook_id = $user_fb['id'] ?? '';
    $username    = 'facebook_' . $facebook_id;
    $email       = $user_fb['email'] ?? '';

    // URL รูปจาก Facebook (ใช้ raw URL เพื่อโหลดไฟล์)
    $picture_url = $user_fb['picture']['data']['url'] ?? '';

    // เก็บข้อมูลชั่วคราวไว้ใช้ใน logins2.php
    $_SESSION['social_tmp'] = [
        'email_tmp'    => $email,
        'username_tmp' => $username,
        'user_id_tmp'  => $facebook_id, // หรือ google_id/line_id
        'created_at'   => time(),
        'expires_at'   => time() + 10*60, // อายุ 10 นาที
    ];

    // 1) ลองหา Officer ก่อน
    $Officer = Officer::find_by_username($username);
    if ($Officer) {

        if ($Officer->is_approved) {

            // ✅ ดึงรูปจาก Facebook มาบันทึกไว้ (ถ้ายังไม่มี profile_image)
            save_social_profile_image($Officer, $picture_url);

            $role = Session::map_usertype_id_to_role($Officer->usertype_id);
            if ($role === 'unknown') {
                Officer::alert_and_redirect(
                    'ไม่สามารถเข้าสู่ระบบ',
                    'สิทธิ์ของคุณไม่ถูกต้อง',
                    'login.php'
                );
            }

            // ไม่จำเป็นต้องส่ง $picture แล้ว ให้ Session/Officer จัดการเองจาก profile_image
            $session->login($Officer, $role);
            Session::redirect_by_role($role);

        } else {
            if ((int)$Officer->departments_id === 38 || (int)$Officer->usertype_id === 6 || $Officer->position === '') {
                Officer::alert_and_redirect(
                    'บัญชียังกรอกข้อมูลไม่ครบ',
                    'กรุณาตรวจสอบ และรอการอนุมัติจากเจ้าหน้าที่',
                    'logins2.php'
                );
            } else {
                Officer::alert_and_redirect(
                    'รอการอนุมัติ',
                    'กรุณารอการอนุมัติจากเจ้าหน้าที่',
                    'login.php'
                );
            }
        }

    } else {

        // 2) ถ้าไม่ใช่ Officer ลองหา Fisherman
        $fisherman = Fisherman::find_by_username($username);
        if ($fisherman) {

            if ($fisherman->is_approved) {

                // ✅ ดึงรูปจาก Facebook มาบันทึกไว้ (ถ้ายังไม่มี profile_image)
                save_social_profile_image($fisherman, $picture_url);

                $session->login($fisherman, 'fisherman');
                Session::redirect_by_role('fisherman');

            } else {
                Fisherman::alert_and_redirect(
                    'รอการอนุมัติ',
                    'คุณขอเข้าใช้ระบบในฐานะชาวประมง กรุณารอการอนุมัติจากเจ้าหน้าที่',
                    'login.php'
                );
            }

        } else {
            // ยังไม่มี user ในระบบ → ไปหน้าเก็บข้อมูลเพิ่ม
            redirect_to('logins2.php');
        }
    }

} else {
    echo "ไม่ได้รับ code กลับมาจาก Facebook";
}
?>
