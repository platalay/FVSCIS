<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);

try {

    // รับ id ของใบรับรองเก่า
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception('ไม่พบ id');
    }

    /** @var FvSanitationCertificationOld|null $obj */
    $obj = FvSanitationCertificationOld::find_by_id($id);
    if (!$obj) {
        throw new Exception('ไม่พบข้อมูล');
    }

    // 1) ดึงไฟล์แนบทั้งหมดของใบรับรองนี้
    /** @var FvCertificateAttachment[] $attachments */
    $attachments = FvCertificateAttachment::find_by_certificate_id($id);

    // 2) ลบไฟล์จริง + เรคคอร์ด attachment ทีละตัว
    foreach ($attachments as $att) {
        // delete_with_file() จะ unlink(PUBLIC_PATH . file_path) + delete()
        $att->delete_with_file();
    }

    // 3) ลบเรคคอร์ดใบรับรองหลัก
    $ok = $obj->delete();
    if (!$ok) {
        throw new Exception('ไม่สามารถลบข้อมูลได้');
    }

    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
