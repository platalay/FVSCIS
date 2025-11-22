<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? '';
    $field = $_POST['field'] ?? '';
    $value = $_POST['value'] ?? '';

    if (!$request_id || !$field) {
        http_response_code(400);
        echo "Missing request_id or field";
        exit;
    }

    $success = InspectionFormStructure::autosave($request_id, $field, $value);
    echo $success ? "saved" : "error";
}
