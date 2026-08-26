<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);

try {

    // ใช้ $db สำหรับ transaction (สมมติได้จาก initialize.php)
    global $database;
    $db = $database;
    if (!$db) {
        throw new Exception('ไม่พบตัวแปรเชื่อมต่อฐานข้อมูล');
    }

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

    $deleted_certificate_values = [
        'certificate_id' => (int)$obj->id,
        'vessel_name' => $obj->vessel_name,
        'ship_code' => $obj->ship_code,
        'certificate_number' => $obj->certificate_number,
        'effective_date' => $obj->effective_date,
        'expiration_date' => $obj->expiration_date,
        'certificate_status' => $obj->certificate_status,
        'status' => $obj->status,
    ];

    // Backend Guard: ลบได้เฉพาะ record ที่เป็น working record จริง (status=active และยังไม่หมดอายุ) เท่านั้น
    // record ที่เป็นประวัติ (active-แต่หมดอายุ/inactive/fail/pending) ห้ามลบ
    if (!FvSanitationCertificationOld::is_active_working($obj->status, $obj->expiration_date)) {
        throw new Exception('รายการนี้ไม่ใช่ใบรับรองที่ใช้งานอยู่ในปัจจุบัน และไม่สามารถแก้ไขหรือลบได้');
    }

    // เริ่ม Transaction
    $db->begin_transaction();

    // 1) ดึงไฟล์แนบทั้งหมดของใบรับรองนี้
    /** @var FvCertificateAttachment[] $attachments */
    $attachments = FvCertificateAttachment::find_by_certificate_id($id);
    $deleted_attachments = [];

    // 2) เก็บ path และลบเฉพาะ DB row; physical file จะลบหลัง commit
    $physical_paths = [];
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $pubPath = rtrim(str_replace('\\', '/', defined('PUBLIC_PATH') ? PUBLIC_PATH : $docRoot), '/');
    $uploadsBase = realpath($pubPath . '/uploads');
    foreach ($attachments as $att) {
        $deleted_attachments[] = [
            'attachment_id' => (int)$att->id,
            'attachment_type' => $att->attachment_type,
            'file_name' => $att->file_name,
        ];
        $candidate = realpath($pubPath . '/' . ltrim((string)$att->file_path, '/'));
        if ($candidate && $uploadsBase && str_starts_with($candidate, $uploadsBase) && is_file($candidate)) {
            $physical_paths[] = $candidate;
        }
        if (!$att->delete()) {
            throw new Exception('ลบข้อมูลไฟล์แนบไม่สำเร็จ');
        }
    }

    // 3) ลบเรคคอร์ดใบรับรองหลัก
    $ok = $obj->delete();
    if (!$ok) {
        throw new Exception('ไม่สามารถลบข้อมูลได้');
    }

    // 4) ปรับ pending กลับเป็น active/ค่าเดิม (แล้วแต่ logic ภายใน mark_inactive)
    FvSanitationCertificationOld::mark_active($obj->ship_code);

    // 5) เขียน audit หลังลบ business record สำเร็จ แต่ยังอยู่ใน transaction เดียวกัน
    $actionDelete = LogAction::find_by_code('fvscis_deleted_by_officer');
    if (!InspectionLog::create_manual_certificate_audit(
        'fvscis_deleted_by_officer',
        $id,
        "เจ้าหน้าที่ลบผลตรวจจากเอกสารของเรือ " . $obj->vessel_name,
        $deleted_certificate_values,
        null
    )) {
        throw new Exception('บันทึกประวัติการลบไม่สำเร็จ');
    }
    foreach ($deleted_attachments as $deleted_attachment) {
        if (!InspectionLog::create_manual_certificate_audit(
            'fvscis_attachment_deleted',
            $id,
            'ลบไฟล์แนบใบรับรอง Manual พร้อมใบรับรอง',
            $deleted_attachment,
            null
        )) {
            throw new Exception('บันทึกประวัติการลบไฟล์แนบไม่สำเร็จ');
        }
    }

    // 6) แจ้งเตือนเจ้าหน้าที่ในหน่วยงานประเมิน
    $message   = "เจ้าหน้าที่ลบผลตรวจจากเอกสารของเรือ " . $obj->vessel_name;
    $officers  = Officer::find_by_department_id($obj->evaluation_agency);

    if (!empty($officers) && $actionDelete) {
        foreach ($officers as $officer) {
            Notification::create_notification(
                $officer->id,
                'inspectofficer',
                $obj->id,
                $actionDelete->id,
                $message,
                'warning'
            );
        }
    }

    // 7) ปิด action เดิมที่เคยแจ้ง (created / updated) ให้เป็น "จัดการแล้ว"
    $action1 = LogAction::find_by_code('fvscis_created_by_officer');
    $action2 = LogAction::find_by_code('fvscis_updated_by_officer');

    $actionIdsToClose = [];
    if ($action1) $actionIdsToClose[] = $action1->id;
    if ($action2) $actionIdsToClose[] = $action2->id;

    if (!empty($officers) && !empty($actionIdsToClose)) {
        foreach ($officers as $officer) {
            Notification::mark_action_taken(
                $officer->id,
                'inspectofficer',
                $obj->id,
                $actionIdsToClose
            );
        }
    }

    // ถ้าทุกอย่างผ่าน → commit
    $db->commit();

    $cleanup_failed = [];
    foreach (array_unique($physical_paths) as $physical_path) {
        if (!@unlink($physical_path)) {
            $cleanup_failed[] = $physical_path;
            error_log('[FVSCIS] Manual certificate delete committed, but attachment physical cleanup failed: ' . $physical_path);
        }
    }

    echo json_encode([
        'success' => true,
        'database_deleted' => true,
        'audit_recorded' => true,
        'physical_cleanup_success' => empty($cleanup_failed),
        'physical_cleanup_failed' => $cleanup_failed,
        'message' => empty($cleanup_failed)
            ? 'ลบใบรับรองสำเร็จ'
            : 'ลบข้อมูลและประวัติสำเร็จ แต่ลบไฟล์แนบบางรายการไม่สำเร็จ'
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {

    // ถ้ามี transaction อยู่ → rollback
    if (isset($db) && $db instanceof mysqli) {
        // suppress error ถ้าไม่มี transaction ค้างอยู่
        try {
            $db->rollback();
        } catch (Throwable $ignored) {
        }
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
