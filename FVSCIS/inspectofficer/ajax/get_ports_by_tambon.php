<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);

if (!isset($_GET['tambon_id'])) {
    echo '<option value="">-- เลือกท่าเรือ --</option>';
    exit;
}

$tambon_id = (int) $_GET['tambon_id'];
$ports = Elicenseport::find_by_tambon($el_db, $tambon_id);

echo '<option value="">-- เลือกท่าเรือ --</option>';
foreach ($ports as $port) {
    echo '<option value="' . h($port->license_no) . '">' . h($port->port_name) . '</option>';
}
?>
