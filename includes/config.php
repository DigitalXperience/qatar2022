<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'exportfoot_33');
define('DB_USER','exportfoot_patient');
define('DB_PASSWORD','30Decembre?');


$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
    exit();
}