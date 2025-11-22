<?php
declare(strict_types=1);

require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
header('Content-Type: application/json; charset=utf-8');

try {

    // ------------------------------
    // 1) ตรวจสอบค่า id
    // ------------------------------
    $id = $_POST['id'] ?? '';
    if (!$id || !ctype_digit((string)$id)) {
        throw new Exception('invalid request id');
    }
    $req_id = (int)$id;

    
    /** @var InspectionRequest|null $req */
    $req = InspectionRequest::find_by_id($req_id);
    if (!$req) {
        throw new Exception('คำขอไม่พบในระบบ');
    }
    $sessionUser = (int)$session->user_id();
    $createdBy   = (int)$req->created_by;

    // ------------------------------
    // 2) ตรวจสอบสิทธิ์: ลบได้เฉพาะคนสร้างเอง
    // ------------------------------
    if ($sessionUser !== $createdBy) {
        throw new Exception('คุณไม่มีสิทธิ์ลบคำขอนี้');
    }

    // ------------------------------
    // 3) ลบ attachments ทั้งหมด
    // ------------------------------
    $atts = InspectionAttachment::find_by_request_id($req_id);

    $docRoot = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $pubPath = rtrim(str_replace('\\','/', defined('PUBLIC_PATH') ? PUBLIC_PATH : $docRoot), '/');

    foreach ($atts as $att) {

        $filePath = $att->file_path;  // เช่น '/uploads/inspection/...'
        if (!empty($filePath) && !preg_match('~^https?://~i', $filePath)) {

            $abs = realpath($pubPath . '/' . ltrim($filePath, '/'));
            $uploadsBase = realpath($pubPath . '/uploads');

            if ($abs && $uploadsBase && str_starts_with($abs, $uploadsBase) && is_file($abs)) {
                @unlink($abs);
            }
        }

        $att->delete();
    }

    // ------------------------------
    // 4) ลบคำขอจาก DB
    // ------------------------------
    if (!$req->delete()) {
        throw new Exception('ลบคำขอไม่สำเร็จ');
    }

    // ------------------------------
    // 5) LOG - การลบ
    // ------------------------------
    $action = LogAction::find_by_code('request_deleted_by_officer');
    if ($action) {
        $log = new InspectionLog();
            $log->inspection_request_id = $req->id;
            $log->action_id             = $action->id;
            $log->note                  = "เจ้าหน้าที่ปรับปรุงคำขอตรวจเรือแทนชาวประมง เรือ".$req->vessel_name;
            $log->save();
    }
    $message = "เจ้าหน้าที่ปรับปรุงคำขอตรวจเรือแทนชาวประมง เรือ".$req->vessel_name;
    $officers = Officer::find_by_department_id($req->department_id);
        foreach ($officers as $officer) {
            Notification::create_notification(
                $officer->id,
                'inspectofficer',
                $req->id,
                $action->id,
                $message,
                'warning'
            );
        }
        $action1 = LogAction::find_by_code('request_created_by_officer');
        $action2 = LogAction::find_by_code('request_updated_by_officer');
        $officers = Officer::find_by_department_id($req->department_id);
        foreach ($officers as $officer) {
        Notification::mark_action_taken($officer->id, 'inspectofficer', $req->id, [$action1->id,$action2->id]);
        }
        /*Notification::mark_action_taken($session->user_id(), 'inspectofficer', $req->id, $action1->id);*/

    echo json_encode(['success' => true]);

} catch (Throwable $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
