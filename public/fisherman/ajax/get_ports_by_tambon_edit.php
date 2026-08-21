<?php
require_once('../../../private/initialize.php');

if (!isset($_GET['tambon_id'])) {
    exit;
}

$tambon_id = (int) $_GET['tambon_id'];
$ports = Elicenseport::find_by_tambon($el_db, $tambon_id);
$data = [];
foreach ($ports as $port) {
    $data[] = [
            'license_no' => $port->license_no,
            'port_name' => $port->port_name
        ];
}
echo json_encode($data);
?>