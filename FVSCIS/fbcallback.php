<?php
require_once('../private/initialize.php');

$fb_app_id = FB_APP_ID;
$fb_app_secret = FB_APP_SECRET;
$redirect_uri = FB_REDIRECT_URI;

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // ✅ สร้าง URL ที่ถูกต้องเพื่อขอ access token
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
        $_SESSION['user_id'] = htmlspecialchars($user['id']);
        $_SESSION['user_picture'] = htmlspecialchars($user['picture']['data']['url']);
        $_SESSION['username'] = htmlspecialchars("facebook_".$user['id']);
        $email = isset($user['email']) ? htmlspecialchars($user['email']) : '';
        $_SESSION['email'] = $email;
         $Officer = Officer::find_by_username($_SESSION['username']);
        if ($Officer) {
            if ($Officer->is_approved) {
                switch ((int)$Officer->usertype_id) {
                    case 1:
                        redirect_to('admin/index.php');
                        break;
                    case 2:
                        redirect_to('officer/index.php');
                        break;
                    case 3:
                        redirect_to('headquarter/index.php');
                        break;
                    case 5:
                        redirect_to('authorize/index.php');
                        break;
                    default:
                        Officer::alert_and_redirect(
                            'ไม่สามารถเข้าสู่ระบบ',
                            'สิทธิ์ของคุณไม่ถูกต้อง',
                            'login.php'
                        );
                }
            } else {
                if ((int)$Officer->departments_id == 38 || (int)$Officer->usertype_id == 6 || $Officer->position == '') {
                    Officer::alert_and_redirect(
                        'บัญชียังกรอกข้อมูลไม่ครบ',
                        'กรุณาตรวจสอบ และรอการอนุมัติจากเจ้าหน้าที่',
                        'register_officer.php'
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
            $fisherman = Fisherman::find_by_username($_SESSION['username']);
            if ($fisherman) {
                if ($fisherman->is_approved) {
                    redirect_to('Fisherman/index.php');
                } else {
                    Fisherman::alert_and_redirect(
                        'ไม่พบบัญชีผู้ใช้งาน',
                        'กรุณากรอกหมายเลขบัตรประชาชน และรอการอนุมัติจากเจ้าหน้าที่',
                        'register_fisherman.php'
                    );
                }
            } else {
                redirect_to('logins2.php');
            }
        }

        
    } else {
        echo "ไม่สามารถดึง access token ได้";
    }
} else {
    echo "ไม่ได้รับ code กลับมาจาก Facebook";
}

?>
