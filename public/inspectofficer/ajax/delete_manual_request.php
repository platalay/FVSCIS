<?php
declare(strict_types=1);

require_once('../../../private/initialize.php');
global $database;          // ดึงตัวแปร $database ที่ initialize.php สร้างไว้
$db = $database;           // แค่ alias เฉย ๆ จะได้อ่านง่าย
$currentUserId = $session->user_id();
$session->require_role(['inspectofficer']);
header('Content-Type: application/json; charset=utf-8');

try {

    global $database; // หรือ DatabaseObject::$database แล้วแต่โครงของพี่

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

    if ($sessionUser !== $createdBy) {
        throw new Exception('คุณไม่มีสิทธิ์ลบคำขอนี้');
    }

    // ------------------------------
    // เตรียมข้อมูลไฟล์แนบ (ยังไม่ลบ)
    // ------------------------------
    $atts = InspectionAttachment::find_by_request_id($req_id);

    $docRoot = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $pubPath = rtrim(str_replace('\\','/', defined('PUBLIC_PATH') ? PUBLIC_PATH : $docRoot), '/');

    $fileList = []; // เก็บ path ไฟล์ที่จะลบทิ้งหลัง commit

    foreach ($atts as $att) {

        $filePath = $att->file_path;  // เช่น '/uploads/inspection/...'

        if (!empty($filePath) && !preg_match('~^https?://~i', $filePath)) {
            $abs = realpath($pubPath . '/' . ltrim($filePath, '/'));
            $uploadsBase = realpath($pubPath . '/uploads');

            if ($abs && $uploadsBase && str_starts_with($abs, $uploadsBase) && is_file($abs)) {
                $fileList[] = $abs; // เก็บไว้ก่อน
            }
        }
    }

    // ------------------------------
    // เริ่ม Transaction
    // ------------------------------
    $db->query("START TRANSACTION");

    // 1) ลบ row attachments ทั้งหมด
    foreach ($atts as $att) {
        if (!$att->delete()) {
            throw new Exception('ลบไฟล์แนบในฐานข้อมูลไม่สำเร็จ');
        }
    }
    $applicant = InspectionApplicantInfo::find_by_request_id($id);
    // 2) ลบข้อมูลผู้ยื่น + reset ใบรับรอง
    InspectionApplicantInfo::delete_by_request_id($req_id);

    $ship_code = $req->ship_code ?? null;
    if ($ship_code) {
        FvSanitationCertificationOld::reset_after_request_deleted($ship_code);
    }

    // 3) ลบคำขอจาก DB
    if (!$req->delete()) {
        throw new Exception('ลบคำขอไม่สำเร็จ');
    }



    // 4) LOG + Notification (ยังอยู่ใน Transaction)
    $action = LogAction::find_by_code('request_deleted_by_officer');
    $officer = Officer::find_by_id($currentUserId);
    if ($action) {
        $log = new InspectionLog();
        $log->inspection_request_id = $req->id;
        $log->action_id             = $action->id;
        if ($applicant && !empty($applicant->form1_doc_number)) {
        $log->note = "เรือ {$req->vessel_name} หมายเลขทะเบียน {$req->ship_code} หมายเลขเอกสาร {$applicant->form1_doc_number} ถูกลบคำขอโดย {$officer->full_name}";    
        }else{
        $log->note = "เรือ {$req->vessel_name} หมายเลขทะเบียน {$req->ship_code} ถูกลบคำขอโดย {$officer->full_name}";
        }
        $log->save();
    }

    $message  = "เจ้าหน้าที่ลบคำขอตรวจเรือแทนชาวประมง เรือ".$req->vessel_name;
    $officers = Officer::find_by_department_id($req->department_id);

    if ($action && !empty($officers)) {
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

        $action1  = LogAction::find_by_code('request_created_by_officer');
        $action2  = LogAction::find_by_code('request_updated_by_officer');
        $actIds   = [];
        if ($action1) { $actIds[] = $action1->id; }
        if ($action2) { $actIds[] = $action2->id; }

        if (!empty($actIds)) {
            foreach ($officers as $officer) {
                Notification::mark_action_taken(
                    $officer->id,
                    'inspectofficer',
                    $req->id,
                    $actIds
                );
            }
        }
    }

    // ถ้าทุกอย่างผ่าน → commit ก่อน
    $database->commit();

    // ------------------------------
    // หลัง commit แล้วค่อยลบไฟล์จริงใน disk
    // ------------------------------
    foreach ($fileList as $abs) {
        if (is_file($abs)) {
            @unlink($abs);
        }
    }

    echo json_encode(['success' => true]);

} catch (Throwable $e) {

    if (isset($database) && method_exists($database, 'rollback')) {
    $database->rollback();
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
