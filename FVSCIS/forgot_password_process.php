<?php
require_once('../private/initialize.php');

// รับค่าจาก POST
$user_type = $_POST['user_type'] ?? '';
$email = trim($_POST['email'] ?? '');
$citizen_id = trim($_POST['citizen_id'] ?? '');

// ตรวจสอบความถูกต้อง
if ($user_type === 'officer' && !empty($email)) {
    $officer = Officer::find_by_email($email);
    if ($officer && $officer->is_approved) {
        // สร้างรหัสผ่านใหม่
        $new_password = bin2hex(random_bytes(4)); // เช่น 'a8b4d2e3'
        $officer->password = password_hash($new_password, PASSWORD_DEFAULT);
        $officer->save();

        // ส่งกลับข้อความให้แสดงบน modal
        echo json_encode([
            'status' => 'success',
            'message' => "รหัสผ่านใหม่ของคุณคือ: <strong>{$new_password}</strong><br>กรุณาเข้าสู่ระบบและเปลี่ยนรหัสผ่านทันที"
        ]);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบอีเมลในระบบหรือยังไม่ได้อนุมัติ']);
        exit;
    }

} elseif ($user_type === 'fisherman' && !empty($citizen_id)) {
    $fisherman = Fisherman::find_by_username($citizen_id);
    if ($fisherman && $fisherman->is_approved) {
        $new_password = bin2hex(random_bytes(4));
        $fisherman->password = password_hash($new_password, PASSWORD_DEFAULT);
        $fisherman->save();

        echo json_encode([
            'status' => 'success',
            'message' => "รหัสผ่านใหม่ของคุณคือ: <strong>{$new_password}</strong><br>กรุณาเข้าสู่ระบบและเปลี่ยนรหัสผ่านทันที"
        ]);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบหมายเลขบัตรในระบบหรือยังไม่ได้อนุมัติ']);
        exit;
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    exit;
}
