<?php
require_once('../../../private/initialize.php');
header('Content-Type: application/json; charset=utf-8');

global $database;

try {

    $request_id    = $_POST['request_id'] ?? null;
    $approval_note = $_POST['approval_note'] ?? null;
    $user_id       = $session->user_id();

    // effective_date (รองรับ type=date และ dd/mm/yyyy)
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

    // confirm fail endpoint ต้องเป็น failed เท่านั้น
    if ($request->status !== InspectionRequest::STATUS_FAILED) {
        echo json_encode(['success' => false, 'message' => 'สถานะคำขอไม่อยู่ในเงื่อนไข “ไม่ผ่านการตรวจ”']);
        exit;
    }

    $departmentgroup = DepartmentGroup::find_by_id($request->department_group_id);
    if (!$departmentgroup) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลหน่วยงานผู้ลงนาม']);
        exit;
    }

    $database->begin_transaction();

    $now = date('Y-m-d H:i:s');

    // 1) save inspection request ก่อน
    $request->approved_by    = $user_id;
    $request->approved_at    = $now;
    $request->approved_ip    = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $request->effective_date = $effective_date;
    $request->approval_note  = $approval_note;

    // ถ้ามีสถานะใหม่ค่อยเปิดใช้
    // $request->status = InspectionRequest::STATUS_FAILED_CONFIRMED;

    if (!$request->save()) {
        $database->rollback();
        echo json_encode(['success' => false, 'message' => 'บันทึกการยืนยันผลไม่ผ่านไม่สำเร็จ']);
        exit;
    }

    // 2) insert record ใหม่ลง old (ให้กลายเป็น latest ทันที)
    $old = new FvSanitationCertificationOld();
    $old->vessel_name          = $request->vessel_name;
    $old->ship_code            = $request->ship_code;
    $old->vessel_mark          = $request->vessel_mark;
    $old->license_number       = $request->license_number;
    $old->gear_type            = $request->gear_type;
    $old->owner_name           = $request->owner_name;

    // ไม่ผ่าน → ไม่มีเลขใบ/ไม่มีหมดอายุ
    $old->certificate_number   = null; // หรือ '-' ถ้า schema บังคับ NOT NULL
    $old->request_date         = $request->created_at;
    $old->signature_date       = $request->approved_at;
    $old->effective_date       = $request->effective_date;
    $old->expiration_date      = null;

    // สถานะคำขอปัจจุบัน (failed)
    $old->vessel_status        = $request->status;

    $old->evaluation_agency    = $request->department_id;
    $old->signing_unit         = $request->department_group_id;
    $old->temporary_reason     = null;
    $old->responsible_unit     = $departmentgroup->responsible_unit;

    // ✅ ตามที่คุณใช้จริง
    $old->certification_status = 'ไม่ผ่าน';

    $old->remark               = $request->approval_note;
    $old->type                 = 1; // online

    // ✅ status สำหรับตาราง old (ให้สอดคล้อง mark ล่าสุด)
    $old->status               = 'fail';

    if (!$old->save()) {
        $database->rollback();
        echo json_encode(['success' => false, 'message' => 'บันทึกข้อมูลผลไม่ผ่านในตารางประวัติใบรับรองไม่สำเร็จ']);
        exit;
    }

    // 3) เคลียร์ pending เก่าของเรือลำนี้ (กันค้าง/กันนับทั้งตารางเพี้ยน)
    //    ทำเฉพาะแถวเก่าที่เป็น pending เท่านั้น
    $new_id    = (int)($old->id ?? 0);
    $ship_code = $database->escape_string($request->ship_code); // ถ้าอยู่ใน class ใช้ self::$database; ถ้าไม่ ให้ใช้ $database

    // ใช้ $database (global) จะชัวร์ในไฟล์นี้:
    $ship_code = $database->escape_string($request->ship_code);
    $new_id_esc = $database->escape_string((string)$new_id);

    $sql_clear = "UPDATE fv_sanitation_certification_old
                 SET status = 'inactive'
                 WHERE ship_code = '{$ship_code}'
                   AND status = 'pending'
                   AND id <> '{$new_id_esc}'";

    $ok_clear = $database->query($sql_clear);
    if (!$ok_clear) {
        $database->rollback();
        echo json_encode(['success' => false, 'message' => 'เคลียร์สถานะ pending เดิมไม่สำเร็จ']);
        exit;
    }

    $database->commit();
    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    if (isset($database)) { @$database->rollback(); }
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
