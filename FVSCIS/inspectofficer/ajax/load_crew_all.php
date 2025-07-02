<?php
require_once('../../../private/initialize.php');
header('Content-Type: application/json');

try {
    if (!isset($_POST['request_id'])) {
        throw new Exception("ไม่ได้รับ request_id");
    }

    $request_id = trim($_POST['request_id']);

    if (empty($request_id)) {
        throw new Exception("request_id ว่างเปล่า");
    }

    $record = InspectionFormCrew::find_or_create($request_id);

    $data = [];
    foreach (InspectionFormCrew::$db_columns as $column) {
        if ($column === 'id' || $column === 'request_id') {
            continue;
        }
        $data[$column] = $record->$column ?? '';
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

