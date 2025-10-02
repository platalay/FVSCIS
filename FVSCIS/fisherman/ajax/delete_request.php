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

    // ปั้นข้อความเรือให้อ่านง่าย
    $vessel_tag = $vessel_name !== '' ? "เรือ {$vessel_name}" : 'เรือไม่ระบุชื่อ';
    if ($ship_code !== '')  { $vessel_tag .= " ({$ship_code})"; }
    if ($license_no !== '') { $vessel_tag .= " เลขใบอนุญาต {$license_no}"; }

    // ตรวจ role (ปรับตาม method จริงของระบบคุณ)
    $is_admin   = method_exists($session, 'is_admin')   ? (bool)$session->is_admin()   : false;
    $is_officer = method_exists($session, 'is_officer') ? (bool)$session->is_officer() : false;

    // เลือก action code ตามผู้ลบ
    if ($deleter_user_id === (int)$request_owner_id) {
        $action_code = 'fisher_delete_request';
    } elseif ($is_admin) {
        $action_code = 'admin_delete_request';
    } elseif ($is_officer) {
        $action_code = 'officer_delete_request';
    } else {
        $action_code = 'officer_delete_request'; // fallback
    }

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

    // ✅ หา action
    $action = LogAction::find_by_code($action_code);
    if (!$action) {
        $action = LogAction::find_by_code('submitted'); // กันพัง
        if (!$action) {
            throw new Exception("ไม่พบรหัส action '{$action_code}' และ 'submitted'");
        }
    }

    // 📝 Log การลบ
    $log = new InspectionLog();
    $log->inspection_request_id = $request_id;
    $log->action_id             = $action->id;
    $log->note                  = "{$vessel_tag} ถูกลบคำขอโดย user_id={$deleter_user_id}";
    $log->performed_by          = $deleter_user_id;
    $log->performed_at          = date('Y-m-d H:i:s');
    $log->target_department_id  = $department_id;
    // สมมติ: 1=admin, 2=fisherman, 3=officer (ปรับตามระบบจริง)
    $log->target_usertype_id    = ($deleter_user_id === (int)$request_owner_id) ? 2 : ($is_admin ? 1 : 3);
    $log->port_license_no       = $port_license_no;

    if (!$log->save()) {
        if ($in_tx) Database::$database->rollback();
        echo json_encode(['success' => false, 'message' => 'ลบสำเร็จ แต่บันทึก log ไม่สำเร็จ']);
        exit;
    }

    // 🔔 Notify เจ้าตัว (ผู้ยื่นคำขอ/หรือคนลบ)
    Notification::create_notification(
        $request_owner_id ?: $deleter_user_id,
        'fisherman',
        "{$vessel_tag} : ลบคำขอตรวจสุขอนามัยเรียบร้อย",
        'info',
        'inspection_request',
        $request_id,
        $log->id
    );

    // 🔔 Notify เจ้าหน้าที่ (เฉพาะกรณีผู้ลบคือผู้ยื่นคำขอเอง)
    if ($deleter_user_id === (int)$request_owner_id && $department_id) {
        $officers = Officer::find_by_department_id($department_id) ?: [];
        foreach ($officers as $officer) {
            Notification::create_notification(
                $officer->id,
                'inspectofficer',
                "{$vessel_tag} : ผู้ยื่นคำขอลบคำขอตรวจสุขอนามัย",
                'info',
                'inspection_request',
                $request_id,
                $log->id
            );
        }
    }

    if ($in_tx) Database::$database->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (!empty($in_tx) && $in_tx === true) {
        Database::$database->rollback();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
