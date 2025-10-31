<?php
require_once('../private/initialize.php');

$client_id = GOOGLE_CLIENT_ID;
$client_secret = GOOGLE_CLIENT_SECRET;
$redirect_uri = GOOGLE_LOGIN_CALLBACK_URL;

$session = new Session();

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
        // Decode JWT payload
        $payload = explode('.', $data['id_token'])[1] ?? '';
        $userinfo = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

        // เตรียมข้อมูล
        $google_id = htmlspecialchars($userinfo['sub']);
        $username = 'google_' . $google_id;
        $picture = htmlspecialchars($userinfo['picture'] ?? '');
        $email = htmlspecialchars($userinfo['email'] ?? '');
        $_SESSION['social_tmp'] = [
        'email_tmp'       => $email,
        'username_tmp'    => $username,
        'user_id_tmp' => $google_id, // หรือ google_id/line_id
        'created_at'  => time(),
        'expires_at'  => time() + 10*60, // อายุ 10 นาที
        ];
        
        // ตรวจสอบ Officer ก่อน
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

                // Login แล้ว redirect
                $session->login($Officer, $role, $picture);
                Session::redirect_by_role($role);

            } else {
                // ยังไม่อนุมัติ
                if ((int)$Officer->departments_id === 38 || (int)$Officer->usertype_id === 6 || $Officer->position === '') {
                    Officer::alert_and_redirect(
                        'บัญชียังกรอกข้อมูลไม่ครบ',
                        'กรุณาตรวจสอบ และรอการอนุมัติจากเจ้าหน้าที่',
                        'logins2.php'
                    );
                } else {
                    Officer::alert_and_redirect(
                        'รอการอนุมัติ',
                        'คุณขอเข้าใช้ระบบในฐานะเจ้าหน้าที่ กรุณารอการอนุมัติจากผู้ดูแลระบบ',
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
                // ยังไม่เคยลงทะเบียน
                redirect_to('logins2.php');
            }
        }

    } else {
        $session->message('ไม่สามารถรับ token ได้จาก Google');
        redirect_to('login.php');
    }

} else {
    echo "ไม่ได้รับ code จาก Google";
}

