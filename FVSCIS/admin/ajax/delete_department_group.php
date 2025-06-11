<?php
require_once('../../../private/initialize.php');
header('Content-Type: application/json');
ob_start(); // ป้องกัน output อื่น

$id = $_POST['id'] ?? null;

if (empty($id) || !is_numeric($id)) {
    echo json_encode(['success' => false, 'message' => 'ID ไม่ถูกต้อง']);
    exit;
}

$related_departments = Department::find_by_department_group_id($id);
if ($related_departments !== false) {
    echo json_encode([
        'success' => false,
        'message' => 'ไม่สามารถลบได้: กลุ่มหน่วยงานนี้มีการเชื่อมโยงกับหน่วยงานอื่น'
    ]);
    exit;
}

$DepartmentGroup = DepartmentGroup::find_by_id($id);
if (!$DepartmentGroup) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูล']);
    exit;
}

$result = $DepartmentGroup->delete();

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ลบข้อมูลไม่สำเร็จ'
    ]);
}
