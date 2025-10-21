<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
header('Content-Type: application/json');

try {
    // รับ ID
    $id = $_GET['id'] ?? '';
    if (!$id) throw new Exception("ไม่มีรหัสคำขอ");
    
    // ค้นหาคำขอ
    $request = FvSanitationCertificationOld::find_by_id($id);
    if (!$request) throw new Exception("ไม่พบคำขอในระบบ");

    // เตรียมข้อมูลตอบกลับ
    $data = [
        'id' => $id,
        'vessel_name'         => $request->vessel_name,
        'ship_code'           => $request->ship_code
    ];
    echo json_encode([
        'success' => true,
        'request' => $data
    ]);
} catch (Exception $ex) {
    echo json_encode([
        'success' => false,
        'message' => $ex->getMessage()
    ]);
}

?>