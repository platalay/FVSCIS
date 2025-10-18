<?php
require_once('../../../private/initialize.php');

$request_id     = $_POST['request_id'] ?? null;
$approval_note  = $_POST['approval_note'] ?? null;

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

/**
 * helper: ตรวจว่าแบบฟอร์มเป็น EU หรือไม่
 * รองรับได้ทั้งการกำหนดเป็น string ('eu','EU') หรือมี class constant
 */
$is_eu = false;
$ift   = $request->inspection_form_type ?? null;
if (is_string($ift)) {
    $is_eu = (strtolower(trim($ift)) === 'eu');
}
// เผื่อระบบมีการนิยามเป็น class constant
if (!$is_eu && defined('InspectionRequest::FORM_TYPE_EU')) {
    $is_eu = ($ift === constant('InspectionRequest::FORM_TYPE_EU'));
}
if (!$is_eu && defined('InspectionRequest::TYPE_EU')) {
    $is_eu = ($ift === constant('InspectionRequest::TYPE_EU'));
}

/**
 * สร้างเลขเอกสารโดยอิง effective_date
 */
list($doc_code, $running_no, $doc_year) = DocumentCounter::next_code_by_effective(
    $request->department_group_id,
    $effective_date
);

// เก็บผลประเมินก่อนเปลี่ยนสถานะเป็น COMPLETED (ผ่าน/ชั่วคราว/ไม่ผ่าน)
$evaluation_status = $request->status;

$now    = date('Y-m-d H:i:s');
$expire = date('Y-m-d', strtotime('+2 years'));

$request->approved_by      = $user_id;
$request->approved_at      = $now;
$request->approved_ip      = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$request->effective_date   = $effective_date;
$request->temporary_reason = $temporary_reason;
$request->approval_note    = $approval_note;
$request->expire_at        = $expire;
$request->status           = InspectionRequest::STATUS_COMPLETED;

$InspectionFormStatus = InspectionFormStatus::find_by_request_id($request->id);
$departmentgroup      = DepartmentGroup::find_by_id($request->department_group_id);

/**
 * map: ผลประเมิน -> certification_status (5 แบบ)
 * - PASSED         : 'สร.3 EU' หรือ 'สร.3'
 * - CONDITIONAL    : 'สร.3 EU ชั่วคราว' หรือ 'สร.3 ชั่วคราว (condition)'
 * - FAILED         : 'ไม่ผ่าน'
 */
$certification_status = 'ไม่ระบุ';
switch ($evaluation_status) {
    case InspectionRequest::STATUS_PASSED:
        $certification_status = $is_eu ? 'สร.3 EU' : 'สร.3';
        break;
    case InspectionRequest::STATUS_CONDITIONAL:
        $certification_status = $is_eu ? 'สร.3 EU ชั่วคราว' : 'สร.3 ชั่วคราว (condition)';
        break;
    case InspectionRequest::STATUS_FAILED:
        $certification_status = 'ไม่ผ่าน';
        break;
    default:
        // กรณีอื่น ๆ คงค่า 'ไม่ระบุ' ไว้
        break;
}

if ($request->save()) {
    // ✅ บันทึกซ้ำไปที่ fv_sanitation_certification_old
    $old = new FvSanitationCertificationOld();
    $old->vessel_name         = $request->vessel_name;
    $old->ship_code           = $request->ship_code;
    $old->vessel_mark         = $request->vessel_mark;
    $old->license_number      = $request->license_number;
    $old->gear_type           = $request->gear_type;
    $old->owner_name          = $request->owner_name;
    $old->certificate_number  = $doc_code;
    $old->request_date        = $request->submitted_at;
    $old->signature_date      = $request->approved_at;
    $old->effective_date      = $request->effective_date;
    $old->expiration_date     = $request->expire_at;

    // หมายเหตุ: vessel_status ใน old เก็บสถานะคำขอปัจจุบัน (COMPLETED) ตาม flow เดิม
    $old->vessel_status       = $request->status;

    $old->evaluation_agency   = $request->department_id;
    $old->signing_unit        = $request->department_group_id;
    $old->temporary_reason    = $request->temporary_reason;
    $old->responsible_unit    = $departmentgroup->responsible_unit;

    // 🔁 เปลี่ยนมาเขียนลง 'certification_status' ตามที่ต้องการ (แทนของเดิม)
    $old->certification_status = $certification_status;

    // ถ้า schema เก่ายังมีฟิลด์ certificate_status และต้องการคง compatibility ไว้
    // ให้คอมเมนต์บรรทัดด้านล่างนี้ออก ถ้าไม่ต้องการก็ลบทิ้งได้
    // $old->certificate_status = $certification_status;

    $old->remark              = $request->approval_note;
    $old->type                = 1; // ✅ online

    $old->save();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'บันทึกข้อมูลไม่สำเร็จ']);
}
