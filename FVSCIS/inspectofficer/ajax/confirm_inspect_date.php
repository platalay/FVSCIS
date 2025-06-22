<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
header('Content-Type: application/json');

try {
    $request_id           = $_POST['request_id'] ?? null;
    $confirmed_date       = $_POST['confirmed_date'] ?? null;
    $original_date_hidden = $_POST['original_confirmed_date'] ?? '';

    if (!$request_id || !$confirmed_date) throw new Exception("ข้อมูลไม่ครบถ้วน");

    $request = InspectionRequest::find_by_id($request_id);
    if (!$request) throw new Exception("ไม่พบคำขอ");

    // 🔒 ป้องกันยืนยันซ้ำ (concurrent update)
    $current_date = $request->confirmed_inspect_date ?? '';
    if ($current_date !== $original_date_hidden) {
        throw new Exception("มีเจ้าหน้าที่คนอื่นยืนยันวันตรวจแล้ว กรุณารีเฟรชข้อมูลก่อนดำเนินการ");
    }

    $request->confirmed_inspect_date = $confirmed_date;
    $request->status = InspectionRequest::STATUS_INSPECTING;

    if (!$request->save()) throw new Exception("ไม่สามารถอัปเดตได้");

    // ✅ เก็บ log
    $action = LogAction::find_by_code('confirmed');
    if ($action) {
        $log = new InspectionLog();
        $log->inspection_request_id = $request->id;
        $log->action_id = $action->id;
        $log->note = "เจ้าหน้าที่ยืนยันวันตรวจเป็น {$confirmed_date}";
        $log->performed_by = $session->user_id() ?? 0;
        $log->target_department_id = $request->department_id;
        $log->target_usertype_id = 3;
        $log->port_license_no = $request->port_license_no;
        $log->save();
    }

    echo json_encode(['success' => true, 'message' => 'ยืนยันวันตรวจเรียบร้อยแล้ว']);
} catch (Exception $ex) {
    echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
}
?>
