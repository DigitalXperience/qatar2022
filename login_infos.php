<?php
session_start();
date_default_timezone_set('Africa/Douala');
require_once 'includes/config.php';
include_once 'includes/functions.php';

$_SESSION['code'] = $_POST['code'];
$_SESSION['numero'] = $_POST['numero'];
//echo "<pre>";
var_dump($_POST['numero']);

global $mysqli;

		// Traces -- On enregistre les traces du passage de l'utilisateur pour un audits
		$date = date('Y-m-d H:i:s');

$sql = "SELECT * FROM utilisateurs WHERE numero = '".$_POST['numero']."' ";
//		var_dump($sql);
$result = $mysqli->query($sql);


if ($row = $result->fetch_assoc()) {
	//var_dump($row);
	if($_POST['code'] == $row["code"]) {
		$sql2 = "UPDATE utilisateurs SET num_ok = 'oui' WHERE numero = '".$_POST['numero']."'";
		$mysqli->query($sql2);
		$_SESSION['id'] = $row["id"];
		$_SESSION['oauth_provider'] = $row["oauth_provider"];
		header('Location: https://www.33export-foot.com/qatar2022/index_test.php');
		die();
	}
} else {
	header('Location: https://www.33export-foot.com/qatar2022/index_mobile_register_code.php?message=error');
	die();
}

?>