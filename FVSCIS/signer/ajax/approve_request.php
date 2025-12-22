<?php
require_once('../../../private/initialize.php');
header('Content-Type: application/json; charset=utf-8');

global $database;

try {

    $request_id      = $_POST['request_id'] ?? null;
    $approval_note   = $_POST['approval_note'] ?? null;
    $temporary_reason = $_POST['temporary_reason'] ?? null;
    $user_id         = $session->user_id();

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

    // เก็บผลประเมินก่อนเปลี่ยนเป็น COMPLETED
    $evaluation_status = $request->status;

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
            break;

        case InspectionRequest::STATUS_CONDITIONAL:
            $certification_status = $is_eu ? 'สร.3 EU ชั่วคราว' : 'สร.3 ชั่วคราว';
            $expire_at = date('Y-m-d', strtotime($effective_date . ' +90 days'));
            break;
    }

    if (!$expire_at) {
        echo json_encode(['success' => false, 'message' => 'คำนวณวันหมดอายุไม่สำเร็จ']);
        exit;
    }

    // ===== สร้างเลขเอกสารโดยอิง effective_date =====
    list($doc_code, $running_no, $doc_year) = DocumentCounter::next_code_by_effective(
        $request->department_group_id,
        $effective_date
    );

    $departmentgroup = DepartmentGroup::find_by_id($request->department_group_id);
    if (!$departmentgroup) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลหน่วยงานผู้ลงนาม']);
        exit;
    }

    // ===== Transaction start =====
    $database->begin_transaction();

    $now = date('Y-m-d H:i:s');

    // 1) update request
    $request->approved_by      = $user_id;
    $request->approved_at      = $now;
    $request->approved_ip      = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $request->effective_date   = $effective_date;
    $request->temporary_reason = $temporary_reason;
    $request->approval_note    = $approval_note;
    $request->expire_at        = $expire_at;
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
    $old->vessel_mark          = $request->vessel_mark;
    $old->license_number       = $request->license_number;
    $old->gear_type            = $request->gear_type;
    $old->owner_name           = $request->owner_name;

    $old->certificate_number   = $doc_code;
    $old->request_date         = $request->submitted_at;
    $old->signature_date       = $request->approved_at;
    $old->effective_date       = $request->effective_date;
    $old->expiration_date      = $request->expire_at;

    // เก็บสถานะคำขอปัจจุบัน (COMPLETED)
    $old->vessel_status        = $request->status;

    $old->evaluation_agency    = $request->department_id;
    $old->signing_unit         = $request->department_group_id;
    $old->temporary_reason     = $request->temporary_reason;
    $old->responsible_unit     = $departmentgroup->responsible_unit;

    $old->certification_status = $certification_status;

    $old->remark               = $request->approval_note;
    $old->type                 = 1; // online

    if (!$old->save()) {
        $database->rollback();
        echo json_encode(['success' => false, 'message' => 'บันทึกข้อมูลใบรับรอง (old) ไม่สำเร็จ']);
        exit;
    }

    $database->commit();
    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    if (isset($database)) { @$database->rollback(); }
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
