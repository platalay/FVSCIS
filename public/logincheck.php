<?php
require_once('../private/initialize.php');

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']);

// ตรวจสอบ Officer ก่อน
$Officer = Officer::find_by_username($username);
if ($Officer && password_verify($password, $Officer->password)) {

    if ($Officer->is_approved) {
        $role = Session::map_usertype_id_to_role($Officer->usertype_id);

        if ($role === 'unknown') {
            Officer::alert_and_redirect(
                'ไม่สามารถเข้าสู่ระบบ',
                'สิทธิ์ของคุณไม่ถูกต้อง',
                'login.php'
            );
        }

        $session->login($Officer, $role, '' , $remember_me);

        if ($remember_me) {
            $token = bin2hex(random_bytes(32));
            $expiry = time() + (30 * 24 * 60 * 60); // 30 วัน

            $Officer->login_token = $token;
            $Officer->token_expiry = date('Y-m-d H:i:s', $expiry);
            $Officer->save();
        }

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
                'คุณขอเข้าใช้ระบบในฐานะเจ้าหน้าที่ กรุณารอการอนุมัติจากผู้ดูแลระบบ',
                'login.php'
            );
        }
    }
    exit;
}

// ตรวจสอบ Fisherman
$fisherman = Fisherman::find_by_username($username);
if ($fisherman && password_verify($password, $fisherman->password)) {
    if ($fisherman->is_approved) {
        $session->login($fisherman, 'fisherman', '' , $remember_me);
        if ($remember_me) {
            $token = bin2hex(random_bytes(32));
            $expiry = time() + (30 * 24 * 60 * 60); // 30 วัน

            $fisherman->login_token = $token;
            $fisherman->token_expiry = date('Y-m-d H:i:s', $expiry);
            $fisherman->save();
        }

        Session::redirect_by_role('fisherman');
    } else {
        Fisherman::alert_and_redirect(
            'รอการอนุมัติ',
            'คุณขอเข้าใช้ระบบในฐานะชาวประมง กรุณารอการอนุมัติจากเจ้าหน้าที่',
            'login.php'
        );
    }
    exit;
}

$session->message('ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');
redirect_to('login.php');
