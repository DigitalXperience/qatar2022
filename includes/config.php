<?php
//define('DB_HOST', 'localhost');
define('DB_NAME', 'xxxxxxx3');
define('DB_USER','xxxxxx');
define('DB_PASSWORD','xxxxxxxx');


$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
    exit();
}
