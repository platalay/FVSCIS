<?php
require_once('../../../private/initialize.php');

$request_id = $_POST['request_id'] ?? null;
$approval_note = $_POST['approval_note'] ?? null;
$user_id = $session->user_id();

if (!$request_id || !$user_id) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$request = InspectionRequest::find_by_id($request_id);

if (!$request) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบคำขอนี้']);
    exit;
}

$now = date('Y-m-d H:i:s');
$expire = date('Y-m-d', strtotime('+2 years'));

$request->approved_by = $user_id;
$request->approved_at = $now;
$request->approved_ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$request->approval_note = $approval_note;
$request->expire_at = $expire;
$request->status = InspectionRequest::STATUS_COMPLETED;

if ($request->save()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'บันทึกข้อมูลไม่สำเร็จ']);
}
?>
