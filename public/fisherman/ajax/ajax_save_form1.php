<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // ---------- 1) รับค่า ----------
    $request_id           = trim($_POST['request_id']    ?? '');
    $written_at           = trim($_POST['written_at']    ?? '');
    $written_date         = trim($_POST['written_date']  ?? '');

    $is_juristic          = isset($_POST['is_juristic']) ? (int)$_POST['is_juristic'] : 0;

    $applicant_name       = trim($_POST['applicant_name']       ?? '');
    $applicant_age        = trim($_POST['applicant_age']        ?? '');
    $applicant_nationality= trim($_POST['applicant_nationality']?? '');
    $applicant_address_no = trim($_POST['applicant_address_no'] ?? '');
    $applicant_moo        = trim($_POST['applicant_moo']        ?? '');
    $applicant_tambon     = trim($_POST['applicant_tambon']     ?? '');
    $applicant_amphoe     = trim($_POST['applicant_amphoe']     ?? '');
    $applicant_province   = trim($_POST['applicant_province']   ?? '');
    $applicant_phone      = trim($_POST['applicant_phone']      ?? '');

    $juristic_name        = trim($_POST['juristic_name']        ?? '');
    $juristic_office      = trim($_POST['juristic_office']      ?? '');
    $juristic_address_no  = trim($_POST['juristic_address_no']  ?? '');
    $juristic_moo         = trim($_POST['juristic_moo']         ?? '');
    $juristic_tambon      = trim($_POST['juristic_tambon']      ?? '');
    $juristic_amphoe      = trim($_POST['juristic_amphoe']      ?? '');
    $juristic_province    = trim($_POST['juristic_province']    ?? '');

    if ($request_id === '') {
        throw new Exception('ไม่พบ request_id');
    }

    // ---------- 2) ตรวจสอบคำขอตรวจ ----------
    $request = InspectionRequest::find_by_id($request_id);
    if (!$request) {
        throw new Exception('ไม่พบข้อมูลคำขอตรวจ');
    }

    // ---------- 3) validate ข้อมูลจำเป็น ----------
    if ($written_at === '' || $written_date === '') {
        throw new Exception('กรุณากรอก "เขียนที่" และ "วันที่" ให้ครบถ้วน');
    }

    if ($applicant_name === '' || $applicant_address_no === '' ||
        $applicant_tambon === '' || $applicant_amphoe === '' ||
        $applicant_province === '') {
        throw new Exception('กรุณากรอกข้อมูลผู้ยื่นคำขอให้ครบถ้วน');
    }

    if ($is_juristic === 1 && $juristic_name === '') {
        throw new Exception('กรุณากรอกชื่อบริษัท/นิติบุคคล');
    }

    // ---------- 4) หา/สร้าง InspectionApplicantInfo ----------
    $applicant = InspectionApplicantInfo::find_by_request_id($request_id);
    if (!$applicant) {
        $applicant = new InspectionApplicantInfo();
        $applicant->request_id = $request_id;
        $applicant->created_at = date('Y-m-d H:i:s');
        $applicant->created_by = $session->user_id ?? null;
        $applicant->created_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    }

    $applicant->updated_at = date('Y-m-d H:i:s');
    $applicant->updated_by = $session->user_id ?? null;
    $applicant->updated_ip = $_SERVER['REMOTE_ADDR'] ?? '';

    // เขียนที่ / วันที่
    $applicant->written_at   = $written_at;
    $applicant->written_date = $written_date;

    // ประเภทผู้ยื่น
    $applicant->is_juristic = $is_juristic;

    // บุคคลธรรมดา / ผู้แทน
    $applicant->applicant_name        = $applicant_name;
    $applicant->applicant_age         = $applicant_age;
    $applicant->applicant_nationality = $applicant_nationality;
    $applicant->applicant_address_no  = $applicant_address_no;
    $applicant->applicant_moo         = $applicant_moo;
    $applicant->applicant_tambon      = $applicant_tambon;
    $applicant->applicant_amphoe      = $applicant_amphoe;
    $applicant->applicant_province    = $applicant_province;
    $applicant->applicant_phone       = $applicant_phone;

    // นิติบุคคล (ถ้าเป็นนิติ)
    if ($is_juristic === 1) {
        $applicant->juristic_name       = $juristic_name;
        $applicant->juristic_office     = $juristic_office;
        $applicant->juristic_address_no = $juristic_address_no;
        $applicant->juristic_moo        = $juristic_moo;
        $applicant->juristic_tambon     = $juristic_tambon;
        $applicant->juristic_amphoe     = $juristic_amphoe;
        $applicant->juristic_province   = $juristic_province;
    }

    // ---------- 5) ถ้ายังไม่มีเลขเอกสาร → gen ด้วย DocumentCounter ----------
    if (empty($applicant->form1_doc_number)) {

        $effective_date = $written_date ?: date('Y-m-d');

        // นับเลขเฉพาะเอกสาร สร.1 ทั้งประเทศ
        list($doc_code, $running, $doc_year) =
            DocumentCounter::next_code_by_effective('SR1', $effective_date);

        $applicant->form1_doc_number = $doc_code;
        $applicant->form1_locked     = 1;
        $applicant->form1_locked_at  = date('Y-m-d H:i:s');
        $applicant->form1_locked_by  = $session->user_id ?? null;
    }

    // ---------- 6) save ----------
    if (!$applicant->save()) {
        throw new Exception('บันทึกข้อมูลผู้ยื่นคำขอไม่สำเร็จ');
    }

    // log ลง php_error.log เผื่อ debug
    //error_log("[FVSCIS] FORM1 saved: request_id={$request_id}, doc={$applicant->form1_doc_number}");

    // URL หน้าพิมพ์ PDF
    $print_url = 'print_form1.php?request_id=' . urlencode($request_id);

    echo json_encode([
        'success'    => true,
        'message'    => 'บันทึกข้อมูลเรียบร้อยแล้ว',
        'doc_number' => $applicant->form1_doc_number,
        'locked'     => (int)$applicant->form1_locked,
        'print_url'  => $print_url,
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {

    //error_log("[FVSCIS] FORM1 error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
