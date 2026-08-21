<?php
$client_id = '2007374384';
$redirect_uri = urlencode('https://fishlanding.fisheries.go.th/FVSCIS/linecallback.php');
$state = uniqid(); // สำหรับป้องกัน CSRF
$scope = 'profile openid email';

$auth_url = "https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=$client_id&redirect_uri=$redirect_uri&state=$state&scope=$scope";

header('Location: ' . $auth_url);
exit;
?>