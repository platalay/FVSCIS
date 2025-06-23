<?php
require_once('../../../private/initialize.php');
$session->require_role(['fisherman']);
header('Content-Type: application/json');

try {
    if (!isset($_POST['request'])) {
        throw new Exception("ไม่พบข้อมูลที่ส่งมา");
    }

    $data = $_POST['request'];

    // ✳️ ตรวจสอบข้อมูลจำเป็น
    $ship_code      = trim($data['ship_code'] ?? '');
    $contact_phone  = trim($data['contact_phone'] ?? '');
    $department_id  = trim($data['department_id'] ?? '');
    $province_id    = trim($data['port_province_id'] ?? '');
    $amphur_id      = trim($data['port_amphur_id'] ?? '');
    $tambon_id      = trim($data['port_tambon_id'] ?? '');
    $port_license   = trim($data['port_license_no'] ?? '');
    $inspect_start  = trim($data['inspect_date_start'] ?? '');
    $inspect_end    = trim($data['inspect_date_end'] ?? '');
    $agree          = isset($data['confirm_agreement']) ? 1 : 0;

    if ($ship_code === '' || $contact_phone === '' || $department_id === '') {
        throw new Exception("กรุณากรอกข้อมูลให้ครบถ้วน");
    }

    // ✅ ตรวจสอบว่าเรือนี้มีคำขอที่ยังไม่เสร็จหรือไม่
    $existing_request = InspectionRequest::find_active_by_ship($ship_code);
    if ($existing_request) {
        echo json_encode([
            'success' => false,
            'message' => 'คุณมีคำขอตรวจเรือที่ยังไม่เสร็จ กรุณายกเลิกหรือรอผลก่อน',
        ]);
        exit;
    }

    // ✅ 1. บันทึกคำขอ
    $request = new InspectionRequest();
    $request->ship_code           = $ship_code;
    $request->contact_phone       = $contact_phone;
    $request->department_id       = $department_id;
    $request->port_province_id    = $province_id;
    $request->port_amphur_id      = $amphur_id;
    $request->port_tambon_id      = $tambon_id;
    $request->port_license_no     = $port_license;
    $request->inspect_date_start  = $inspect_start;
    $request->inspect_date_end    = $inspect_end;
    $request->confirm_agreement   = $agree;
    $request->created_by = $session->user_id() ?? 0;
    $request->created_at          = date('Y-m-d H:i:s');
    $request->status = InspectionRequest::STATUS_PENDING;
    
    if (!$request->save()) {
        throw new Exception("ไม่สามารถบันทึกคำขอได้" . ($request->errors ?? ''));
    }

    // ✅ 2. บันทึก log
    $action = LogAction::find_by_code('submitted');
    if (!$action) {
        throw new Exception("ไม่พบรหัส action 'submitted'");
    }

    $log = new InspectionLog();
    $log->inspection_request_id = $request->id;
    $log->action_id             = $action->id;
    $log->note                  = 'ชาวประมงยื่นคำขอตรวจเรือ';
    $log->performed_by = $session->user_id() ?? 0; // ถ้าไม่มี session ให้เก็บเป็น 0 (system)
    $log->target_department_id  = $department_id;
    $log->target_usertype_id    = 3; // สมมุติว่า 3 = officer
    $log->port_license_no       = $port_license;
    $log->save();
    //save notification
    $officers = Officer::find_by_department_id($department_id);
    foreach ($officers as $officer) {
        Notification::create_notification(
            $officer->id,
            'inspectofficer',
            "มีคำขอตรวจสุขอนามัยเรือใหม่จากชาวประมง",
            'action_required',
            'inspection_request',
            $request->id,
            $log->id
        );
    }

    echo json_encode([
        'success' => true,
        'message' => 'บันทึกคำขอเรียบร้อยแล้ว',
    ]);
    exit;

} catch (Exception $ex) {
    echo json_encode([
        'success' => false,
        'message' => $ex->getMessage(),
    ]);
    exit;
}
?>
