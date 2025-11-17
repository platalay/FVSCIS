<?php
require_once('../../../private/initialize.php');
header('Content-Type: application/json; charset=utf-8');

try {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        throw new Exception('ไม่พบ request_id');
    }

    $req = InspectionRequest::find_by_id($id);
    if (!$req) {
        throw new Exception('ไม่พบคำขอในระบบ');
    }

    // ป้องกันไม่ให้ชาวประมงคนอื่นมาแอบดู
    $fisherman = Fisherman::find_by_id($session->user_id());
    if (!$fisherman || $req->created_by !== $fisherman->id) {
        throw new Exception('คุณไม่มีสิทธิ์ดูคำขอนี้');
    }

    // ดึงข้อมูลเรือ (ถ้าอยากโชว์ชื่อ/ตัน/พื้นที่)
    $vessel = Elicense::find_by_ship_code($el_db, $req->ship_code);

    $data = [
        'id'                  => $req->id,
        'ship_code'           => $req->ship_code,
        'vessel_name'         => $vessel->vessel_name ?? $req->vessel_name ?? null,
        'gross_ton'           => $vessel->vessel_ton_gross ?? null,
        'fishing_area'        => $vessel->fishing_area ?? null,

        'contact_phone'       => $req->contact_phone,

        // *** ใช้ id ตรง ๆ ***
        'port_province_id'    => $req->port_province_id,
        'port_amphur_id'      => $req->port_amphur_id,
        'port_tambon_id'      => $req->port_tambon_id,
        'port_license_no'     => $req->port_license_no,

        'department_id'       => $req->department_id,

        // *** เดตต้องเป็น YYYY-MM-DD ***
        'inspect_date_start'  => $req->inspect_date_start,
        'inspect_date_end'    => $req->inspect_date_end,

        'inspection_form_type'=> (int)$req->inspection_form_type,
        'cold_room_flag'      => (int)$req->cold_room_flag,
    ];

    echo json_encode([
        'success' => true,
        'request' => $data,
    ]);

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
