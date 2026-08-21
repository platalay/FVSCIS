<?php
require_once('../../../private/initialize.php');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $officer = Officer::find_by_id($id);

    if ($officer) {
        echo json_encode($officer);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Officer not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Missing ID']);
}
?>