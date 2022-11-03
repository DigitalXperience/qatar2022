<?php 

session_start();
require_once __DIR__ . '/../includes/config.php';

date_default_timezone_set('Africa/Douala');
$_SESSION['name'] = $_POST['name'];
$_SESSION['email'] = $_POST['email'];
$_SESSION['image'] = $_POST['image'];
$_SESSION['oauth_uid'] = $_POST['id'];

// Traces -- On enregistre les traces du passage de l'utilisateur pour un audits
$date = date('Y-m-d H:i:s');
$sql3 = "INSERT INTO traces VALUES(NULL, 'Tentative de login Google pour ".$_POST['name']." avec id=".$_POST['id']." et email ".$_POST['email']."', '".$date."')";
$mysqli->query($sql3);
// Traces --

global $mysqli;

$sql = "SELECT * FROM utilisateurs WHERE oauth_uid = '".$_POST['id']."' LIMIT 1;";
$result = $mysqli->query($sql);


/////////////////////////////////////////
/* if(!empty($result->fetch_assoc())) {
	$sql2 = "UPDATE utilisateurs SET oauth_uid = '".$_POST['id']."' WHERE email = '".$_POST['email']."'";
	//echo "Validation terminée!";
} else {
	$sql2 = "INSERT INTO utilisateurs(`id`, `oauth_uid`, `oauth_provider`, `nom`, `email`, `picture_url`) 
			VALUES (NULL, '".$_POST['id']."', 'google', '".$_POST['name']."', '".$_POST['email']."', '".$_POST['image']."')";	
	//echo "Enregistrement terminé!";
}

$mysqli->query($sql2);

 */
////////////////////////////////

if(!empty($result->fetch_assoc())) {
	
	// Traces -- On enregistre les traces du passage de l'utilisateur pour un audits
	$date = date('Y-m-d H:i:s');
	$sql3 = "INSERT INTO traces VALUES(NULL, 'Reconnaissance de l'OAUTH de ".$_POST['name']."', '".$date."')";
	$mysqli->query($sql3);
	// Traces --
	
	if ($result = mysqli_query($mysqli, $sql)) {  
	   while ($obj = mysqli_fetch_object($result)){
		//echo "<pre>Non code"; var_dump($obj); die;
			$id = $obj->id;
			$email = $obj->email;
			$name = $obj->nom;
			$image = $obj->picture_url;
	   }
	   $result->close();
	}
	
	if($_SESSION['name'] != $name || $_SESSION['image'] != $image) {
		$sql2 = "UPDATE utilisateurs SET nom = '".$_POST['name']."', picture_url = '".$_POST['image']."' WHERE oauth_uid = '".$_POST['id']."'";
		$mysqli->query($sql2);
		// Traces -- On enregistre les traces du passage de l'utilisateur pour un audits
		$date = date('Y-m-d H:i:s');
		$sql3 = "INSERT INTO traces VALUES(NULL, 'Mise à jour du nom ".$name." en ".$_POST['name']." et image ".$image." vers ".$_POST['image']."', '".$date."')";
		$mysqli->query($sql3);
		// Traces --
	}
	
	$_SESSION['id'] = $id;
	$_SESSION['name'] = $_POST['name'];
	$_SESSION['email'] = $_POST['email'];
	$_SESSION['oauth'] = 'google';
	
	
	
	echo "Validation terminée!";
	//echo "<pre>Non code"; var_dump($_SESSION);
	
} else {
	$sql2 = "INSERT INTO utilisateurs(`id`, `oauth_uid`, `oauth_provider`, `nom`, `email`, `picture_url`) 
			VALUES (NULL, '".$_POST['id']."', 'google', '".$_POST['name']."', '".$_POST['email']."', '".$_POST['image']."')";
	//var_dump($sql2);
	$mysqli->query($sql2);
	
	$_SESSION['id'] = $mysqli->insert_id;
	$_SESSION['name'] = $_POST['name'];
	$_SESSION['email'] = $_POST['email'];
	$_SESSION['image'] = $_POST['image'];
	$_SESSION['oauth'] = 'google';
	// Traces -- On enregistre les traces du passage de l'utilisateur pour un audits
	$date = date('Y-m-d H:i:s');
	$sql3 = "INSERT INTO traces VALUES(NULL, 'Enregistrement de ".$_POST['name']." avec id enregistrement ".$_SESSION['id']." et sql : ".$sql2."', '".$date."')";
	$mysqli->query($sql3);
	// Traces --
	
	echo "Enregistrement terminé!";
}


?>