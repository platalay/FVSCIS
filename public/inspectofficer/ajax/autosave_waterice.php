
<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);

header('Content-Type: application/json; charset=utf-8');

// อนุญาตเฉพาะ POST
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

// optional: กัน field แปลก ๆ เช่นการยิง script แปลก ๆ เข้ามา
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
 * 🔒 ดักล็อกเอกสาร: หากส่งยืนยันผลการตรวจแล้ว ห้าม autosave
 * ส่ง 200 พร้อม locked=true เพื่อให้ JS แจ้งเตือนใน success callback ได้
 */
$formStatus = InspectionFormStatus::find_by_request_id($request_id); // ถ้าเมธอดชื่ออื่น ให้เปลี่ยนบรรทัดนี้

if ($formStatus && (int)$formStatus->document_locked === 1) {
    echo json_encode([
        'success' => false,
        'locked'  => true,
        'field'   => $field,
        'value'   => $value,
        'message' => 'ไม่สามารถปรับข้อมูลได้แล้ว เนื่องจากมีการส่งยืนยันผลการตรวจแล้ว'
    ]);
    exit;
}

// Autosave สำหรับหมวดน้ำจืดและน้ำแข็ง
$success = InspectionFormWaterAndIce::autosave($request_id, $field, $value);

// ส่งผลลัพธ์กลับ
echo json_encode([
    'success' => (bool)$success,
    'locked'  => false,
    'field'   => $field,
    'value'   => $value,
    'message' => $success ? 'บันทึกสำเร็จ' : 'บันทึกล้มเหลว'
]);
