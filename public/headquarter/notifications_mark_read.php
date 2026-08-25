<?php
require_once('../../private/initialize.php');
$session->require_role(['headquarter']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$notification_id = (int)($_POST['notification_id'] ?? 0);
$user_id = $session->user_id();
$user_role = $session->role;

if ($notification_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบ notification id']);
    exit;
}

$result = Notification::mark_single_as_read($notification_id, $user_id, $user_role);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถอัปเดตสถานะอ่านได้']);
}
