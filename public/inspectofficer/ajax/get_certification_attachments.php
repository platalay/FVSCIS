<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
@ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
if (function_exists('ob_get_length') && ob_get_length()) { @ob_clean(); }

$certification_id = $_GET['id'] ?? null;
if (!$certification_id) { echo json_encode(['success'=>false,'message'=>'missing certification id']); exit; }

$atts = FvCertificateAttachment::find_by_certificate_id($certification_id);

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
  //error_log('a vars = ' . print_r(get_object_vars($a), true));
  $out[] = [
    'id'        => (int)$a->id,
    'name'      => $a->file_name,
    'file_type'      => $a->file_type,
    'size'      => (int)($a->file_size ?? 0),
    'attachment_type' => $a->attachment_type,
    'url'       => $url,                    // มาตรฐาน
    'url_enc'   => encode_path($url),       // เผื่อ JS อยากใช้ที่ encode แล้ว
    'is_image'  => is_img($a->file_name, $a->file_type),
    'exists'    => $rel ? file_exists($pubPath . $rel) : false, // บอกสถานะไฟล์จริง
  ];
}
echo json_encode(['success'=>true,'attachments'=>$out], JSON_UNESCAPED_UNICODE);

