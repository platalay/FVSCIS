<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
header('Content-Type: application/json');

try {
    $request_id           = $_POST['request_id'] ?? null;
    $confirmed_date       = $_POST['confirmed_date'] ?? null;
    $original_date_hidden = $_POST['original_confirmed_date'] ?? '';

    // ✅ Debug log (ใช้เฉพาะตอนทดสอบ)
    //////error_log("[CONFIRM] request_id = $request_id, confirmed_date = $confirmed_date, original_date = $original_date_hidden");

    // 🔒 ตรวจสอบข้อมูลที่ส่งมาว่าครบถ้วน
    if (!$request_id || !$confirmed_date) {
        throw new Exception("ข้อมูลไม่ครบถ้วน <br>original_date_hidden = $original_date_hidden<br>request_id = $request_id");
    }

    // 🔒 ตรวจสอบรูปแบบวันที่
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $confirmed_date) || $confirmed_date === '0000-00-00') {
        throw new Exception("วันที่ไม่ถูกต้อง");
    }

    $request = InspectionRequest::find_by_id($request_id);
    if (!$request) throw new Exception("ไม่พบคำขอ");

    // 🔒 ป้องกันการยืนยันซ้ำจากข้อมูลที่ล้าสมัย
    $current_date = $request->confirmed_inspect_date ?? '';
    $original_date_hidden = $original_date_hidden ?? '';

    // ✅ เทียบวันที่โดยพิจารณาว่าค่าว่างคือ "ยังไม่มีนัดหมาย"
    $isEmptyCurrent  = ($current_date === '' || $current_date === '0000-00-00' || is_null($current_date));
    $isEmptyOriginal = ($original_date_hidden === '' || $original_date_hidden === '0000-00-00' || is_null($original_date_hidden));

    if (!$isEmptyCurrent && $current_date !== $original_date_hidden) {
        throw new Exception("มีเจ้าหน้าที่คนอื่นยืนยันวันตรวจแล้ว กรุณารีเฟรชข้อมูลก่อนดำเนินการ");
    }

    // ✅ บันทึกวันที่ใหม่
    $request->confirmed_inspect_date = $confirmed_date;

    if (!$request->save()) {
        throw new Exception("ไม่สามารถอัปเดตวันตรวจได้");
    }

    // ✅ บันทึก log
    $action = LogAction::find_by_code('inspection_scheduled');
    if ($action) {
        $log = new InspectionLog();
        $log->inspection_request_id = $request->id;
        $log->action_id             = $action->id;
        $log->note                  = "เจ้าหน้าที่ยืนยันวันตรวจเป็น {$confirmed_date}";
        $log->performed_by          = $session->user_id() ?? 0;
        $log->performed_at          = date('Y-m-d H:i:s'); // ✅ ใส่เวลาแบบ real-time
        $log->target_department_id  = $request->department_id;
        $log->target_usertype_id    = 3;
        $log->port_license_no       = $request->port_license_no;
        $log->save();
    }

    // ✅ แจ้งเตือนเจ้าของคำขอ
    Notification::create_notification(
        $request->created_by,
        'fisherman',
        "เจ้าหน้าที่ได้ยืนยันวันตรวจเรือเป็นวันที่ {$confirmed_date}",
        'info',
        'inspection_scheduled',
        $request->id,
        $log->id ?? null
    );
    Notification::mark_related_as_read('inspection_request', $request->id);
    echo json_encode(['success' => true, 'message' => 'ยืนยันวันตรวจเรียบร้อยแล้ว']);
} catch (Exception $ex) {
    echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
}
?>
