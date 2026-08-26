<?php
declare(strict_types=1);

require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
header('Content-Type: application/json; charset=utf-8');

try {
    $idParam = $_POST['attachment_id'] ?? ($_POST['id'] ?? '');
    if ($idParam === '' || !ctype_digit((string)$idParam)) {
        throw new Exception('missing or invalid attachment id');
    }
    $attach_id = (int)$idParam;

    /** @var FvCertificateAttachment|null $att */
    $att = FvCertificateAttachment::find_by_id($attach_id);
    if(!$att) throw new Exception('attachment not found');

    // Backend Guard: ลบไฟล์แนบได้เฉพาะของ record ที่เป็น working record จริง (status=active และยังไม่หมดอายุ) เท่านั้น
    $cert = FvSanitationCertificationOld::find_by_id((int)$att->certificate_id);
    if (!$cert || !FvSanitationCertificationOld::is_active_working($cert->status, $cert->expiration_date)) {
        throw new Exception('รายการนี้ไม่ใช่ใบรับรองที่ใช้งานอยู่ในปัจจุบัน และไม่สามารถแก้ไขหรือลบได้');
    }

    $deleted_attachment = [
        'attachment_id' => (int)$att->id,
        'attachment_type' => $att->attachment_type,
        'file_name' => $att->file_name,
    ];

    $docRoot = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $pubPath = rtrim(str_replace('\\','/', defined('PUBLIC_PATH') ? PUBLIC_PATH : $docRoot), '/');
    $filePath = (string)$att->file_path;
    $physical_path = null;
    if (!preg_match('~^https?://~i', $filePath)) {
        $candidate = realpath($pubPath . '/' . ltrim($filePath, '/'));
        $uploadsBase = realpath($pubPath . '/uploads');
        if ($candidate && $uploadsBase && str_starts_with($candidate, $uploadsBase) && is_file($candidate)) {
            $physical_path = $candidate;
        }
    }

    $database->begin_transaction();

    // ลบ DB และเขียน audit ก่อน commit; physical file cleanup ทำหลัง commit เท่านั้น
    if(!$att->delete()){
        throw new Exception('delete db failed');
    }

    if (!InspectionLog::create_manual_certificate_audit(
        'fvscis_attachment_deleted',
        (int)$cert->id,
        'ลบไฟล์แนบใบรับรอง Manual',
        $deleted_attachment,
        null
    )) {
        throw new Exception('บันทึกประวัติการลบไฟล์แนบไม่สำเร็จ');
    }

    $database->commit();

    $cleanup_ok = true;
    if ($physical_path !== null && !@unlink($physical_path)) {
        $cleanup_ok = false;
        error_log('[FVSCIS] Manual certificate attachment database/audit delete committed, but physical cleanup failed: ' . $physical_path);
    }

    echo json_encode([
        'success' => true,
        'database_deleted' => true,
        'audit_recorded' => true,
        'physical_cleanup_success' => $cleanup_ok,
        'message' => $cleanup_ok ? 'ลบไฟล์แนบสำเร็จ' : 'ลบข้อมูลและประวัติสำเร็จ แต่ลบไฟล์จริงไม่สำเร็จ'
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if (isset($database)) {
        @$database->rollback();
    }
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
