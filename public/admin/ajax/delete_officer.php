<?php
require_once('../../../private/initialize.php');

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูล ID']);
    exit;
}

$id = $_POST['id'];
$officer = Officer::find_by_id($id);

if (!$officer) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบเจ้าหน้าที่ในระบบ']);
    exit;
}

if ($officer->delete()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'ลบข้อมูลไม่สำเร็จ']);
}
