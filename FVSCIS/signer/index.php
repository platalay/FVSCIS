<?php
require_once('../../private/initialize.php');
$session->require_role(['signer']);
$Officer = Officer::find_by_id($session->user_id());
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
?>

<!-- Begin Page Content -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">หน้าผู้ลงนามใบรับรอง (signer)</h1>
</div>

<?php
include("../../private/shared/footerofficer.php");
include("../../private/shared/footerall.php");
?>