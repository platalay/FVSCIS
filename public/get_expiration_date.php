<?php
header('Content-Type: application/json');
require_once('../private/initialize.php'); // ปรับ path ตามจริง

$ship_code = $_GET['ship_code'] ?? '';

if (empty($ship_code)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing ship_code']);
    exit;
}

// ค้นหาข้อมูล
$record = FvSanitationCertificationOld::find_one_by_ship_code($ship_code);

if ($record) {
    echo json_encode([
        'ship_code' => $record->ship_code,
        'vessel_name' => $record->vessel_name,
        'request_date' => $record->request_date,
        'signature_date' => $record->signature_date,
        'effective_date' => $record->effective_date,
        'expiration_date' => $record->expiration_date
    ]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
}
