<?php
declare(strict_types=1);

require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
header('Content-Type: application/json; charset=utf-8');

try {
    $idParam = $_POST['attachment_id'] ?? '';
    if ($idParam === '' || !ctype_digit((string)$idParam)) {
        throw new Exception('missing or invalid attachment id');
    }
    $attach_id = (int)$idParam;

    /** @var FvCertificateAttachment|null $att */
    $att = FvCertificateAttachment::find_by_id($attach_id);
    if(!$att) throw new Exception('attachment not found');

    // ลบไฟล์จริง (กรณีเก็บใน /public/uploads)
    $docRoot = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $pubPath = rtrim(str_replace('\\','/', defined('PUBLIC_PATH') ? PUBLIC_PATH : $docRoot), '/');

    $filePath = (string)$att->file_path; // อาจเป็น '/uploads/...' หรือ URL
    if(!preg_match('~^https?://~i', $filePath)){
        $abs = realpath($pubPath . '/' . ltrim($filePath,'/'));
        $uploadsBase = realpath($pubPath . '/uploads');
        if($abs && $uploadsBase && str_starts_with($abs, $uploadsBase) && is_file($abs)){
            @unlink($abs);
        }
    }

    // ลบ DB
    if(!$att->delete()){
        throw new Exception('delete db failed');
    }

    echo json_encode(['success'=>true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
