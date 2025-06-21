<?php
require_once('../../../private/initialize.php');

// ดึงข้อมูลจังหวัดทั้งหมดจากคลาส Province
$provinces = Province::find_all();

header('Content-Type: application/json');

if ($provinces !== false && is_array($provinces)) {
    $data = [];
    foreach ($provinces as $province) {
        $data[] = [
            'id' => $province->id,
            'name' => $province->name
        ];
    }
    echo json_encode($data);
} else {
    echo json_encode([]);
}
