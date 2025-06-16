<?php
require_once('../../../private/initialize.php');

header('Content-Type: application/json');

// ตรวจสอบว่ามี id ถูกส่งมาหรือไม่
if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูล ID']);
    exit;
}

$id = $_POST['id'];
$fisherman = Fisherman::find_by_id($id);

// ถ้าไม่เจอ
if (!$fisherman) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลชาวประมงในระบบ']);
    exit;
}

// ลบ
if ($fisherman->delete()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'ลบข้อมูลไม่สำเร็จ']);
}
