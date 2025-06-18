<?php
require_once('../private/initialize.php');

$channel_id = '2007374384';
$channel_secret = 'c528c7071c8f8991f68102cb1e8687ae';
$redirect_uri = 'https://fishlanding.fisheries.go.th/FVSCIS/linecallback.php';

$session = new Session();

// ✅ ตรวจสอบว่าได้รับ code หรือไม่
if (!isset($_GET['code'])) {
    exit('ไม่ได้รับ code จาก LINE');
}

$code = $_GET['code'];

// ✅ ขอ access token
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
    exit('ไม่สามารถรับ access token ได้จาก LINE');
}

$access_token = $token_data['access_token'];

// ✅ ขอข้อมูลผู้ใช้จาก LINE
$userinfo_url = 'https://api.line.me/oauth2/v2.1/userinfo';
$ch = curl_init($userinfo_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token
]);
$response = curl_exec($ch);
curl_close($ch);

$user_info = json_decode($response, true);

// ✅ ตรวจสอบข้อมูลผู้ใช้
if (!isset($user_info['sub'])) {
    echo '<pre>';
    print_r($user_info);
    echo '</pre>';
    exit('ไม่สามารถดึงข้อมูลผู้ใช้จาก LINE ได้');
}

// เตรียมข้อมูลเข้าสู่ระบบ
$line_id = htmlspecialchars($user_info['sub']);
$username = 'line_' . $line_id;
$picture = htmlspecialchars($user_info['picture'] ?? '');
$email = htmlspecialchars($user_info['email'] ?? '');

// ✅ ตรวจสอบ Officer
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
    // ✅ ตรวจสอบ Fisherman
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
?>
