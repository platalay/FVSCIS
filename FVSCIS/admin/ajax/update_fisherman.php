<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('../../../private/initialize.php');

header('Content-Type: application/json');

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (!isset($_POST['fisherman'])) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูล']);
    exit;
}

$data = $_POST['fisherman'];
$current_user_id = $_SESSION['user_id'] ?? null;

if (!empty($data['id'])) {
    $fisherman = Fisherman::find_by_id($data['id']);
    if (!$fisherman) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบชาวประมง']);
        exit;
    }
    $old_is_approved = $fisherman->is_approved;
} else {
    $fisherman = new Fisherman();
    $old_is_approved = 0;
}

// อัปเดตข้อมูล
$fisherman->full_name = $data['full_name'] ?? '';
$fisherman->email = $data['email'] ?? '';
$fisherman->citizen_id = $data['citizen_id'] ?? '';
$fisherman->is_active = $data['is_active'] ?? 0;
$fisherman->is_approved = $data['is_approved'] ?? 0;
$fisherman->updated_by = $current_user_id;
$fisherman->updated_at = date('Y-m-d H:i:s');

if (empty($data['id'])) {
    $fisherman->created_by = $current_user_id;
    $fisherman->created_at = date('Y-m-d H:i:s');
}

// ✅ จัดการสถานะอนุมัติ
if ($old_is_approved == 0 && $fisherman->is_approved == 1) {
    $fisherman->approved_by = $current_user_id;
    $fisherman->approved_at = date('Y-m-d H:i:s');
} elseif ($fisherman->is_approved == 0) {
    $fisherman->approved_by = null;
    $fisherman->approved_at = null;
}

// บันทึก
if ($fisherman->save()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดขณะบันทึก']);
}
