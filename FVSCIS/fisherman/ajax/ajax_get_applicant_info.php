<?php
require_once('../../../private/initialize.php');
$session->require_role(['fisherman']); // ปรับตาม role ที่คุณใช้

header('Content-Type: application/json; charset=utf-8');

try {
    $request_id = $_GET['request_id'] ?? '';
    $request_id = trim($request_id);

    if ($request_id === '') {
        throw new Exception('ไม่พบค่า request_id');
    }

    // ดึงคำขอตรวจ
    $request = InspectionRequest::find_by_id($request_id);
    if (!$request) {
        throw new Exception('ไม่พบข้อมูลคำขอตรวจ');
    }

    // ดึงข้อมูลผู้ยื่น
    $applicant = InspectionApplicantInfo::find_by_request_id($request_id);

    // default written_date = created_at หรือวันนี้
    $default_written_date = !empty($request->created_at)
        ? substr($request->created_at, 0, 10)
        : date('Y-m-d');

    // default written_at ไว้ก่อน (ถ้าอยากดึงจาก officer/department ค่อยเติมทีหลัง)
    $department_name = Department::find_by_id($request->department_id);
    $default_written_at = $department_name->name;

    $data = [
        // เขียนที่ / วันที่
        'written_at'   => $default_written_at,
        'written_date' => $default_written_date,
        'written_date_text'   => thai_date($default_written_date),

        // ประเภทผู้ยื่น
        'is_juristic'  => 0,

        // บุคคลธรรมดา / ผู้แทน
        'applicant_name'         => '',
        'applicant_age'          => '',
        'applicant_nationality'  => '',
        'applicant_address_no'   => '',
        'applicant_moo'          => '',
        'applicant_tambon'       => '',
        'applicant_amphoe'       => '',
        'applicant_province'     => '',
        'applicant_phone'        => '',

        // นิติบุคคล
        'juristic_name'          => '',
        'juristic_office'        => '',
        'juristic_address_no'    => '',
        'juristic_moo'           => '',
        'juristic_tambon'        => '',
        'juristic_amphoe'        => '',
        'juristic_province'      => '',

        // สถานะเอกสาร (เผื่อใช้ทำ UI)
        'form1_doc_number'       => '',
        'form1_locked'           => 0,
    ];

    if ($applicant) {
        $data['written_at']   = $applicant->written_at   ?? $default_written_at;
        $data['written_date'] = $applicant->written_date ?? $default_written_date;

        $data['is_juristic']  = (int)($applicant->is_juristic ?? 0);

        $data['applicant_name']         = $applicant->applicant_name ?? '';
        $data['applicant_age']          = $applicant->applicant_age ?? '';
        $data['applicant_nationality']  = $applicant->applicant_nationality ?? '';
        $data['applicant_address_no']   = $applicant->applicant_address_no ?? '';
        $data['applicant_moo']          = $applicant->applicant_moo ?? '';
        $data['applicant_tambon']       = $applicant->applicant_tambon ?? '';
        $data['applicant_amphoe']       = $applicant->applicant_amphoe ?? '';
        $data['applicant_province']     = $applicant->applicant_province ?? '';
        $data['applicant_phone']        = $applicant->applicant_phone ?? '';

        $data['juristic_name']          = $applicant->juristic_name ?? '';
        $data['juristic_office']        = $applicant->juristic_office ?? '';
        $data['juristic_address_no']    = $applicant->juristic_address_no ?? '';
        $data['juristic_moo']           = $applicant->juristic_moo ?? '';
        $data['juristic_tambon']        = $applicant->juristic_tambon ?? '';
        $data['juristic_amphoe']        = $applicant->juristic_amphoe ?? '';
        $data['juristic_province']      = $applicant->juristic_province ?? '';

        $data['form1_doc_number']       = $applicant->form1_doc_number ?? '';
        $data['form1_locked']           = (int)($applicant->form1_locked ?? 0);
    }

    echo json_encode([
        'success' => true,
        'data'    => $data,
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
