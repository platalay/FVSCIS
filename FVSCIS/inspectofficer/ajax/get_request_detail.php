<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
header('Content-Type: application/json');

try {
    // รับ ID
    $id = $_GET['id'] ?? '';
    if (!$id) throw new Exception("ไม่มีรหัสคำขอ");

    // ค้นหาคำขอ
    $request = InspectionRequest::find_by_id($id);
    if (!$request) throw new Exception("ไม่พบคำขอในระบบ");

    // เตรียมข้อมูลตอบกลับ
    $ship = Elicense::find_by_ship_code($el_db, $request->ship_code);
    $data = [
        'id' => $id,
        'ship_name'           => $ship->vessel_name,
        'ship_code'           => $request->ship_code,
        'port_license_no'     => $request->port_license_no,
        'inspect_date_start'  => $request->inspect_date_start,
        'inspect_date_end'    => $request->inspect_date_end,
        'contact_phone'       => $request->contact_phone,
        'status'              => $request->status,
        'confirmed_inspect_date' => $request->confirmed_inspect_date,
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