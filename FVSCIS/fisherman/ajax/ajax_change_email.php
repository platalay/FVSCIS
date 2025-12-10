<?php
require_once('../../../private/initialize.php');

header('Content-Type: application/json; charset=utf-8');

$response = [
    'success' => false,
    'message' => ''
];

try {

    // 1) ตรวจสิทธิ์ผู้ใช้งาน
    $session->require_role(['fisherman']);

    if (!$session->is_logged_in()) {
        throw new Exception('กรุณาเข้าสู่ระบบก่อน');
    }

    // 2) ดึงข้อมูล fisherman ปัจจุบัน
    $fisherman = Fisherman::find_by_id($session->user_id());
    if (!$fisherman) {
        throw new Exception('ไม่พบข้อมูลผู้ใช้งาน');
    }

    // 3) รับค่าจากฟอร์ม
    $new_email        = trim($_POST['new_email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';

    // 4) ตรวจความครบถ้วน
    if ($new_email === '' || $current_password === '') {
        throw new Exception('กรุณากรอกข้อมูลให้ครบถ้วน');
    }

    // 5) ตรวจรูปแบบอีเมล
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('รูปแบบอีเมลไม่ถูกต้อง');
    }

    // 6) ตรวจรหัสผ่านปัจจุบัน
    if (!$fisherman->verify_password($current_password)) {
        throw new Exception('รหัสผ่านปัจจุบันไม่ถูกต้อง');
    }

    // 7) เช็คว่าอีเมลใหม่ซ้ำคนอื่นหรือไม่
    if ($new_email !== $fisherman->email && Fisherman::exists_email($new_email)) {
        throw new Exception('อีเมลนี้ถูกใช้งานแล้ว');
    }

    // 8) เช็คว่าไม่ใช่อีเมลเดิม
    if (strcasecmp($new_email, $fisherman->email) === 0) {
        throw new Exception('อีเมลใหม่ต้องไม่ตรงกับอีเมลเดิม');
    }

    // 9) บันทึกอีเมลใหม่
    $fisherman->email = $new_email;

    if (!$fisherman->save()) {
        throw new Exception('ไม่สามารถบันทึกอีเมลใหม่ได้');
    }

    $response['success']   = true;
    $response['message']   = 'เปลี่ยนอีเมลเรียบร้อยแล้ว';
    $response['new_email'] = $new_email;

} catch (Exception $e) {

    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
