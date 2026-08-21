<?php
require_once('../../../private/initialize.php');

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(null);
    exit;
}

$DepartmentGroup = DepartmentGroup::find_by_id($id);
if (!$DepartmentGroup) {
    echo json_encode(null);
    exit;
}

echo json_encode([
    'id' => $DepartmentGroup->id,
    'name' => $DepartmentGroup->name,
    'note' => $DepartmentGroup->note
]);
