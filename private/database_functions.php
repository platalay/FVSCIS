<?php

function db_connect() {
  $connection = new mysqli(
    DB_SERVER,
    DB_USER,
    DB_PASS,
    DB_NAME,
    DB_PORT
  );

  confirm_db_connect($connection);
  $connection->set_charset("utf8");

  return $connection;
}

function db_fi_connect() {
  $dsn_fi = 'pgsql:host=' . DB_SERVER_FI .
            ';port=' . DB_PORT_FI .
            ';dbname=' . DB_NAME_FI .
            ";options='-c client_encoding=utf8'";

  $db_fi = new PDO($dsn_fi, DB_USER_FI, DB_PASS_FI);
  $db_fi->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

  return $db_fi;
}

function db_el_connect() {
  $dsn_el = 'pgsql:host=' . DB_SERVER_EL .
            ';port=' . DB_PORT_EL .
            ';dbname=' . DB_NAME_EL .
            ";options='-c client_encoding=utf8'";

  $db_el = new PDO($dsn_el, DB_USER_EL, DB_PASS_EL);
  $db_el->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

  return $db_el;
}

function confirm_db_connect($connection) {
  if ($connection->connect_errno) {
    $msg = "Database connection failed: ";
    $msg .= $connection->connect_error;
    $msg .= " (" . $connection->connect_errno . ")";
    exit($msg);
  }
}

function db_disconnect($connection) {
  if (isset($connection)) {
    $connection->close();
  }
}

?>