<?php
require_once('../../private/initialize.php');
header('Content-Type: application/json');

$username = trim($_POST['username'] ?? '');

if ($username === '') {
    echo json_encode(['available' => false, 'message' => 'username ว่าง']);
    exit;
}

$officer = Officer::find_by_username($username);

echo json_encode([
    'available' => $officer === false
]);
