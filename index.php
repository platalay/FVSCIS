<?php
// URL ปลายทางที่ต้องการ redirect ไป
$redirect_url = "/FVSCIS/index.php";

// ส่ง header redirect
header("Location: " . $redirect_url);
exit;
?>