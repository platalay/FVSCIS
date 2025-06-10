<?php
require_once('../../../private/initialize.php');

$department_groups = DepartmentGroup::find_all();

$results = [];

foreach ($department_groups as $group) {
    $results[] = [
        'id' => $group->id,
        'name' => $group->name
    ];
}

header('Content-Type: application/json');
echo json_encode($results);
