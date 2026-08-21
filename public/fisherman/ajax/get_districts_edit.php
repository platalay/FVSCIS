<?php
require_once('../../../private/initialize.php');

$province_id = $_GET['province_id'] ?? '';

if (is_numeric($province_id)) {
    $Amphurs = Amphur::find_by_province_id($province_id);
    $data = [];
    foreach ($Amphurs as $Amphur) {
        $data[] = [
            'id' => $Amphur->id,
            'name' => $Amphur->name
        ];
    }
}
echo json_encode($data);

?>