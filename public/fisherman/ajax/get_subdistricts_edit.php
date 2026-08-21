<?php
require_once('../../../private/initialize.php');

$district_id = $_GET['amphur_id'] ?? '';


if (is_numeric($district_id)) {
    $subdistricts = Tambon::find_by_amphur_id($district_id);
    $data = [];
    foreach ($subdistricts as $subdistrict) {
        $data[] = [
            'id' => $subdistrict->id,
            'name' => $subdistrict->name
        ];
    }
}
echo json_encode($data);
?>