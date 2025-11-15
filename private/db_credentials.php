<?php

// Keep database credentials in a separate file
// 1. Easy to exclude this file from source code managers
// 2. Unique credentials on development and production servers
// 3. Unique credentials if working with multiple developers
define('BASE_URL', 'http://localhost/FVSCIS/');

define("DB_SERVER", "localhost");
define("DB_USER", "fvuser");
define("DB_PASS", "Ppmttm093419*");
define("DB_NAME", "fvscis");


define("DB_SERVER_FI","172.16.1.141");
define("DB_USER_FI", "pg_fd");
define("DB_PASS_FI", "ug1kae8N");
define("DB_NAME_FI", "FI2");
define("DB_PORT_FI", "5432");


define("DB_SERVER_EL","172.16.1.168");
define("DB_USER_EL", "pg_fd");
define("DB_PASS_EL", "fd@123");
define("DB_NAME_EL", "db_elicense_live");
define("DB_PORT_EL", "5432");


define('LINE_LOGIN_CHANNEL_ID','2007374384');
define('LINE_LOGIN_CHANNEL_SECRET','c528c7071c8f8991f68102cb1e8687ae');
define('LINE_LOGIN_CALLBACK_URL','https://fishlanding.fisheries.go.th/FVSCIS/linecallback.php');

define('GOOGLE_CLIENT_ID',"334855618936-6rj6q830g4avknampvkhv8phjfonijqm.apps.googleusercontent.com");
define('GOOGLE_CLIENT_SECRET',"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");
define('GOOGLE_LOGIN_CALLBACK_URL','http://localhost/FVSCIS/googlecallback.php');

define('FB_APP_ID','1376186423509609');
define('FB_APP_SECRET','2fd7a0a9d29ad00f10a1ce26a428eb3b');
define('FB_REDIRECT_URI','http://localhost/FVSCIS/fbcallback.php');
define('APP_PASSWORD','nhgo bepk ulsr hsfv');
?>
