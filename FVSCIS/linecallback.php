<?php
require_once('../private/initialize.php');
// เปิด error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ตั้งค่าคงที่
$channel_id = '2007374384';
$channel_secret = 'c528c7071c8f8991f68102cb1e8687ae';
$redirect_uri = 'https://fishlanding.fisheries.go.th/FVSCIS/linecallback.php';

// ตรวจสอบว่าได้รับ code กลับมาหรือไม่
if (!isset($_GET['code'])) {
    exit('No code parameter provided');
}

$code = $_GET['code'];
$state = $_GET['state'] ?? ''; // ควรเก็บไว้และตรวจสอบกับ session ในระบบจริง

// ขอ access token
$token_url = 'https://api.line.me/oauth2/v2.1/token';
$data = [
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $redirect_uri,
    'client_id' => $channel_id,
    'client_secret' => $channel_secret
];

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
    exit;
}
curl_close($ch);

$token_data = json_decode($response, true);

if (!isset($token_data['access_token'])) {
    echo '<pre>';
    print_r($token_data);
    echo '</pre>';
    exit('ไม่สามารถรับ access token ได้');
}

$access_token = $token_data['access_token'];

// 🔄 ดึงข้อมูลจาก OpenID endpoint เพื่อให้ได้ email
$userinfo_url = 'https://api.line.me/oauth2/v2.1/userinfo';
$ch = curl_init($userinfo_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token
]);
$response = curl_exec($ch);
curl_close($ch);

$user_info = json_decode($response, true);

// ตรวจสอบข้อมูลที่ได้รับ
if (!isset($user_info['sub'])) {
    echo '<pre>';
    print_r($user_info);
    echo '</pre>';
    exit('ไม่สามารถดึงข้อมูลผู้ใช้จาก LINE ได้');
}

// สร้าง SESSION
session_start();
$_SESSION['user_id'] = htmlspecialchars($user_info['sub']); // ใช้ 'sub' แทน userId
$_SESSION['username'] = htmlspecialchars("line_".$user_info['sub']);
$_SESSION['user_picture'] = htmlspecialchars($user_info['picture'] ?? '');
$_SESSION['email'] = htmlspecialchars($user_info['email'] ?? '');
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
                if ((int)$Officer->departments_id == 38 || (int)$Officer->usertype_id == 6) {
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
?>
