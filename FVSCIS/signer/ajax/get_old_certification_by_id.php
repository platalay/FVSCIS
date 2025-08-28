<?php
declare(strict_types=1);

require_once('../../../private/initialize.php');
$session->require_role(['signer']);
header('Content-Type: application/json; charset=utf-8');

try {
    // รับและตรวจสอบ id
    $idParam = $_GET['id'] ?? '';
    if ($idParam === '' || !ctype_digit((string)$idParam)) {
        throw new Exception('ไม่มีรหัสข้อมูลหรือรูปแบบไม่ถูกต้อง');
    }
    $id = (int)$idParam;

    // ค้นหาข้อมูล
    /** @var FvSanitationCertificationOld|null $rec */
    $rec = FvSanitationCertificationOld::find_by_id($id);
    if (!$rec) {
        throw new Exception('ไม่พบข้อมูลตามรหัสที่ระบุ');
    }

    // เตรียมข้อมูลตอบกลับ
    $data = [
        'id'                  => $rec->id,
        'vessel_name'         => $rec->vessel_name,
        'ship_code'           => $rec->ship_code,
        'vessel_mark'         => $rec->vessel_mark,
        'license_number'      => sci_to_plain((string)$rec->license_number),
        'gear_type'           => $rec->gear_type,
        'owner_name'          => $rec->owner_name,
        'certificate_number'  => $rec->certificate_number,
        'request_date'        => $rec->request_date,
        'signature_date'      => $rec->signature_date,
        'effective_date'      => $rec->effective_date,
        'expiration_date'     => $rec->expiration_date,
        'vessel_status'       => $rec->vessel_status,
        'evaluation_agency'   => Department::get_name_by_id($rec->evaluation_agency),
        'signing_unit'        => DepartmentGroup::get_name_by_id($rec->signing_unit),
        'temporary_reason'    => $rec->temporary_reason,
        'responsible_unit'    => DepartmentGroup::get_name_by_id($rec->responsible_unit),
        'certificate_status'  => $rec->certificate_status,
        'remark'              => $rec->remark,
    ];

    echo json_encode([
        'success' => true,
        'data'    => $data
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $ex) {
    echo json_encode([
        'success' => false,
        'message' => $ex->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
