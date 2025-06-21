<?php

require_once('../../../private/initialize.php');
$action = LogAction::find_by_code('submitted');
print_r($action);
?>