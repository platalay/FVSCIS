<?php
require_once('../../../private/initialize.php');

header('Content-Type: application/json');

// ตรวจสอบว่าได้รับข้อมูล Officer มาหรือไม่
if (!isset($_POST['Officer'])) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูล']);
    exit;
}

$data = $_POST['Officer'];
$current_user_id = $_SESSION['user_id'] ?? null;

// ถ้ามี id ให้โหลดข้อมูล Officer เดิม
if (!empty($data['id'])) {
    $officer = Officer::find_by_id($data['id']);
    if (!$officer) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบเจ้าหน้าที่']);
        exit;
    }
    $old_is_approved = $officer->is_approved; // จดจำสถานะเดิมไว้เปรียบเทียบ
} else {
    $officer = new Officer();
    $old_is_approved = 0; // ไม่มีข้อมูลเดิมถือว่าเดิมเป็น 0
}

// อัปเดตข้อมูลจากฟอร์ม
$officer->username = $data['username'] ?? '';
$officer->password = $data['password'] ?? '';
$officer->full_name = $data['full_name'] ?? '';
$officer->position = $data['position'] ?? '';
$officer->email = $data['email'] ?? '';
$officer->google_id = $data['google_id'] ?? '';
$officer->facebook_id = $data['facebook_id'] ?? '';
$officer->line_id = $data['line_id'] ?? '';
$officer->is_active = $data['is_active'] ?? 1;
$officer->is_approved = $data['is_approved'] ?? 0;
$officer->departments_id = $data['departments_id'] ?? null;
$officer->usertype_id = $data['usertype_id'] ?? null;
$officer->updated_by = $current_user_id;

if (empty($data['id'])) {
    $officer->created_by = $current_user_id;
}

// ✅ ตรวจสอบสถานะการอนุมัติ
if ($old_is_approved == 0 && $officer->is_approved == 1) {
    $officer->approved_by = $current_user_id;
    $officer->approved_at = date('Y-m-d H:i:s');
} elseif ($officer->is_approved == 0) {
    $officer->approved_by = null;
    $officer->approved_at = null;
}

// บันทึกลงฐานข้อมูล
if ($officer->save()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดขณะบันทึก']);
}
