<?php
require_once('../private/initialize.php');

$channel_id     = LINE_LOGIN_CHANNEL_ID;
$channel_secret = LINE_LOGIN_CHANNEL_SECRET;
$redirect_uri   = LINE_LOGIN_CALLBACK_URL;

$session = new Session();

// ==========================
//   รับ code จาก LINE
// ==========================
if (!isset($_GET['code'])) {
    exit('ไม่ได้รับ code จาก LINE');
}

$code = $_GET['code'];

// 1) ขอ access token
$token_url = 'https://api.line.me/oauth2/v2.1/token';
$data = [
    'grant_type'    => 'authorization_code',
    'code'          => $code,
    'redirect_uri'  => $redirect_uri,
    'client_id'     => $channel_id,
    'client_secret' => $channel_secret
];

$ch = curl_init($token_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($data),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded']
]);
$response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($response, true);

if (!isset($token_data['access_token'])) {
    echo '<pre>'; print_r($token_data); echo '</pre>';
    exit('ไม่สามารถรับ access token ได้จาก LINE');
}

$access_token = $token_data['access_token'];

// 2) ขอ user info
$userinfo_url = "https://api.line.me/oauth2/v2.1/userinfo";
$ch = curl_init($userinfo_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ["Authorization: Bearer $access_token"]
]);
$response = curl_exec($ch);
curl_close($ch);

$user_info = json_decode($response, true);

// ตรวจ sub
if (!isset($user_info['sub'])) {
    echo '<pre>'; print_r($user_info); echo '</pre>';
    exit('ไม่สามารถดึงข้อมูลผู้ใช้จาก LINE ได้');
}

// ==========================
//   เตรียมข้อมูล
// ==========================

$line_id  = htmlspecialchars($user_info['sub']);
$username = 'line_' . $line_id;
$email    = htmlspecialchars($user_info['email'] ?? '');
$picture  = htmlspecialchars($user_info['picture'] ?? ''); // << เก็บ URL ได้เลย

// เก็บชั่วคราว
$_SESSION['social_tmp'] = [
    'email_tmp'     => $email,
    'username_tmp'  => $username,
    'user_id_tmp'   => $line_id,
    'picture_tmp'   => $picture,
    'created_at'    => time(),
    'expires_at'    => time() + 10*60
];

// ==========================
//   เช็ค Officer
// ==========================

$Officer = Officer::find_by_username($username);

if ($Officer) {

    if ($Officer->is_approved) {

        // บันทึก URL รูปลง DB ถ้ายังไม่มี
        if (empty($Officer->profile_image) && !empty($picture)) {
            $Officer->profile_image = $picture;
            $Officer->save();
        }

        $role = Session::map_usertype_id_to_role($Officer->usertype_id);
        $session->login($Officer, $role, $Officer->profile_image);
        Session::redirect_by_role($role);

    } else {
        Officer::alert_and_redirect(
            'รอการอนุมัติ',
            'บัญชีของคุณยังไม่พร้อมใช้งาน',
            'login.php'
        );
    }

} else {

    // ==========================
    //   เช็ค Fisherman
    // ==========================

    $fisherman = Fisherman::find_by_username($username);

    if ($fisherman) {

        if ($fisherman->is_approved) {

            if (empty($fisherman->profile_image) && !empty($picture)) {
                $fisherman->profile_image = $picture;
                $fisherman->save();
            }

            $session->login($fisherman, 'fisherman', $fisherman->profile_image);
            Session::redirect_by_role('fisherman');

        } else {
            Fisherman::alert_and_redirect(
                'รอการอนุมัติ',
                'บัญชีของคุณยังไม่พร้อมใช้งาน',
                'login.php'
            );
        }

    } else {
        // ยังไม่เคยลงทะเบียน
        redirect_to('logins2.php');
    }
}
?>
