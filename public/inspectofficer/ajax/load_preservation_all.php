<?php

require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? '';

    if (!$request_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ไม่พบ request_id']);
        exit;
    }

    $record = InspectionFormPreservation::find_or_create($request_id);

    if ($record) {
        echo json_encode([
            'success' => true,
            'data' => $record
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบข้อมูล'
        ]);
    }
}

