<?php
require_once('../../../private/initialize.php');

$request_id = $_POST['request_id'] ?? null;
$approval_note = $_POST['approval_note'] ?? null;
$effective_date = $_POST['effective_date'] ?? null;
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
if ($request->save()) {
    // ✅ บันทึกซ้ำไปที่ fv_sanitation_certification_old
    $old = new FvSanitationCertificationOld();
    $old->vessel_name      = $request->vessel_name;
    $old->ship_code        = $request->ship_code;
    $old->vessel_mark      = $request->vessel_mark;
    $old->license_number   = $request->license_number;
    $old->gear_type        = $request->gear_type;
    $old->owner_name       = $request->owner_name;
    $old->certificate_number = $InspectionFormStatus->document_number; // ใช้ id request แทน หรือ generate เลขใหม่ตามที่คุณต้องการ
    $old->request_date     = $request->submitted_at;
    $old->signature_date   = $request->approved_at;
    $old->effective_date   = $request->effective_date;
    $old->expiration_date  = $request->expire_at;
    $old->vessel_status    = $request->status;
    $old->evaluation_agency = $request->department_id;
    $old->signing_unit     = $request->department_group_id;
    $old->temporary_reason = $request->temporary_reason;
    $old->responsible_unit = $request->department_id;
    $old->certificate_status = 'active'; // หรือ set ตามเงื่อนไข
    $old->remark           = $request->approval_note;
    $old->type             = 1; // ✅ online

    $old->save();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'บันทึกข้อมูลไม่สำเร็จ']);
}

