<?php
require_once('../../../private/initialize.php');

$district_id = $_GET['district_id'] ?? '';
$output = '<option value="">-- เลือกตำบล --</option>';

if (is_numeric($district_id)) {
    $subdistricts = Tambon::find_by_amphur_id($district_id);
    foreach ($subdistricts as $subdistrict) {
        $output .= '<option value="' . h($subdistrict->id) . '">' . h($subdistrict->name) . '</option>';
    }
}
echo $output;