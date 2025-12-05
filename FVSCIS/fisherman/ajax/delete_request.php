<?php
require_once('../../../private/initialize.php');
header('Content-Type: application/json; charset=utf-8');

try {
    // ตรวจ id
    if (!isset($_POST['id']) || !ctype_digit((string)$_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูล ID']);
        exit;
    }
    $id = (int)$_POST['id'];

    // หา request
    $request = InspectionRequest::find_by_id($id);
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบคำขอ']);
        exit;
    }

    // ✅ เก็บข้อมูลที่ต้องใช้ทำ log/notify ไว้ก่อนลบ
    $request_id        = $request->id;
    $department_id     = $request->department_id ?? null;
    $port_license_no   = $request->port_license_no ?? null;
    $request_owner_id  = $request->created_by ?? 0;   // ผู้ยื่นคำขอ (ชาวประมง)
    $deleter_user_id   = $session->user_id() ?? 0;    // ผู้ที่ลบ

    // ข้อมูลเรือสำหรับ log/notify
    $vessel_name   = trim($request->vessel_name ?? '');
    $ship_code     = trim($request->ship_code ?? '');
    $license_no    = trim($request->license_number ?? $port_license_no ?? '');

    
    // ใช้ transaction ถ้ามี
    if (class_exists('Database') && method_exists(Database::$database, 'begin_transaction')) {
        Database::$database->begin_transaction();
        $in_tx = true;
    } else {
        $in_tx = false;
    }

    // 🔥 ลบหลัก
    if (!$request->delete()) {
        if ($in_tx) Database::$database->rollback();
        echo json_encode(['success' => false, 'message' => 'ลบคำขอไม่สำเร็จ']);
        exit;
    }
    FvSanitationCertificationOld::mark_pending($ship_code);

    // 📝 Log การลบ
    $log = new InspectionLog();
    $log->inspection_request_id = $request_id;
    $log->action_id             = 3;
    $log->note                  = "{$vessel_name} ถูกลบคำขอโดย user_id={$deleter_user_id}";
    if (!$log->save()) {
        if ($in_tx) Database::$database->rollback();
        echo json_encode(['success' => false, 'message' => 'ลบสำเร็จ แต่บันทึก log ไม่สำเร็จ']);
        exit;
    }

    // 🔔 Notify เจ้าตัว (ผู้ยื่นคำขอ/หรือคนลบ)
    //create_notification($user_id, $user_role, $inspection_request_id, $action_id, $message, $notification_type = 'info') 
    //info/success/warning/error
    
    Notification::create_notification(
        $session->user_id(),
        $session->role,
        $request_id,
        4,//delete_request
        "เรือ {$vessel_name} : ลบคำขอตรวจสุขอนามัยเรียบร้อย",
        'warning'
    );

    // 🔔 Notify เจ้าหน้าที่ (เฉพาะกรณีผู้ลบคือผู้ยื่นคำขอเอง)
    
        $officers = Officer::find_by_department_id($department_id) ?: [];
        foreach ($officers as $officer) {
            Notification::create_notification(
                $officer->id,
                $session->map_usertype_id_to_role($sission->role),
                $request_id,
                4,//delete_request
                "เรือ {$vessel_name} : ผู้ยื่นคำขอลบคำขอตรวจสุขอนามัย",
                'warning'
            );
        }


    if ($in_tx) Database::$database->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (!empty($in_tx) && $in_tx === true) {
        Database::$database->rollback();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
