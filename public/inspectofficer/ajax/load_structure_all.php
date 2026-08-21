<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);

if (!isset($_POST['request_id'])) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบ request_id']);
    exit;
}

$request_id = $_POST['request_id'];
$record = InspectionFormStructure::find_or_create($request_id);

if (!$record) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูล']);
    
    exit;
}
// แปลง object เป็น array เพื่อใช้ใน JavaScript
echo json_encode([
    'success' => true,
    'data' => get_object_vars($record)
]);
