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
    $req->is_confirm = true;
    $req->status = "inspecting";
    if (!$req->save()) throw new Exception("ไม่สามารถบันทึกการยืนยันได้".$req->is_confirm);

    $log = new InspectionLog();
        $log->inspection_request_id = $req->id;
        $log->action_id             = 8;
        $log->note                  = "ยืนยันวันนัดตรวจเรือ {$req->vessel_name} วันที่ {$req->confirmed_date}";
        $log->save();

    // ✅ 4. แจ้งเตือนเจ้าหน้าที่กลุ่มที่รับผิดชอบ
    $message = "ชาวประมงยืนยันวันตรวจเรือ {$req->vessel_name} เรียบร้อยแล้ว";

    $officers = Officer::find_by_department_id($req->department_id);
    foreach ($officers as $officer) {
        Notification::create_notification(
            $officer->id,
            'inspectofficer',
            $req->id,
            8,
            $message,
            'warning'
        );
    }
    Notification::mark_action_taken($session->user_id(), 'fisherman', $req->id, 7);
    echo json_encode(['success' => true]);
} catch (Exception $ex) {
    echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
}
?>
