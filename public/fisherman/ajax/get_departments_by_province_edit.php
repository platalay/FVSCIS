<?php
require_once('../../../private/initialize.php');

header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['province_id'])) {
    exit;
}

$province_id = $_GET['province_id'];

$departments = Department::find_by_province($province_id);

if (!empty($departments)) {
    $data = [];
    foreach ($departments as $dept) {
        $data[] = [
            'id' => $dept->id,
            'name' => $dept->name
        ];
    }
    echo json_encode($data);
}
