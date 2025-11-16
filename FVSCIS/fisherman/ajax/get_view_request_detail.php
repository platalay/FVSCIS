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

    // โหลดข้อมูลเสริม เช่น เรือ / ท่าเรือ / หน่วยงาน
    $vessel = Elicense::find_by_ship_code($el_db, $req->ship_code);
    $dept   = Department::find_by_id($req->department_id);
    $port   = ElicensePort::find_by_license_no($el_db, $req->port_license_no);
    $port_amphur_name = Amphur::find_by_id($req->port_amphur_id);
     $port_province_id = Province::find_by_id($req->port_province_id);
      $port_tambon_name = Tambon::find_by_id($req->port_tambon_id);
      $department_name = Department::find_by_id($req->department_id);

    $data = [
        'id'                     => $req->id,
        'ship_code'              => $req->ship_code,
        'status'                 => $req->status,
        'contact_phone'          => $req->contact_phone,
        'inspect_date_start'     => thai_date($req->inspect_date_start),
        'inspect_date_end'       => thai_date($req->inspect_date_end),
        'confirmed_inspect_date' =>
    (!empty($req->confirmed_inspect_date) &&
     $req->confirmed_inspect_date !== '0000-00-00')
        ? thai_date($req->confirmed_inspect_date)
        : '-',

        'inspection_form_type'   => $req->inspection_form_type,
        'cold_room_flag'         => $req->cold_room_flag,
        'created_at'             => thai_date($req->created_at),
        'vessel_name'       => $vessel->vessel_name ?? null,
        'vessel_ton_gross'  => $vessel->vessel_ton_gross ?? null,
        'fishing_area'      => $vessel->fishing_area ?? null,
       
        'port_province_name' => $port_province_id->name ?? null,
        
        'port_amphur_name'   => $port_amphur_name->name ?? null,
       
        'port_tambon_name'   => $port_tambon_name->name ?? null,
        'port_name'          => $req->port_name ?? null,
        'port_license_no'    => $req->port_license_no ?? null,
        'department_name'    => $department_name->name ?? null,
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
