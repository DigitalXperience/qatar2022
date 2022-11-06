<?php
session_start();
date_default_timezone_set('Africa/Douala');
require_once 'includes/config.php';
include_once 'includes/functions.php';

//ini_set('display_errors',0);

$message = "";
if(isset($_POST)) {
	// On vérifie que toutes les valeurs on été renseignées
	// On store ces variables dans la session pour retourner avec à l'enregistrement
	$error = false;
	foreach($_POST as $key => $var) {
		if($var == "") 
			$error = true;
		else 
			$_SESSION[$key] = $var;
	}
	
	// S'il y a une variable vide on rentre
	if($error) {
		header('Location: https://www.33export-foot.com/qatar2022/index_mobile_register.php?message=error2');
		die();
	} else {
		$_SESSION['name'] = $_POST['nom'];
		$_SESSION['email'] = $_POST['email'];
		$_SESSION['numero'] = $_POST['numero'];
		$nom = $_SESSION['name'];
		$email = $_SESSION['email'];
	}
	
	// Sinon 
	
	// On traite au cas par cas
	// 1. On verifie le numéro 
	$numero = $stripped = str_replace(' ', '', $_POST['numero']); // On récupère le numéro et on enlève les espace vide
	$numberOfDigits = strlen($numero); // On récupère la longueur du numéro
	//var_dump($numberOfDigits); die;
	if ($numberOfDigits != 9) {
		header('Location: https://www.33export-foot.com/qatar2022/index_mobile_register.php?message=error3');
		die();
	}
	// 2. On vérifie le nom

	// 3. On vérifie l'email
	
	// 4. On vérifie et traite la date de naissance
	//$datenaiss = $_POST['datenaiss'];
	//$daten = explode("/", $datenaiss);
	//$annee = $daten[2];
	
	if ($numberOfDigits == 9) {
		
		// Traces
		global $mysqli;

		// Traces -- On enregistre les traces du passage de l'utilisateur pour un audits
		$date = date('Y-m-d H:i:s');
		$sql3 = "INSERT INTO traces VALUES(NULL, 'Tentative de login via le code pour ".$numero." avec nom = ".$nom." et email ".$email."', '".$date."')";
		$mysqli->query($sql3);
		// Traces --
		
		// On fouille l'existence de son numéro dans la BD
		$sql = "SELECT * FROM utilisateurs WHERE numero = '".$numero."' LIMIT 1;";
		$result = $mysqli->query($sql);

		if(!empty($result->fetch_assoc())) { // Son numero existe déjà, on met à jour son numéro
			
			if ($result = mysqli_query($mysqli, $sql)) {  
			   while ($obj = mysqli_fetch_object($result)){
				//echo "<pre>Non code"; var_dump($obj); die;
					$id = $obj->id;
					$email = $obj->email;
					$name = $obj->nom;
					$code = $obj->code;
			   }
			   $result->close();
			}
			
			sendCodebySMS($numero, $code);
			
		} else { // Son numero n'existe pas encore
			$code = createCode();
			$sql5 = "INSERT INTO utilisateurs(`id`, `oauth_uid`, `oauth_provider`, `nom`, `email`, `picture_url`, `last_login`, `creation_date`, `code`, `numero`) 
					VALUES (NULL, NULL, '', '".$nom."', '".$email."', NULL, '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '".$code."', '".$numero."')";	
			
			$message = sendCodebySMS($numero, $code); // On doit écrire un code pour analyser le résultat de l'envoi de sms
			$mysqli->query($sql5);
		}
		
		
		
		
	} 
} else {
	
	header('Location: https://www.33export-foot.com/qatar2022/index_mobile_register.php?message=error1');
		die();
}

// http://rslr.connectbind.com/bulksms/bulksms?username=dms-brc2018&password=brc2018&type=0&dlr=1&destination=237665222225@&source=33Export@&message=VotreCode

?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>33 Export Pronostics</title>
	<meta name="viewport" content="width=device-width, initial-scale=1  maximum-scale=1 user-scalable=no">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-touch-fullscreen" content="yes">
	<meta name="HandheldFriendly" content="True">
	  <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="css3/materialize.css">
	<link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="css3/normalize.css">
	<link rel="stylesheet" href="css3/owl.carousel.css">
	<link rel="stylesheet" href="css3/owl.theme.css">
	<link rel="stylesheet" href="css3/owl.transitions.css">
	<link rel="stylesheet" href="css3/fakeLoader.css">
	<link rel="stylesheet" href="css3/style.css">
	
	
</head>

<body>
	<!-- login register -->
	<div class="login-register-wrap-home">
		<div class="container">
			<div class="content">	
				<h1><img src="img/33_transparent_1.png" alt="33 Export Logo" title="33 Export Pronostic Foot" /></h1>
				<h6>33 Export Foot</h6>	
				<div id="response">
				<?php echo $message; ?>
				</div>
				
				<form id="form_register" style="margin-top:15px" action="index_mobile3.php" method="POST">
					<div id="name">Entrez le code que vous avez reçu par SMS</div>
					<input type="text" id="numero" value="<?php echo $numero; ?>" placeholder="Votre Numero" name="numero" disabled >
					<input type="password" id="code" placeholder="****" name="code"> 
					<button class="button-default" style="background-color: #B51729;"> Confirmer</button>
					<h6>Vous avez déjà un compte ? <a href="index_mobile3.php">Connexion</a></h6>
				</form>
			</div>
		</div>
	</div>
	<!-- end login register -->
	
	<!-- scripts -->
	<script src="js3/jquery.min.js"></script>
	<script src="js3/materialize.min.js"></script>
	<script src="js3/owl.carousel.min.js"></script>
	<script src="js3/contact-form.js"></script>
	<script src="js3/fakeLoader.min.js"></script>
	<script src="js3/main.js"></script>
	<script type="text/javacsript">
	
	$(document).ready(function () {
		
	});
	</script>
<div class="hiddendiv common"></div>
</body>
</html>