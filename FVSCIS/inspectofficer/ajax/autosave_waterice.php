<?php
require_once('../../../private/initialize.php');
header('Content-Type: application/json');

try {
    if (!isset($_POST['request_id'], $_POST['field'], $_POST['value'])) {
        throw new Exception("ข้อมูลไม่ครบ");
    }

    $request_id = trim($_POST['request_id']);
    $field = trim($_POST['field']);
    $value = trim($_POST['value']);

    if (empty($request_id)) {
        throw new Exception("ไม่พบ request_id");
    }

    // เรียก autosave จากคลาส WaterAndIce
    $result = InspectionFormWaterAndIce::autosave($request_id, $field, $value);

    echo json_encode([
        'success' => $result,
        'message' => $result ? 'บันทึกสำเร็จ' : 'บันทกล้มเหลว',
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
