<?php
require_once('../../../private/initialize.php');

$province_id = $_GET['province_id'] ?? '';
$output = '<option value="">-- เลือกอำเภอ --</option>';

if (is_numeric($province_id)) {
    $Amphurs = Amphur::find_by_province_id($province_id);
    foreach ($Amphurs as $Amphur) {
        $output .= '<option value="' . h($Amphur->id) . '">' . h($Amphur->name) . '</option>';
    }
}
echo $output;