<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);

try {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) throw new Exception('ไม่พบ id');

    $obj = FvSanitationCertificationOld::find_by_id($id);
    if(!$obj) throw new Exception('ไม่พบรายการ');

    $data = [
      'id'                => $obj->id,
      'ship_code'         => $obj->ship_code,
      'vessel_name'       => $obj->vessel_name,
      'vessel_mark'       => $obj->vessel_mark,
      'license_number'    => $obj->license_number,
      'license_status'    => $obj->license_status,
      'gear_type'         => $obj->gear_type,
      'owner_name'        => $obj->owner_name,
      'certificate_number'=> $obj->certificate_number,
      'request_date'      => $obj->request_date,
      'signature_date'    => $obj->signature_date,
      'effective_date'    => $obj->effective_date,
      'expiration_date'   => $obj->expiration_date,
      'vessel_status'     => $obj->vessel_status,
      'evaluation_agency' => $obj->evaluation_agency,
      'signing_unit'      => $obj->signing_unit,
      'temporary_reason'  => $obj->temporary_reason,
      'responsible_unit'  => $obj->responsible_unit,
      'certificate_status'=> $obj->certificate_status,
      'remark'            => $obj->remark,
    ];

    echo json_encode(['success'=>true, 'data'=>$data], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
}
