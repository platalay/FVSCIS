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

    // โหลดข้อมูลเรือ
    $vessel = Elicense::find_by_ship_code($el_db, $req->ship_code);

    // เตรียมข้อมูล
    $data = [
        'id'                  => $req->id,
        'ship_code'           => $req->ship_code,
        'vessel_name'         => $vessel->vessel_name ?? $req->vessel_name ?? null,
        'gross_ton'           => $vessel->vessel_ton_gross ?? null,
        'fishing_area'        => $vessel->fishing_area ?? null,

        'contact_phone'       => $req->contact_phone,

        'port_province_id'    => $req->port_province_id,
        'port_amphur_id'      => $req->port_amphur_id,
        'port_tambon_id'      => $req->port_tambon_id,
        'port_license_no'     => $req->port_license_no,

        'department_id'       => $req->department_id,

        'inspect_date_start'  => $req->inspect_date_start,
        'inspect_date_end'    => $req->inspect_date_end,

        'inspection_form_type'=> (int)$req->inspection_form_type,
        'cold_room_flag'      => (int)$req->cold_room_flag,
    ];

    $atts = InspectionAttachment::find_by_request_id($req->id);

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


// ===== helper: ตรวจว่าไฟล์เป็นรูปภาพหรือไม่ =====
function is_img($name, $type) {
    // ถ้า MIME type เริ่มด้วย image/
    if (!empty($type) && strtolower(substr($type, 0, 6)) === 'image/') {
        return true;
    }
    // ถ้าไม่มี MIME type ให้ดูนามสกุล
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg','jpeg','png','gif','webp','bmp','svg'], true);
}


$out = [];
foreach ($atts as $a) {
  $rel = normalize_rel_upload($a->file_path ?? '');
  $url = $rel ? $appBase . $rel : '';

  $out[] = [
    'id'        => (int)$a->id,
    'name'      => $a->file_name,
    'type'      => $a->file_type,
    'size'      => (int)($a->file_size ?? 0),
    'attachment_type' => $a->attachment_type,
    'url'       => $url,                    // มาตรฐาน
    'url_enc'   => encode_path($url),       // เผื่อ JS อยากใช้ที่ encode แล้ว
    'is_image'  => is_img($a->file_name, $a->file_type),
    'exists'    => $rel ? file_exists($pubPath . $rel) : false, // บอกสถานะไฟล์จริง
  ];
}

    echo json_encode([
        'success'     => true,
        'request'     => $data,
        'attachments' => $out,
    ]);

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
