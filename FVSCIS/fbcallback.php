<?php
require_once('../private/initialize.php');

$fb_app_id = FB_APP_ID;
$fb_app_secret = FB_APP_SECRET;
$redirect_uri = FB_REDIRECT_URI;

$session = new Session();

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // ขอ access token
    $token_url = 'https://graph.facebook.com/v19.0/oauth/access_token?' . http_build_query([
        'client_id' => $fb_app_id,
        'redirect_uri' => $redirect_uri,
        'client_secret' => $fb_app_secret,
        'code' => $code
    ]);

    $token_data = json_decode(file_get_contents($token_url), true);
    $access_token = $token_data['access_token'] ?? null;

    if ($access_token) {
        $user_info = file_get_contents('https://graph.facebook.com/me?fields=id,name,email,picture&access_token=' . $access_token);
        $user = json_decode($user_info, true);

        // เตรียมข้อมูลสำหรับระบบ
        $facebook_id = htmlspecialchars($user['id']);
        $username = 'facebook_' . $facebook_id;
        $picture = htmlspecialchars($user['picture']['data']['url'] ?? '');
        $email = htmlspecialchars($user['email'] ?? '');

        // ตรวจสอบ Officer
        $Officer = Officer::find_by_username($username);
        if ($Officer) {
            if ($Officer->is_approved) {
                $role = Session::map_usertype_id_to_role($Officer->usertype_id);
                if ($role === 'unknown') {
                    Officer::alert_and_redirect(
                        'ไม่สามารถเข้าสู่ระบบ',
                        'สิทธิ์ของคุณไม่ถูกต้อง',
                        'login.php'
                    );
                }

                $session->login($Officer, $role, $picture);
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
            // ตรวจสอบ Fisherman
            $fisherman = Fisherman::find_by_username($username);
            if ($fisherman) {
                if ($fisherman->is_approved) {
                    $session->login($fisherman, 'fisherman', $picture);
                    Session::redirect_by_role('fisherman');
                } else {
                    Fisherman::alert_and_redirect(
                        'รอการอนุมัติ',
                        'คุณขอเข้าใช้ระบบในฐานะชาวประมง กรุณารอการอนุมัติจากเจ้าหน้าที่',
                        'login.php'
                    );
                }
            } else {
                redirect_to('logins2.php');
            }
        }

    } else {
        echo "ไม่สามารถดึง access token ได้จาก Facebook";
    }

} else {
    echo "ไม่ได้รับ code กลับมาจาก Facebook";
}
?>
