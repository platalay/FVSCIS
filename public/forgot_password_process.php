<?php
require_once('../private/initialize.php');

header('Content-Type: application/json');

$user_type = $_POST['user_type'] ?? '';
$email = $_POST['email'] ?? '';
$citizen_id = $_POST['citizen_id'] ?? '';

if ($user_type === 'officer' && !empty($email)) {
    $officer = Officer::find_by_email($email);
    if ($officer) {
        // สร้างรหัสใหม่
        $new_password = bin2hex(random_bytes(4));
        $officer->set_hashed_password($new_password);
        $officer->save();

        // ส่งอีเมลหรือแสดงผล
        echo json_encode([
            'status' => 'success',
            'message' => 'รหัสผ่านใหม่ของคุณคือ: <strong>' . $new_password . '</strong>'
        ]);
        exit;
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'ไม่พบบัญชีที่มีอีเมลนี้ในระบบ'
        ]);
        exit;
    }

} elseif ($user_type === 'fisherman' && !empty($citizen_id)) {
    $fisherman = Fisherman::find_by_citizen_id($citizen_id);
    if ($fisherman) {
        $new_password = bin2hex(random_bytes(4));
        $fisherman->set_hashed_password($new_password);
        $fisherman->save();

        echo json_encode([
            'status' => 'success',
            'message' => 'รหัสผ่านใหม่ของคุณคือ: <strong>' . $new_password . '</strong>'
        ]);
        exit;
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'ไม่พบบัญชีที่มีหมายเลขบัตรนี้ในระบบ'
        ]);
        exit;
    }

} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'ข้อมูลไม่ครบถ้วนหรือประเภทผู้ใช้ไม่ถูกต้อง'
    ]);
    exit;
}
?>
