<?php
require_once('../private/initialize.php');
$client_id = GOOGLE_CLIENT_ID;
$redirect_uri = GOOGLE_LOGIN_CALLBACK_URL;

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'access_type' => 'offline',
]);

header('Location: ' . $auth_url);
exit;
?>