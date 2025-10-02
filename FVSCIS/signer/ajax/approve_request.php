<?php
require_once('../../../private/initialize.php');

$request_id = $_POST['request_id'] ?? null;
$approval_note = $_POST['approval_note'] ?? null;
// รับค่าดิบจากฟอร์ม
$effective_raw = $_POST['effective_date'] ?? '';

// แปลงให้ได้ Y-m-d (รองรับทั้ง type=date และ dd/mm/yyyy)
$effective_date = null;
if ($effective_raw !== '') {
    $dt = DateTime::createFromFormat('Y-m-d', substr($effective_raw, 0, 10));
    if (!$dt) {
        // เผื่อกรอก/ส่งมาแบบไทย dd/mm/YYYY
        $dt = DateTime::createFromFormat('d/m/Y', $effective_raw);
    }
    if ($dt) {
        $effective_date = $dt->format('Y-m-d');
    }
}

// ถ้าไม่มี effective_date ที่ถูกต้อง ให้หยุดก่อน
if (!$effective_date) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเลือกวันที่มีผล (effective date)']);
    exit;
}
$temporary_reason = $_POST['temporary_reason'] ?? null;
$user_id = $session->user_id();

if (!$request_id || !$user_id) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$request = InspectionRequest::find_by_id($request_id);

if (!$request) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบคำขอนี้']);
    exit;
}

list($doc_code, $running_no, $doc_year) = DocumentCounter::next_code_by_effective(
    $request->department_group_id,
    $effective_date   // ต้องเป็น Y-m-d
);
$type = $request->status; //เก็บค่า evluation ผ่าน ไม่ผ่าน

$now = date('Y-m-d H:i:s');
$expire = date('Y-m-d', strtotime('+2 years'));
$request->approved_by = $user_id;
$request->approved_at = $now;
$request->approved_ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$request->effective_date = $effective_date;
$request->temporary_reason = $temporary_reason;
$request->approval_note = $approval_note;
$request->expire_at = $expire;
$request->status = InspectionRequest::STATUS_COMPLETED;
$InspectionFormStatus = InspectionFormStatus::find_by_request_id($request->id);
$departmentgroup = DepartmentGroup::find_by_id($request->department_group_id);

if ($request->save()) {
    // ✅ บันทึกซ้ำไปที่ fv_sanitation_certification_old
    $old = new FvSanitationCertificationOld();
    $old->vessel_name      = $request->vessel_name;
    $old->ship_code        = $request->ship_code;
    $old->vessel_mark      = $request->vessel_mark;
    $old->license_number   = $request->license_number;
    $old->gear_type        = $request->gear_type;
    $old->owner_name       = $request->owner_name;
    $old->certificate_number = $doc_code;
    $old->request_date     = $request->submitted_at;
    $old->signature_date   = $request->approved_at;
    $old->effective_date   = $request->effective_date;
    $old->expiration_date  = $request->expire_at;
    $old->vessel_status    = $request->status;
    $old->evaluation_agency = $request->department_id;
    $old->signing_unit     = $request->department_group_id;
    $old->temporary_reason = $request->temporary_reason;
    $old->responsible_unit = $departmentgroup->responsible_unit;
    $old->certificate_status = $type; // อยู่ใน InspectionRequest class
    $old->remark           = $request->approval_note;
    $old->type             = 1; // ✅ online

    $old->save();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'บันทึกข้อมูลไม่สำเร็จ']);
}

