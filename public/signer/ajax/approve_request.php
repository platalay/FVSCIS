<?php
require_once('../../../private/initialize.php');
header('Content-Type: application/json; charset=utf-8');

global $database;

try {

    $request_id      = $_POST['request_id'] ?? null;
    $approval_note   = $_POST['approval_note'] ?? null;
    $temporary_reason = $_POST['temporary_reason'] ?? null;
    $user_id         = $session->user_id();
    $currentUserId = $user_id; 
    // ===== effective_date =====
    $effective_raw  = $_POST['effective_date'] ?? '';
    $effective_date = null;

    if ($effective_raw !== '') {
        $dt = DateTime::createFromFormat('Y-m-d', substr($effective_raw, 0, 10));
        if (!$dt) $dt = DateTime::createFromFormat('d/m/Y', $effective_raw);
        if ($dt) $effective_date = $dt->format('Y-m-d');
    }

    if (!$effective_date) {
        echo json_encode(['success' => false, 'message' => 'กรุณาเลือกวันที่มีผล (effective date)']);
        exit;
    }

    if (!$request_id || !$user_id) {
        echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
        exit;
    }

    $request = InspectionRequest::find_by_id($request_id);
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบคำขอนี้']);
        exit;
    }

    // เก็บผลประเมินเดิม (passed/conditional) ไว้ใช้ตลอด logic นี้ ก่อนที่ status จะถูกปิดเป็น completed
    $evaluation_status = $request->status;

    // ===== กัน approve ซ้ำ (ตรวจเบื้องต้นนอก transaction ก่อน — จะถูกตรวจซ้ำแบบ lock อีกครั้งด้านล่าง) =====
    if ((int)($request->is_complete ?? 0) === 1
        || $request->status === InspectionRequest::STATUS_COMPLETED
        || !empty($request->approved_at)) {
        echo json_encode(['success' => false, 'message' => 'คำขอนี้ได้รับการอนุมัติไปแล้ว']);
        exit;
    }

    // ===== กันพลาด: approve ได้เฉพาะ PASSED / CONDITIONAL =====
    if (!in_array($evaluation_status, [InspectionRequest::STATUS_PASSED, InspectionRequest::STATUS_CONDITIONAL], true)) {
        echo json_encode(['success' => false, 'message' => 'สถานะคำขอไม่อยู่ในเงื่อนไขการออกใบรับรอง']);
        exit;
    }

    // ===== ตรวจ EU หรือไม่ (รองรับทั้ง 1/2 หรือ string) =====
    $is_eu = false;
    $ift   = $request->inspection_form_type ?? null;

    if (is_numeric($ift)) {
        $is_eu = ((int)$ift === 2);
    } elseif (is_string($ift)) {
        $is_eu = (strtolower(trim($ift)) === 'eu');
    }

    if (!$is_eu && defined('InspectionRequest::FORM_TYPE_EU')) {
        $is_eu = ($ift === constant('InspectionRequest::FORM_TYPE_EU'));
    }
    if (!$is_eu && defined('InspectionRequest::TYPE_EU')) {
        $is_eu = ($ift === constant('InspectionRequest::TYPE_EU'));
    }

    // ===== map certification_status + expire_at ตามผลประเมิน =====
    $certification_status = 'ไม่ระบุ';
    $expire_at = null;

    switch ($evaluation_status) {
        case InspectionRequest::STATUS_PASSED:
            $certification_status = $is_eu ? 'สร.3 EU' : 'สร.3';
            $expire_at = date('Y-m-d', strtotime($effective_date . ' +2 years'));
            $status = 'active';
            break;

        case InspectionRequest::STATUS_CONDITIONAL:
            $certification_status = $is_eu ? 'สร.3 EU ชั่วคราว' : 'สร.3 ชั่วคราว';
            $expire_at = date('Y-m-d', strtotime($effective_date . ' +90 days'));
            $status = 'active';
            break;
        case InspectionRequest::STATUS_FAILED:
            $certification_status = 'ไม่ผ่าน';
            $expire_at = date('Y-m-d', strtotime($effective_date));
            $status = 'inactive';
            break;
    }

    if (!$expire_at) {
        echo json_encode(['success' => false, 'message' => 'คำนวณวันหมดอายุไม่สำเร็จ']);
        exit;
    }

    $departmentgroup = DepartmentGroup::find_by_id($request->department_group_id);
    if (!$departmentgroup) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลหน่วยงานผู้ลงนาม']);
        exit;
    }

    // ===== Transaction start =====
    $database->begin_transaction();

    // ===== ล็อกแถว request กัน race condition (เปิดสองแท็บ/กดซ้ำพร้อมกัน) แล้วตรวจซ้ำว่ายังไม่ถูกอนุมัติ =====
    $request_id_esc = $database->escape_string((string)$request->id);
    $lock_sql = "SELECT is_complete, status, approved_at FROM inspection_requests WHERE id = '{$request_id_esc}' FOR UPDATE";
    $lock_result = $database->query($lock_sql);
    $lock_row = $lock_result ? $lock_result->fetch_assoc() : null;

    if (!$lock_row
        || (int)($lock_row['is_complete'] ?? 0) === 1
        || $lock_row['status'] === InspectionRequest::STATUS_COMPLETED
        || !empty($lock_row['approved_at'])) {
        $database->rollback();
        echo json_encode(['success' => false, 'message' => 'คำขอนี้ได้รับการอนุมัติไปแล้ว']);
        exit;
    }

    // ===== สร้างเลขเอกสารโดยอิง effective_date (ต้องอยู่หลัง guard กันอนุมัติซ้ำเสมอ) =====
    list($doc_code, $running_no, $doc_year) = DocumentCounter::next_code_by_effective(
        $request->department_group_id,
        $effective_date
    );

    $now = date('Y-m-d H:i:s');

    // 1) update request — ปิดคำขอให้จบ workflow เมื่ออนุมัติสำเร็จ
    $request->approved_by      = $user_id;
    $request->approved_at      = $now;
    $request->approved_ip      = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $request->effective_date   = $effective_date;
    $request->temporary_reason = $temporary_reason;
    $request->approval_note    = $approval_note;
    $request->expire_at        = $expire_at;
    $request->is_complete      = 1;
    $request->status           = InspectionRequest::STATUS_COMPLETED;

    if (!$request->save()) {
        $database->rollback();
        echo json_encode(['success' => false, 'message' => 'บันทึกข้อมูลคำขอไม่สำเร็จ']);
        exit;
    }

    // 2) insert old
    $old = new FvSanitationCertificationOld();
    $old->vessel_name          = $request->vessel_name;
    $old->ship_code            = $request->ship_code;
    if ($request->is_manual_case == 0) {

        // กรณีชาวประมงสร้างคำขอเอง
        if (!empty($request->created_by)) {
            $old->fisherman_id = $request->created_by;
        }

    } else {

        // กรณีเจ้าหน้าที่สร้างคำขอ → ดึงจาก eLicense
        $elicense = Elicense::find_one_by_ship_code($el_db, $request->ship_code);

        if ($elicense && !empty($elicense->nationality_id)) {

            $fisherman = Fisherman::find_by_citizen_id($elicense->nationality_id);

            if ($fisherman && !empty($fisherman->id)) {
                $old->fisherman_id = $fisherman->id;
            }
            // ถ้าไม่เจอ fisherman → ไม่ set ค่า

        }
        // ถ้าไม่เจอ elicense → ไม่ set ค่า
    }
    $old->vessel_mark          = $request->vessel_mark;
    $old->license_number       = $request->license_number;
    $old->gear_type            = $request->gear_type;
    $old->owner_name           = $request->owner_name;

    $old->certificate_number   = $doc_code;
    $old->request_date         = $request->submitted_at;
    $old->signature_date       = $request->approved_at;
    $old->effective_date       = $request->effective_date;
    $old->expiration_date      = $request->expire_at;
    $signature_date = thai_date($request->effective_date);
    // เก็บผลตรวจเดิม (passed/conditional) เป็น snapshot — ห้ามเก็บ 'completed' แทนผลตรวจ
    $old->vessel_status        = $evaluation_status;

    $old->evaluation_agency    = $request->department_id;
    $old->signing_unit         = $request->department_group_id;
    $old->temporary_reason     = $request->temporary_reason;
    $old->responsible_unit     = $departmentgroup->responsible_unit;
    $old->status               = $status;
    $old->certificate_status = $certification_status; //สถานะ สร.3

    $old->remark               = $request->approval_note;
    $old->type                 = 1; // online

    if (!$old->save()) {
        $database->rollback();
        echo json_encode(['success' => false, 'message' => 'บันทึกข้อมูลใบรับรอง (old) ไม่สำเร็จ']);
        exit;
    }

    // 3) ใบใหม่ออกสำเร็จแล้ว → ปิดใบ active เดิมของเรือลำนี้เป็น inactive (คงแถวเดิมไว้เป็น history)
    FvSanitationCertificationOld::deactivate_other_active($request->ship_code, (int)$old->id);

    // 4) LOG + Notification (ยังอยู่ใน Transaction) ผ่าน กัน ผ่านแบบมีเงือนไข
    if($evaluation_status == InspectionRequest::STATUS_PASSED || $evaluation_status == InspectionRequest::STATUS_CONDITIONAL){ 
    $action = LogAction::find_by_code('inspection_passed');
    $officer = Officer::find_by_id($currentUserId);
        if ($action) {
            $log = new InspectionLog();
            $log->inspection_request_id = $request->id;
            $log->action_id             = $action->id;
            $log->note = "เรือ {$request->vessel_name} หมายเลขทะเบียน {$request->ship_code} ได้รับการอนุมัติ {$certification_status} โดย {$officer->full_name} ในวันที่ {$signature_date}";
            $log->save();

            Notification::create_notification(
            $request->created_by,
            'fisherman',
            $request->id,
            $log->action_id,
            $log->note,
            'warning'
            );
        }
    }

    // 4) LOG + Notification (ยังอยู่ใน Transaction) ไม่ผ่าน
    if($evaluation_status == InspectionRequest::STATUS_FAILED){ 
    $action = LogAction::find_by_code('fail_notice_signed');
    $officer = Officer::find_by_id($currentUserId);
        if ($action) {
            $log = new InspectionLog();
            $log->inspection_request_id = $request->id;
            $log->action_id             = $action->id;
            $log->note = "เรือ {$request->vessel_name} หมายเลขทะเบียน {$request->ship_code} ได้รับการยืนยันผลการตรวจไม่ผ่านโดย {$officer->full_name} ในวันที่ {$signature_date}";
            $log->save();

            Notification::create_notification(
            $request->created_by,
            'fisherman',
            $request->id,
            $log->action_id,
            $log->note,
            'warning'
            );
        }
    }

     


    $database->commit();
    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    if (isset($database)) { @$database->rollback(); }
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
