<?php
// ajax/get_departments_by_group.php
require_once('../../private/initialize.php');

if (isset($_POST['group_id'])) {
    $group_id = (int)$_POST['group_id'];
    $departments = Department::find_by_department_group_id($group_id);
    $data = [];
    foreach ($departments as $dep) {
        $data[] = [
            'id' => $dep->id,
            'name' => htmlspecialchars($dep->name)
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($data);
}
?>