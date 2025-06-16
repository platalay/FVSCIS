<?php
require_once('../../../private/initialize.php');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $fisherman = Fisherman::find_by_id($id);

    if ($fisherman) {
        echo json_encode($fisherman);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Fisherman not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Missing ID']);
}
?>
