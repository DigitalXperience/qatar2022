<?php 
session_start();
# /js-login.php
$post = json_decode($_POST['userData']); 

session_start();
require_once __DIR__ . '/../includes/config.php';

// id_oauth de facebook
$_SESSION['name'] = $post->name;
$_SESSION['email'] = $post->email;
$_SESSION['oauth'] = 'facebook';
$_SESSION['oauth_uid'] = $post->id;

global $mysqli;

//$_SESSION['id'] = $post->id;
$_SESSION['name'] = $post->name;
$_SESSION['email'] = $post->email;
$_SESSION['oauth'] = 'facebook';

$sql = "SELECT * FROM utilisateurs WHERE oauth_uid = '".$post->id."' OR email = '".$post->email."'";
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
	
	$sql2 = "UPDATE utilisateurs SET oauth_uid = '".$post->id."' WHERE email = '".$post->email."'";
	$mysqli->query($sql2);
	
	$_SESSION['id'] = $id;
	$_SESSION['name'] = $post->name;
	$_SESSION['email'] = $post->email;
	$_SESSION['oauth'] = 'facebook';

	echo "Enregistrement terminé!";
} else {
	$sql2 = "INSERT INTO utilisateurs(`id`, `oauth_uid`, `oauth_provider`, `nom`, `email`) 
			VALUES (NULL, '".$post->id."', 'facebook', '".$post->name."', '".$post->email."')";	
	$mysqli->query($sql2);

	echo "Enregistrement terminé!";
}

?>