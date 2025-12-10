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

    // เริ่ม Transaction
    $db->begin_transaction();

    // 1) ดึงไฟล์แนบทั้งหมดของใบรับรองนี้
    /** @var FvCertificateAttachment[] $attachments */
    $attachments = FvCertificateAttachment::find_by_certificate_id($id);

    // 2) ลบไฟล์จริง + เรคคอร์ด attachment ทีละตัว
    foreach ($attachments as $att) {
        // delete_with_file() จะ unlink(PUBLIC_PATH . file_path) + delete()
        if (!$att->delete_with_file()) {
            throw new Exception('ลบไฟล์แนบไม่สำเร็จ');
        }
    }

    // 3) ลบเรคคอร์ดใบรับรองหลัก
    $ok = $obj->delete();
    if (!$ok) {
        throw new Exception('ไม่สามารถลบข้อมูลได้');
    }

    // 4) ปรับ pending กลับเป็น active/ค่าเดิม (แล้วแต่ logic ภายใน mark_inactive)
    FvSanitationCertificationOld::mark_active($obj->ship_code);

    // 5) เขียน log การลบ
    $actionDelete = LogAction::find_by_code('fvscis_deleted_by_officer');
    if ($actionDelete) {
        $log = new InspectionLog();
        $log->inspection_request_id = $obj->id;
        $log->action_id             = $actionDelete->id;
        $log->note                  = "เจ้าหน้าที่ลบผลตรวจจากเอกสารของเรือ " . $obj->vessel_name;
        if (!$log->save()) {
            throw new Exception('บันทึกประวัติการลบไม่สำเร็จ');
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

    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
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
