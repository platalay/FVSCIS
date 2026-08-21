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
    if (!$fisherman || (int)$req->created_by !== (int)$fisherman->id) {
        throw new Exception('คุณไม่มีสิทธิ์ดูคำขอนี้');
    }

    // โหลดข้อมูลเรือ
    $vessel = Elicense::find_by_ship_code($el_db, $req->ship_code);

    // เตรียมข้อมูลคำขอ
    $data = [
        'id'                   => (int)$req->id,
        'ship_code'            => $req->ship_code,
        'vessel_name'          => $vessel->vessel_name ?? $req->vessel_name ?? null,
        'gross_ton'            => $vessel->vessel_ton_gross ?? null,
        'fishing_area'         => $vessel->fishing_area ?? null,

        'contact_phone'        => $req->contact_phone,

        'port_province_id'     => $req->port_province_id,
        'port_amphur_id'       => $req->port_amphur_id,
        'port_tambon_id'       => $req->port_tambon_id,
        'port_license_no'      => $req->port_license_no,

        'department_id'        => $req->department_id,

        'inspect_date_start'   => $req->inspect_date_start,
        'inspect_date_end'     => $req->inspect_date_end,

        'inspection_form_type' => (int)$req->inspection_form_type,
        'cold_room_flag'       => (int)$req->cold_room_flag,
    ];

    // ===== attachments =====
    $atts = InspectionAttachment::find_by_request_id($req->id);

    // encode ทีละ segment (คง '/')
    // ===== คำนวณ BASE จาก PUBLIC_PATH -> '/fvscis' =====
    $docRoot = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT']), '/');
    $pubPath = rtrim(str_replace('\\','/', PUBLIC_PATH), '/');
    $appBase = str_replace($docRoot, '', $pubPath);
    $appBase = ($appBase === '' ? '' : '/' . ltrim($appBase, '/'));

    // ทำให้เป็น '/uploads/...' เสมอ ต่อให้ DB เก็บ '/inspectofficer/ajax/uploads/...'
    function normalize_rel_upload($p) {
    if (!$p) return '';
    $p = str_replace('\\','/', $p);
    // ตัดทุกอย่างหน้า '/uploads/' ทิ้ง
    $pos = strpos($p, '/uploads/');
    if ($pos !== false) {
        $p = substr($p, $pos); // -> '/uploads/...'
    } else {
        // เผื่อกรณีไม่มีสแลชนำข้างหน้า
        $p = '/' . ltrim($p, '/');
    }
    return $p;
    }

    // encode ทีละ segment (คง '/')
    function encode_path($path) {
    $path = ltrim($path, '/');
    $parts = array_map('rawurlencode', explode('/', $path));
    return '/' . implode('/', $parts);
    }

    // 🔹 แนบไฟล์ของคำขอนี้ (สมมติคลาส ManualRequestAttachment / InspectionRequestAttachment)
    $attachments = [];
    $rows = InspectionAttachment::find_by_request_id($req->id); // ปรับให้ตรงคลาสจริงของเต้ย
    foreach ($rows as $att) {
        $rel = normalize_rel_upload($att->file_path ?? '');
        $url = $rel ? $appBase . $rel : '';
        $attachments[] = [
            'id'              => (int)$att->id,
            'name'            => $att->file_name,
            'attachment_type' => $att->attachment_type,
            'url'       => $url,                    // มาตรฐาน
            'url_enc'   => encode_path($url),       // เผื่อ JS อยากใช้ที่ encode แล้ว
            'is_image'        => preg_match('/\.(jpe?g|png|gif|webp)$/i', $url) ? true : false,
            'exists'          => file_exists(PUBLIC_PATH . $url),
        ];
    }

    echo json_encode([
        'success'     => true,
        'request'     => $data,
        'attachments' => $attachments,
    ]);

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
