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
    $tambon = Tambon::find_by_id($request->port_tambon_id);
    $amphur = Amphur::find_by_id($request->port_amphur_id);
    $province = Province::find_by_id($request->port_province_id);
    $data = [
        'id' => $id,
        'vessel_name'         => $request->vessel_name,
        'ship_code'           => $request->ship_code,
        'owner_name'          => $request->owner_name,
        'port_license_no'     => $request->port_license_no,
        'port_name'           => $request->port_name,
        'port_tambon'         => $tambon->name,  
        'port_amphur'         => $amphur->name,
        'port_province'       => $province->name,
        'inspect_date_start'  => $request->inspect_date_start,
        'inspect_date_end'    => $request->inspect_date_end,
        'contact_phone'       => $request->contact_phone,
        'status'              => $request->status,
        'confirmed_inspect_date' => $request->confirmed_inspect_date,
        'is_confirm' => $request->is_confirm,
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