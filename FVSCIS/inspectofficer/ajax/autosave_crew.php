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

// รับค่า
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
 * ✅ เช็กล็อกเอกสาร: ถ้าส่งยืนยันผลแล้ว (document_locked = 1) ห้าม autosave
 * หมายเหตุ: ในระบบคุณใช้ InspectionFormStatus เก็บ document_token/สถานะเอกสาร
 */
$formStatus = InspectionFormStatus::find_by_request_id($request_id); // หรือ find_by_id ตามที่คุณมีจริง

if ($formStatus && (int)$formStatus->document_locked === 1) {
    http_response_code(423); // Locked (หรือจะใช้ 403 ก็ได้)
    echo json_encode([
        'success' => false,
        'locked'  => true,
        'field'   => $field,
        'message' => 'ไม่สามารถปรับข้อมูลได้แล้ว เนื่องจากมีการส่งยืนยันผลการตรวจแล้ว'
    ]);
    exit;
}

// ดำเนินการ autosave ที่ฟอร์มหมวดบุคลากรประจำเรือ (crew)
$success = InspectionFormCrew::autosave($request_id, $field, $value);

// ตอบกลับ
echo json_encode([
    'success' => (bool)$success,
    'locked'  => false,
    'field'   => $field,
    'value'   => $value,
    'message' => $success ? 'บันทึกสำเร็จ' : 'บันทึกล้มเหลว'
]);
