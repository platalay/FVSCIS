<?php
require_once('../private/initialize.php');
$fb_app_id = FB_APP_ID;
$redirect_uri = FB_REDIRECT_URI;
$scope = 'email,public_profile';

$url = 'https://www.facebook.com/v19.0/dialog/oauth?' . http_build_query([
    'client_id' => $fb_app_id,
    'redirect_uri' => $redirect_uri,
    'scope' => $scope,
    'response_type' => 'code',
]);

header("Location: $url");
exit;
?>
