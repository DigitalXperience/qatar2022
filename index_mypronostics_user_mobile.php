<?php 
header("Content-Type: text/html; charset=UTF-8");
ini_set('display_errors',1); 
error_reporting(E_ALL);

//Démarrage de la session
session_start();
date_default_timezone_set('Africa/Douala');
require_once 'includes/config.php';
include_once 'includes/functions.php';

// Se rassurer que le mec est connecté. Sinon on le renvoie à l'authentification. Si oui on récupère les données de connexion : Mode d'authentification, id, 
if(!isset($_SESSION['id'])) {
	header('Location: https://www.33export-foot.com/qatar2022/index_mobile3.php');
	die();	
}

if(isset($_SESSION['idcompetitions'])) {
	$idcompetitions = $_SESSION['idcompetitions'];
} else $idcompetitions = 1;

if(isset($_GET['idcompetition'])) {
	$idcompetitions = $_GET['idcompetition'];
}



$nb_participants = null;
$params = null;
$rencontres_pronosticables = null;

// database access
global $mysqli;

// Traces -- On enregistre les traces du passage de l'utilisateur pour un audits
$date = date('Y-m-d H:i:s');
$sql9 = "INSERT INTO traces VALUES(NULL, 'Affiche page des pronostics par ".$_SESSION['name']." id: ".$_SESSION['id']."', '".$date."')";
$mysqli->query($sql9);
// Traces --

if(!isset($_SESSION['code'])) {
	
	// Les infos de l'utilisateur 
	if(isset($_GET['user'])) 
		$query = "SELECT id, nom, oauth_uid, oauth_provider, picture_url, numero, email  FROM utilisateurs WHERE id = '" . $_GET['user'] . "' LIMIT 1;";
	else
		$query = "SELECT id, nom, oauth_uid, oauth_provider, picture_url, numero, email  FROM utilisateurs WHERE id = '" . $_SESSION['id'] . "' LIMIT 1;";
	  // var_dump($query); 
	   // var_dump($_SESSION['id']); die;
	if ($result = mysqli_query($mysqli, $query)) {  
	   while ($obj = mysqli_fetch_object($result)){
		   //echo "<pre>Code"; var_dump($obj); die;
			$id = $obj->id;
			$oauth_id = $obj->oauth_uid;
			$oauth = $obj->oauth_provider;
			$picture_url = $obj->picture_url;
			$name = $obj->nom;
			$email = $obj->email;
			$tel = $obj->numero;
	   }
	   $result->close();
	}
} else { 
	
	// Les infos de l'utilisateur 
	if(isset($_GET['user'])) 
		$query = "SELECT id, nom, oauth_uid, oauth_provider, picture_url, numero  FROM utilisateurs WHERE id = '" . $_GET['user'] . "' LIMIT 1;";
	else
		$query = "SELECT id, nom, oauth_uid, oauth_provider, picture_url, numero  FROM utilisateurs WHERE id = '" . $_SESSION['id'] . "' LIMIT 1;";
	  // var_dump($query); 
	   // var_dump($_SESSION['id']); die;
	if ($result = mysqli_query($mysqli, $query)) {  
	   while ($obj = mysqli_fetch_object($result)){
			$id = $obj->id;
			$oauth_id = $obj->oauth_uid;
			$oauth = $obj->oauth_provider;
			$picture_url = $obj->picture_url;
			$name = $obj->nom;
	   }
	   $result->close();
	}
}

$sql = "SELECT p.score_eq1 as pr_eq1, p.score_eq2 as pr_eq2, p.pts_obtenus, r.score_eq1, r.score_eq2, e1.name as eq1, e1.flag as flag1, e2.name as eq2, e2.flag as flag2  
	 FROM pronostics p 
	 LEFT JOIN rencontres r ON r.id = p.rencontre_id 
	 LEFT JOIN equipes e1 ON e1.id = r.equipe_id1 
	 LEFT JOIN equipes e2 ON e2.id = r.equipe_id2 
	 WHERE utilisateur_id = '$id' AND p.pts_obtenus IS NOT NULL ORDER BY r.date_heure DESC";
	 
if(isset($_GET['user']))
	$sql = "SELECT p.score_eq1 as pr_eq1, p.score_eq2 as pr_eq2, p.pts_obtenus, r.score_eq1, r.score_eq2, e1.name as eq1, 
			e1.flag as flag1, e2.name as eq2, e2.flag as flag2, u.nom   
			 FROM pronostics p 
			 LEFT JOIN rencontres r ON r.id = p.rencontre_id 
			 LEFT JOIN equipes e1 ON e1.id = r.equipe_id1 
			 LEFT JOIN equipes e2 ON e2.id = r.equipe_id2 
			 LEFT JOIN utilisateur u ON u.id = p.utilisateur_id 
			 WHERE p.utilisateur_id = '" . $_GET['user'] . "' AND p.pts_obtenus IS NOT NULL ORDER BY r.date_heure DESC";

$pronos = null;	 
 
if ($result = mysqli_query($mysqli, $sql)) {  
   while ($obj = mysqli_fetch_object($result)){
		$pronos[] = $obj;
   }
   $result->close();
}

/* mysqli close connection */
$mysqli->close();

?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class=" js touch" xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#"><!--<![endif]--><head>
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

		<title>33 Export Prono Foot</title>

		<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no, width=device-width, height=device-height">
		<meta property="fb:app_id" content="209165813231127">


        <script>
		    var Env = {
				core: {
					locale			: "fr_FR",
					hasCustomLocale	: "false",
					cb				: "1",
                    is_webview_android : "",
				},
				fb: {
					appId			: "209165813231127",
					version			: "v2.12",
					callbackUrl		: "https://prono-foot-33export.herokuapp.com/",

					canvasUrl		: "https://apps.facebook.com/footballpronostic",
					locale			: 'fr_FR',
					
					mode			: 'website',
					useCookie		:	true
				}
			};
        </script>

			<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<!--<script src="//s3-eu-west-1.amazonaws.com/fbappz/static/core.krds.js?v=1502446663"></script>-->
		<script src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/js/core.krds_v2.js?v=1502446663"></script>

		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
		<script src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/js/modernizr-2.6.2.min.js?2"></script>
		<script src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/js/ka.krds.js?v=1502446663"></script>
		<script>
            ENV = {"callback_url":"https:\/\/prono-foot-33export.herokuapp.com/","canvas_url":"https:\/\/apps.facebook.com\/footballpronostic","tab_url":"https:\/\/www.facebook.com\/footballpronostic?sk=app_209165813231127"}

			$(document).ready(function()
			{
				if(Modernizr.touch)
				{
					$('[fastclick]').on('touchstart', function()
					{
						$(this).click();
						return false;
					});
				}
			});

		</script>

		<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/ui-lightness/jquery-ui.css" media="screen" rel="stylesheet">
		<link href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/css/h5bp.css" rel="stylesheet" media="all">
		<link href="css/wc_pronostics_mobile_style.css?v=1502446663" rel="stylesheet">
		<script type='application/javascript' src='https://<?php echo $_SERVER['SERVER_NAME']; ?>/js/fastclick.js'></script>
		<link href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/css/retina.css?v=1502446663" rel="stylesheet" media="all and (-webkit-min-device-pixel-ratio:2)">
	<style>@media print {#ghostery-purple-box {display:none !important}}</style>
	<link type="text/css" rel="stylesheet" href="chrome-extension://pioclpoplcdbaefihamjohnefbikjilc/content.css">
</head>
	<body cz-shortcut-listen="true">
		<div id="fb-root" class=" fb_reset"><div style="position: absolute; top: -10000px; height: 0px; width: 0px;"><div></div></div><div style="position: absolute; top: -10000px; height: 0px; width: 0px;"><div><iframe name="fb_xdm_frame_https" frameborder="0" allowtransparency="true" allowfullscreen="true" scrolling="no" allow="encrypted-media" id="fb_xdm_frame_https" aria-hidden="true" title="Facebook Cross Domain Communication Frame" tabindex="-1" src="https://staticxx.facebook.com/connect/xd_arbiter/r/qMnGlIs-JNW.js?version=42#channel=f188b9fd1f7963&amp;origin=https%3A%2F%2Fm.pronosfoot.mycanal.fr" style="border: none;"></iframe></div></div></div>
		<form action="" method="post" id="fb-form"><input name="authResponse" value="{&quot;accessToken&quot;:&quot;EAAAAHN9UfZAMBAH8sk0HIfFVMFwgVNBMupDNKNGUJURZCnaflaTVNIIRqZApKPs9vtkCwWuFd7HAwU9qZCdfBd0ZAhtZCQp9aZC1WmvdCJG0mvpZCu6JyQOnamD6gFl02ThquSr9b8gDPiLZBWfP5aVcMMRk6V592ZCZBN88IKq5ZB6jIgHiiOlGzot0yAfe1OUk2a4hppKL3kfZCNgZDZD&quot;,&quot;userID&quot;:&quot;10155870072269961&quot;,&quot;expiresIn&quot;:5743,&quot;signedRequest&quot;:&quot;FLfe2o7xOEY8mp2mG5TiJh2H16mt8CxdVE_aP0AXhQw.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImNvZGUiOiJBUUNWVG5xcTdOb09tSWhMYkhIWjNZZWNjNVI0WEk1Rk96NndBRVFMeWpjMWYxX1F2UmR0M3JrRFBuWUUydlBaYXdFZURXNEQyVXNLcTZ3UlB4cmxlYkYxQTFnT3dhbHJla1dJeEZ1SlcySlV3c1BKRGhqcklqMkVpVjBtaUJIWDVneVZkUG1OWkJfM1FHM2liOC01ZEoxTGZQUnlVc1JlT3BnZ0dLZkVHc1phQm9PbDZmaWE2TnZQcW16SzBLYXFxdzF1ZU5waUUySGRjTW50RVF3aVQ2MFlIdzZ3SXdHcjJaMFRhU3djQkhQS0NXZ0FDLTNlMVd4cUNSMFhUeEtIbWE2ZE9YQjdGZlpDeVZTUmkyYks0ZTNyZHhTX0ZSZTNfdzVZalp1TlFIVnh4eTJFWUJkMUNRTlZqcTdkTi1aOVZ3alRPQ2Z5c01PSHFwS1I2U2h1ajlaZCIsImlzc3VlZF9hdCI6MTUyOTQ2NTA1NywidXNlcl9pZCI6IjEwMTU1ODcwMDcyMjY5OTYxIn0&quot;,&quot;reauthorize_required_in&quot;:5031270}" type="hidden"></form>

		<div id="container">
			<header>

			</header>
			<div id="main" role="main">
			<header>
				<a href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/qatar2022/index_test.php?idcompetition=<?php echo $idcompetitions; ?>" class="logo"></a>
				<?php include('top_menu_mobile.php'); ?>
			</header>
			<style type="text/css">
	.userRankInfo {
		padding: 0 3.125% 20px;
	}
	.userRankDetails a {
		margin-left: 0;
	}
</style>

<div class="rankHolder">
	<div class="rankingTitle">Précédents Pronostics</div>
</div>

<div id="js_errors" style="display: none" class="error"></div>
<h3 class="betTitle">Tous les pronostics</h3>

<?php 
if ($pronos) { 
	foreach($pronos as $p) { ?>
<div class="mainBet">
	<div class="clubName">
		<span><img src="<?php echo $p->flag1; ?>" alt=""></span>
		<p><?php echo $p->eq1; ?></p>
	</div>
	<div class="clubName">
		<span><img src="<?php echo $p->flag2; ?>" alt=""></span>
		<p><?php echo $p->eq2; ?></p>
	</div>
	<?php if(($p->pr_eq1 == $p->score_eq1) && ($p->pr_eq2 == $p->score_eq2)) $color = "green"; else $color = "red"; ?>
	<div class="predictWinner prediction">
		<span class="predictionDefault <?php echo $color; ?>">
			<?php echo $p->pr_eq1; ?>
		</span>
		<span class="predictionDefault <?php echo $color; ?>">
			<?php echo $p->pr_eq2; ?>
		</span>
		<p class="result"><?php echo $p->score_eq1 . " - " . $p->score_eq2; ?> <b>(<?php echo $p->pts_obtenus; ?> pts)</b></p>
	</div>
</div>
<?php } } ?>
<!--<div class="otherBets">
	<div>
		<div class="clubName">
			<span><img src="https://pronosfoot.mycanal.fr/assets/front/img/teams/75/ab80vest6tgco121qtfyphq7c.png" alt=""></span>
			<p>Stade Malherbe Caen</p>
		</div>
		<div class="clubName">
			<span><img src="https://pronosfoot.mycanal.fr/assets/front/img/teams/75/4t4hod56fsj7utpjdor8so5q6.png" alt=""></span>
			<p>AS Monaco FC</p>
		</div>
		<div class="predictWinner prediction">
			<span class="predictionDefault ">1</span>
			<span class="predictionDefault red">N</span>
			<span class="predictionDefault ">2</span>
			<p class="result">1 - 2 </p>
		</div>
	</div>
</div>-->

<script>
$(document).ready(function(){

	/* $('#rounds_carousel').carouFredSel({
		circular: false,
		infinite: false,
		responsive: true,
		width: '100%',
		auto: false,
		items: {
			width: 400,
			visible: 3,
			start: 34
		},
		swipe: {
			onMouse: false,
			onTouch: false
		}
	}); */

    $('.jsRoundBtn').off('click').on('click', function(e) {
        e.preventDefault();

        window.location = $(this).attr('href');
    });
})
</script></div>

<footer id="sticky">
	  © 33 Export <?php echo date('Y'); ?>  | <a href="#" id="btn_logout" style="display:inline;">Se déconnecter</a> | <a href="#" style="display:inline;">Mes Lots</a>

		<!--
		<div id="fbForceResize" style="display:none; height:20px; width:100px">&nbsp;</div>
		 -->
	</footer>
		
	<script>
	/* Credits */
	$(document).ready(function()
	{
		//alert("IDocument ready");
		<?php if(!isset($_SESSION['code'])) { ?>
		$(document).on('fbready', function() {
			$('#btn_logout').off('click').on('click', function(e) {
				e.preventDefault();
				FB.logout(function(response) {
				   // Person is now logged out
				   window.location.href='https://<?php echo $_SERVER['SERVER_NAME']; ?>/index_mobile3.php?action=deconnexion';
				});
				/*
				FB.api('/me/permissions', 'delete', function()
				{
					FB.getLoginStatus(function(r)
					{
						Facebook.redirect(ENV.callback_url);
					}, true);

					Facebook.isUserConnected = false;
				});*/
			});
		});
		<?php } else { ?>
		$('#btn_logout').off('click').on('click', function(e) {
				e.preventDefault();
				//alert("Il essaie de se deconnecter sans facebook");
			 window.location.href='https://<?php echo $_SERVER['SERVER_NAME']; ?>/index_mobile3.php?action=deconnexion';
		});
		<?php } ?>
	});
	</script>
		</div>
	
</body></html>