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
    //ปรับ pending กลับเป็น action
    FvSanitationCertificationOld::mark_inactive($obj->ship_code);

    $action = LogAction::find_by_code('fvscis_deleted_by_officer');
    if ($action) {
        $log = new InspectionLog();
            $log->inspection_request_id = $obj->id;
            $log->action_id             = $action->id;
            $log->note                  = "เจ้าหน้าที่ลบผลตรวจจากเอกสารของเรือ ".$obj->vessel_name;
            $log->save();
    }
    $message = "เจ้าหน้าที่ลบผลตรวจจากเอกสารของเรือ ".$obj->vessel_name;
    $officers = Officer::find_by_department_id($obj->evaluation_agency);
        foreach ($officers as $officer) {
            Notification::create_notification(
                $officer->id,
                'inspectofficer',
                $obj->id,
                $action->id,
                $message,
                'warning'
            );
        }
        $action1 = LogAction::find_by_code('fvscis_created_by_officer');
        $action2 = LogAction::find_by_code('fvscis_updated_by_officer');
        $officers = Officer::find_by_department_id($obj->department_id);
        foreach ($officers as $officer) {
        Notification::mark_action_taken($officer->id, 'inspectofficer', $obj->id, [$action1->id,$action2->id]);
        }
        /*Notification::mark_action_taken($session->user_id(), 'inspectofficer', $obj->id, $action1->id);*/

    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
