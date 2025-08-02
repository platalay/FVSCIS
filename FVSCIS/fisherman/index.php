<?php
require_once('../../private/initialize.php');
$session->require_role(['fisherman']);
include("../../private/shared/headeruser.php");
include("../../private/shared/sidebaruser.php");
include("../../private/shared/topbaruser.php"); ?>

<!-- Begin Page Content -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">หน้าชาวประมง (Fisherman)</h1>
</div>

<?php
include("../../private/shared/footeruser.php");
?>
<script src="../js/fvscis.js"></script>
<?php
include("../../private/shared/footerall.php");
?>
