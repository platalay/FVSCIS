<?php
require_once('../../private/initialize.php');
$session->require_role(['admin']);

include("../../private/shared/headeradmin.php");
include("../../private/shared/sidebaradmin.php");
include("../../private/shared/topbaradmin.php");
?>

<!-- Begin Page Content -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">หน้าผู้ดูแลระบบ (Admin)</h1>
</div>

<?php
include("../../private/shared/footeradmin.php");
include("../../private/shared/footerall.php");
?>