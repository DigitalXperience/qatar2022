<?php 
session_start();
# /js-login.php
$post = json_decode($_POST['userData']); 

require_once __DIR__ . '/../includes/config.php';

date_default_timezone_set('Africa/Douala');

// id_oauth de facebook
$_SESSION['name'] = $post->name;
$_SESSION['email'] = $post->email;
$_SESSION['oauth'] = 'facebook';
$_SESSION['oauth_uid'] = $post->id;

global $mysqli;

// Traces -- On enregistre les traces du passage de l'utilisateur pour un audits
$date = date('Y-m-d H:i:s');
$sql3 = "INSERT INTO traces VALUES(NULL, 'Tentative de login Facebook pour ".$post->name." avec id=".$post->id." et email ".$post->email."', '".$date."')";
$mysqli->query($sql3);
// Traces --



$sql = "SELECT * FROM utilisateurs WHERE oauth_uid = '".$post->id."' LIMIT 1;";
$result = $mysqli->query($sql);

if(!empty($result->fetch_assoc())) {
	
	if ($result = mysqli_query($mysqli, $sql)) {  
	   while ($obj = mysqli_fetch_object($result)){
		//echo "<pre>Non code"; var_dump($obj); die;
			$id = $obj->id;
			$email = $obj->email;
			$name = $obj->nom;
	   }
	   $result->close();
	}
	
	if($_SESSION['email'] != $email || $_SESSION['name'] != $name) {
		$sql2 = "UPDATE utilisateurs SET email = '".$post->email."', nom = '".$post->name."' WHERE oauth_uid = '".$post->id."'";
		$mysqli->query($sql2);
		// Traces -- On enregistre les traces du passage de l'utilisateur pour un audits
		$date = date('Y-m-d H:i:s');
		$sql3 = "INSERT INTO traces VALUES(NULL, 'Mise à jour du nom ".$name." en ".$post->name." et email ".$email." vers ".$post->email."', '".$date."')";
		$mysqli->query($sql3);
		// Traces --
	}
	
	$_SESSION['id'] = $id;
	$_SESSION['name'] = $post->name;
	$_SESSION['email'] = $post->email;
	$_SESSION['oauth'] = 'facebook';
	
	echo "Validation terminée!";
	
} else {
	$date = date('Y-m-d H:i:s');
	$sql2 = "INSERT INTO utilisateurs(`id`, `oauth_uid`, `oauth_provider`, `nom`, `email`, `creation_date`) 
			VALUES (NULL, '".$post->id."', 'facebook', '".$post->name."', '".$post->email."', '".$date."')";
	//var_dump($sql2); die;	
	$mysqli->query($sql2);
	
	$_SESSION['id'] = $mysqli->insert_id;
	$_SESSION['name'] = $post->name;
	$_SESSION['email'] = $post->email;
	$_SESSION['oauth'] = 'facebook';
	// Traces -- On enregistre les traces du passage de l'utilisateur pour un audits
	$date = date('Y-m-d H:i:s');
	$sql3 = "INSERT INTO traces VALUES(NULL, 'Enregistrement de ".$post->name." avec id enregistrement ".$_SESSION['id']."', '".$date."')";
	$mysqli->query($sql3);
	// Traces --
	
	echo "Enregistrement terminé!";
}

?>