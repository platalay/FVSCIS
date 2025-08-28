<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
$Officer = Officer::find_by_id($session->user_id());
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
?>

<!-- Begin Page Content -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">หน้าผู้ตรวจประเมิน (inspectofficer)</h1>
</div>

<?php
include("../../private/shared/footerofficer.php");
?>
<script src="../js/fvscis.js"></script>  
<?php
include("../../private/shared/footerall.php");
?>