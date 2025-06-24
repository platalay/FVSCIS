<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
header('Content-Type: application/json');

try {
    $request_id           = $_POST['request_id'] ?? null;
    $confirmed_date       = $_POST['confirmed_date'] ?? null;
    $original_date_hidden = $_POST['original_confirmed_date'] ?? '';
    error_log($request_id." ". $confirmed_date  );
    // 🔒 ตรวจสอบข้อมูลที่ส่งมาว่าถูกต้องหรือไม่
    if (!$request_id || !$confirmed_date) throw new Exception("ข้อมูลไม่ครบถ้วน <br>original_date_hidden = ".$original_date_hidden."<br>request_id = ".$request_id);

    // 🔒 ป้องกันค่าที่ไม่ใช่รูปแบบวันที่ หรือเป็น "0000-00-00"
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $confirmed_date) || $confirmed_date === '0000-00-00') {
        throw new Exception("วันที่ไม่ถูกต้อง");
    }

    $request = InspectionRequest::find_by_id($request_id);
    if (!$request) throw new Exception("ไม่พบคำขอ");

    // 🔒 ป้องกันการยืนยันซ้ำ
    $current_date = $request->confirmed_inspect_date ?? '';
    if ($current_date !== $original_date_hidden) {
        throw new Exception("มีเจ้าหน้าที่คนอื่นยืนยันวันตรวจแล้ว กรุณารีเฟรชข้อมูลก่อนดำเนินการ");
    }

    $request->confirmed_inspect_date = $confirmed_date;

    if (!$request->save()) throw new Exception("ไม่สามารถอัปเดตได้");

    // ✅ บันทึก log การยืนยัน
    $action = LogAction::find_by_code('confirmed');
    if ($action) {
        $log = new InspectionLog();
        $log->inspection_request_id    = $request->id;
        $log->action_id                = $action->id;
        $log->note                     = "เจ้าหน้าที่ยืนยันวันตรวจเป็น {$confirmed_date}";
        $log->performed_by             = $session->user_id() ?? 0;
        $log->target_department_id     = $request->department_id;
        $log->target_usertype_id       = 3;
        $log->port_license_no          = $request->port_license_no;
        $log->save();
    }

    // ✅ ส่งการแจ้งเตือน
    Notification::create_notification(
        $request->created_by,
        'fisherman',
        "เจ้าหน้าที่ได้ยืนยันวันตรวจเรือเป็นวันที่ {$confirmed_date}",
        'info',
        'inspection_request',
        $request->id,
        $log->id ?? null
    );

    echo json_encode(['success' => true, 'message' => 'ยืนยันวันตรวจเรียบร้อยแล้ว']);
} catch (Exception $ex) {
    echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
}
