<?php
declare(strict_types=1);

require_once('../../../private/initialize.php');
global $database;          // ดึงตัวแปร $database ที่ initialize.php สร้างไว้
$db = $database;           // แค่ alias เฉย ๆ จะได้อ่านง่าย
$session->require_role(['fisherman']);
header('Content-Type: application/json; charset=utf-8');

try {

    // ------------------------------
    // 1) ตรวจสอบ id
    // ------------------------------
    if (!isset($_POST['id']) || !ctype_digit((string)$_POST['id'])) {
        throw new Exception('ไม่พบข้อมูล ID ที่ถูกต้อง');
    }
    $id = (int)$_POST['id'];

    $request = InspectionRequest::find_by_id($id);
    if (!$request) {
        throw new Exception('ไม่พบคำขอในระบบ');
    }

    // ------------------------------
    // 2) ตรวจสอบสิทธิ์
    // ------------------------------
    $currentUserId = (int)$session->user_id();
    if ($currentUserId !== (int)$request->created_by) {
        throw new Exception('คุณไม่มีสิทธิ์ลบคำขอนี้');
    }

    // ------------------------------
    // 3) เตรียมข้อมูลไฟล์แนบ (เก็บ path ก่อน)
    // ------------------------------
    $attachments = InspectionAttachment::find_by_request_id($id);
    $fileList = [];
    $applicant = InspectionApplicantInfo::find_by_request_id($id);
    
    $docRoot = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $pubPath = rtrim(str_replace('\\','/', defined('PUBLIC_PATH') ? PUBLIC_PATH : $docRoot), '/');

    foreach ($attachments as $att) {
        $filePath = $att->file_path ?? '';

        if (!empty($filePath) && !preg_match('~^https?://~i', $filePath)) {

            $abs = realpath($pubPath . '/' . ltrim($filePath, '/'));
            $uploadsBase = realpath($pubPath . '/uploads');

            if ($abs && $uploadsBase && str_starts_with($abs, $uploadsBase) && is_file($abs)) {
                $fileList[] = $abs;
            }
        }
    }

    // ------------------------------
    // 4) เริ่ม Transaction แบบ SQL
    // ------------------------------
    
    $db->query("START TRANSACTION");

    // ------------------------------
    // 5) ลบข้อมูลใน DB
    // ------------------------------

    // ลบคำขอ
    if (!$request->delete()) {
        $db->query("ROLLBACK");
        throw new Exception('ลบคำขอไม่สำเร็จ');
    }

    // ลบผู้ยื่นแบบ สร.1
    InspectionApplicantInfo::delete_by_request_id($id);

    // ลบ attachments (เฉพาะใน DB)
    foreach ($attachments as $att) {
        if (!$att->delete()) {
            $db->query("ROLLBACK");
            throw new Exception('ลบข้อมูลไฟล์แนบในฐานข้อมูลไม่สำเร็จ');
        }
    }

    // รีเซ็ตสถานะเรือ
    $ship_code = trim($request->ship_code ?? '');
    if ($ship_code !== '') {
        FvSanitationCertificationOld::reset_after_request_deleted($ship_code);
    }

    // ------------------------------
    // 6) Log การลบ
    // ------------------------------
    $fisherman = Fisherman::find_by_id($currentUserId);
    $log = new InspectionLog();
    $log->inspection_request_id = $id;
    $log->action_id             = 4;
    if ($applicant && !empty($applicant->form1_doc_number)) {
    $log->note = "เรือ {$request->vessel_name} หมายเลขทะเบียน {$request->ship_code} หมายเลขเอกสาร {$applicant->form1_doc_number} ถูกลบคำขอโดย {$fisherman->full_name}";    
    }else{
    $log->note = "เรือ {$request->vessel_name} หมายเลขทะเบียน {$request->ship_code} ถูกลบคำขอโดย {$fisherman->full_name}";
    }
    if (!$log->save()) {
        $db->query("ROLLBACK");
        throw new Exception('ลบสำเร็จ แต่บันทึกประวัติไม่สำเร็จ');
    }

    // ------------------------------
    // 7) Notifications
    // ------------------------------
    Notification::create_notification(
        $currentUserId,
        'fisherman',
        $id,
        4,
        "เรือ {$request->vessel_name} : ลบคำขอตรวจสุขอนามัยเรียบร้อย",
        'warning'
    );

    if (!empty($request->department_id)) {
        $officers = Officer::find_by_department_id($request->department_id) ?: [];
        foreach ($officers as $officer) {
            Notification::create_notification(
                $officer->id,
                'inspectofficer',
                $id,
                4,
                "เรือ {$request->vessel_name} : ผู้ยื่นคำขอลบคำขอตรวจสุขอนามัย",
                'warning'
            );
        }
    }

    // ------------------------------
    // 8) COMMIT → DB ปลอดภัยแล้ว
    // ------------------------------
    $db->query("COMMIT");

    // ------------------------------
    // 9) ลบไฟล์จริงใน disk
    // ------------------------------
    foreach ($fileList as $abs) {
        if (is_file($abs)) {
            @unlink($abs);
        }
    }

    echo json_encode(['success' => true]);

} catch (Throwable $e) {

    if (isset($db)) {
        @$db->query("ROLLBACK");
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
