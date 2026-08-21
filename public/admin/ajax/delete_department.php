<?php
require_once('../../../private/initialize.php');
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
$id = filter_var($id, FILTER_VALIDATE_INT);

if ($id === false) {
    echo json_encode(['success' => false, 'message' => 'ID ไม่ถูกต้อง']);
    exit;
}

$Department = Department::find_by_id($id);
if (!$Department) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลหน่วยงาน']);
    exit;
}

// ตรวจสอบการเชื่อมโยงกับ officer
$officer_linked = Officer::find_by_sql("SELECT * FROM officer WHERE departments_id = {$id}");

if (!empty($officer_linked)) {
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบได้ เนื่องจากมีเจ้าหน้าที่เชื่อมโยงกับหน่วยงานนี้']);
    exit;
}

$result = $Department->delete();

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'ลบข้อมูลไม่สำเร็จ']);
}
