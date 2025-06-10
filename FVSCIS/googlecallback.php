<?php
require_once('../private/initialize.php');
$client_id = GOOGLE_CLIENT_ID;
$client_secret = GOOGLE_CLIENT_SECRET;
$redirect_uri = GOOGLE_LOGIN_CALLBACK_URL;

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    $token_url = 'https://oauth2.googleapis.com/token';

    $post_fields = [
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code',
    ];

    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['id_token'])) {
        $id_token = $data['id_token'];
        $payload = explode('.', $id_token)[1] ?? '';
        $userinfo = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

        $_SESSION['user_id'] = htmlspecialchars($userinfo['sub']);
        $_SESSION['user_picture'] = htmlspecialchars($userinfo['picture']);
        $_SESSION['username'] = htmlspecialchars('google_' . $userinfo['sub']);
        $_SESSION['email'] = htmlspecialchars($userinfo['email'] ?? '');

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
        $session->message('ไม่สามารถรับ token ได้<br>');
    }
} else {
    echo "ไม่ได้รับ code จาก Google";
}
?>
