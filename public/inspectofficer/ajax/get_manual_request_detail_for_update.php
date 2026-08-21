<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
header('Content-Type: application/json; charset=utf-8');

try {
    $id = $_GET['id'] ?? '';
    if (!$id) throw new Exception("ไม่มีรหัสคำขอ");

    /** @var InspectionRequest|null $req */
    $req = InspectionRequest::find_by_id($id);
    if (!$req) throw new Exception("ไม่พบคำขอในระบบ");

    // ดึงตำบล/อำเภอ/จังหวัด (ถ้าใช้)
    $tambon   = $req->port_tambon_id   ? Tambon::find_by_id($req->port_tambon_id)   : null;
    $amphur   = $req->port_amphur_id   ? Amphur::find_by_id($req->port_amphur_id)   : null;
    $province = $req->port_province_id ? Province::find_by_id($req->port_province_id): null;

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

    $data = [
        'id'                   => (int)$req->id,
        'vessel_name'          => $req->vessel_name,
        'ship_code'            => $req->ship_code,
        'owner_name'           => $req->owner_name,
        'vessel_mark'          => $req->vessel_mark,
        'license_number'       => $req->license_number,
        'license_status'       => $req->license_status,//normal none
        'gear_type'            => $req->gear_type,
        'port_tambon_id'       => $req->port_tambon_id,
        'port_amphur_id'       => $req->port_amphur_id,
        'port_province_id'     => $req->port_province_id,
        'port_license_no'      => $req->port_license_no,
        'inspect_date_start'   => $req->inspect_date_start,
        'inspect_date_end'     => $req->inspect_date_end,
        'confirmed_inspect_date'=> $req->confirmed_inspect_date,
        'inspection_form_type' => $req->inspection_form_type,
        'cold_room_flag'       => $req->cold_room_flag,
        'contact_phone'        => $req->contact_phone,
        'attachments'          => $attachments,
    ];

    echo json_encode(['success' => true, 'request' => $data], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

