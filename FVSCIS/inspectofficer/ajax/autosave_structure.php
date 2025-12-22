<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

$request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
$field      = trim($_POST['field'] ?? '');
$value      = $_POST['value'] ?? '';

// ตรวจค่าเบื้องต้น
if ($request_id <= 0 || $field === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing request_id or field'
    ]);
    exit;
}

// optional: กัน field แปลก ๆ
$allowed_prefixes = ['status_', 'fail_', 'remark_'];
$ok = false;
foreach ($allowed_prefixes as $prefix) {
    if (strpos($field, $prefix) === 0) {
        $ok = true;
        break;
    }
}
if (!$ok) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid field name'
    ]);
    exit;
}

/**
 * 🔒 ดักล็อกเอกสาร: ส่งยืนยันผลแล้ว → ไม่ autosave
 * ใช้ response 200 + locked=true เพื่อให้ JS แจ้งเตือนได้ทันที
 */
$formStatus = InspectionFormStatus::find_by_request_id($request_id); // ปรับชื่อเมธอดถ้าคุณใช้ต่างออกไป

if ($formStatus && (int)$formStatus->document_locked === 1) {
    echo json_encode([
        'success' => false,
        'locked'  => true,
        'field'   => $field,
        'value'   => $value,
        'message' => 'เอกสารถูกยืนยันผลแล้ว ไม่สามารถบันทึกการแก้ไขได้'
    ]);
    exit;
}

// autosave สำหรับหมวดโครงสร้าง (structure)
$success = InspectionFormStructure::autosave($request_id, $field, $value);

echo json_encode([
    'success' => (bool)$success,
    'locked'  => false,
    'field'   => $field,
    'value'   => $value,
    'message' => $success ? 'บันทึกสำเร็จ' : 'บันทึกล้มเหลว'
]);
