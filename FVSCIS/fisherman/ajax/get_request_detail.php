<?php
require_once('../../../private/initialize.php');
header('Content-Type: application/json');

try {
    $request_id = $_POST['request_id'] ?? null;
    if (!$request_id) throw new Exception("ไม่พบ request_id");

    $req = InspectionRequest::find_by_id($request_id);
    if (!$req) throw new Exception("ไม่พบคำขอ");

    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $req->id,
            'confirmed_inspect_date' => $req->confirmed_inspect_date
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
