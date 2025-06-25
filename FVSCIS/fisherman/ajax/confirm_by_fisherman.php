<?php
require_once('../../../private/initialize.php');
$session->require_role(['fisherman']);
header('Content-Type: application/json');

try {
    $request_id = $_POST['request_id'] ?? null;
    $original = $_POST['original_confirmed_date'] ?? '';

    if (!$request_id) throw new Exception("ไม่พบข้อมูล");

    $req = InspectionRequest::find_by_id($request_id);
    if (!$req) throw new Exception("ไม่พบคำขอ");

    if ($req->confirmed_inspect_date !== $original) {
        throw new Exception("วันตรวจถูกเปลี่ยนแปลง กรุณารีเฟรชหน้าใหม่");
    }

    // ✅ 1. อัปเดตการตกลงของชาวประมง
    $req->confirm_agreement = 1;
    $req->status = "inspecting";
    if (!$req->save()) throw new Exception("ไม่สามารถบันทึกการยืนยันได้");

    // ✅ 2. หาค่า action_id ที่ถูกต้องจาก code
    $action = LogAction::find_by_code('fisher_confirm_date');
    if (!$action) throw new Exception("ไม่พบ log action: fisher_confirm_date");

    // ✅ 3. สร้าง log
    $log = new InspectionLog();
    $log->inspection_request_id = $request->id;
    $log->action_id             = $action->id;
    $log->note                  = 'ชาวประมงยืนยันวันตรวจเรือ';
    $log->performed_by          = $session->user_id() ?? 0; // รหัสผู้ใช้
    $log->performed_at          = date('Y-m-d H:i:s'); // ✅ ใส่เวลาแบบ real-time
    $log->target_department_id  = $department_id;
    $log->target_usertype_id    = 3; // สมมุติว่า 3 = officer
    $log->port_license_no       = $port_license;
    $log->save();

    // ✅ 4. แจ้งเตือนเจ้าหน้าที่กลุ่มที่รับผิดชอบ
    $message = "ชาวประมงยืนยันวันตรวจเรือ (" . $req->ship_code . ") เรียบร้อยแล้ว";
    $ref_type = 'fisher_confirm_date';
    $ref_id = $req->id;

    $officers = Officer::find_by_department_id($req->department_id);
    foreach ($officers as $officer) {
        Notification::create_notification(
            $officer->id,
            'officer',
            $message,
            'info',
            $ref_type,
            $ref_id,
            $log->id
        );
    }
    Notification::mark_related_as_read('inspection_scheduled', $request->id);
    echo json_encode(['success' => true]);
} catch (Exception $ex) {
    echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
}
?>
