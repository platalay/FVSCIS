<?php
require_once('../../../private/initialize.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $Department = Department::find_by_id($id);
    
    if ($Department) {
        echo json_encode($Department);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'ไม่พบข้อมูล']);
    }
}
?>