<?php
require_once('../../../private/initialize.php');

header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['province_id'])) {
    echo '<option value="">-- เลือกหน่วยงาน --</option>';
    exit;
}

$province_id = $_GET['province_id'];

$departments = Department::find_by_province($province_id);

if (!empty($departments)) {
    echo '<option value="">-- เลือกหน่วยงาน --</option>';
    foreach ($departments as $dept) {
        echo '<option value="' . htmlspecialchars($dept->id) . '">' . htmlspecialchars($dept->name) . '</option>';
    }
} else {
    echo '<option value="">-- ไม่มีหน่วยงานในจังหวัดนี้ --</option>';
}
