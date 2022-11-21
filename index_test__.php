<?php 
//header("Content-Type: text/html; charset=UTF-8");
ini_set('display_errors',0); 
error_reporting(E_ALL);

session_start();

require_once 'includes/config.php';
date_default_timezone_set('Africa/Douala');
include_once 'includes/functions.php';

if(isset($_SESSION['idcompetitions'])) {
	$idcompetitions = $_SESSION['idcompetitions'];
} else $idcompetitions = 1;

if(isset($_GET['idcompetition'])) {
	$idcompetitions = $_GET['idcompetition'];
}

// Se rassurer que le mec est connecté. Sinon on le renvoie à l'authentification. Si oui on récupère les données de connexion : Mode d'authentification, id, 
// echo "<pre>"; var_dump($_SESSION); die;
if(!isset($_SESSION['id']) || $_SESSION['id'] == "0" || $_SESSION['id'] == "") {
	
	header('Location: index_mobile3.php?reason=nosession');
	die();	
}

// database access
global $mysqli;

// Traces -- On enregistre les traces du passage de l'utilisateur pour un audits
$date = date('Y-m-d H:i:s');
$sql9 = "INSERT INTO traces VALUES(NULL, 'Affiche page mon profil de ".$_SESSION['name']." id: ".$_SESSION['id']."', '".$date."')";
$mysqli->query($sql9);
// Traces --
	


if(!isset($_SESSION['code'])) {
	/* Update la derniere présence sur l'application */
	$sql = "UPDATE `utilisateurs` SET `last_login` = '".date("Y-m-d H:i:s")."', last_ip = '".get_client_ip()."'  WHERE id = '" . $_SESSION['id'] . "' LIMIT 1; ";
	$mysqli->query($sql);

	// Les infos de l'utilisateur 
	$query = "SELECT id, nom, oauth_uid, oauth_provider, picture_url, numero, email, banned  FROM utilisateurs WHERE id = '" . $_SESSION['id'] . "' LIMIT 1;";
	 
	if ($result = mysqli_query($mysqli, $query)) {  
	   while ($obj = mysqli_fetch_object($result)){
			$id = $obj->id;
			$oauth_id = $obj->oauth_uid;
			$oauth = $obj->oauth_provider;
			$picture_url = $obj->picture_url;
			$name = $obj->nom;
			$email = $obj->email;
			$tel = $obj->numero;
			$banni = $obj->banned;
	   }
	   $result->close();
	}
} else { 
	/* Update la derniere présence sur l'application */
	$sql = "UPDATE `utilisateurs` SET `last_login` = '".date("Y-m-d H:i:s")."', last_ip = '".get_client_ip()."'  WHERE id = '" . $_SESSION['id'] . "' LIMIT 1; ";
	$mysqli->query($sql);

	// Les infos de l'utilisateur 
	$query = "SELECT id, nom, oauth_uid, oauth_provider, picture_url, numero, banned  FROM utilisateurs WHERE id = '" . $_SESSION['id'] . "' LIMIT 1;";
	  // var_dump($query); 
	   // var_dump($_SESSION['id']); die;
	if ($result = mysqli_query($mysqli, $query)) {  
	   while ($obj = mysqli_fetch_object($result)){
			$id = $obj->id;
			$oauth_id = $obj->oauth_uid;
			$oauth = $obj->oauth_provider;
			$picture_url = $obj->picture_url;
			$name = $obj->nom;
			$tel = $obj->numero;
			$banni = $obj->banned;
	   }
	   $result->close();
	}
}

if($banni == "1") {
	header('Location: https://www.33export-foot.com/qatar2022/index_mobile3.php?action=deconnexion');
	die();	
}

// if(is_null($tel) || $tel == "") {
	// header('Location: https://www.33export-foot.com/jotokyo/mes_infos.php');
	// die();
// }

$sql = "SELECT * FROM competitions where status = 1 ";
$competitions = array();
if ($result = $mysqli->query($sql)) {

    /* Récupère un tableau associatif */
    while ($row = $result->fetch_assoc()) {
		$competitions[] = array('id' => $row['id'], 'nom' => $row["nom"], 'image' => $row["image"]);
    }

    /* Libération des résultats */
    $result->free();
}

//Le classement global de l'utilsateur et son nombre de points -- Ok
if ($result = $mysqli->query("SELECT u.id, MAX(pr.id), pr.rencontre_id, u.oauth_uid, u.oauth_provider, u.picture_url, u.nom, COALESCE(SUM(pr.pts_obtenus), 0) as pts 
			FROM `utilisateurs` u
			LEFT JOIN pronostics pr ON u.id = pr.utilisateur_id 
			LEFT JOIN rencontres r ON r.id = pr.rencontre_id 
			WHERE r.id_competition = $idcompetitions 
			GROUP BY u.`id` ORDER BY pts DESC, pr.id ASC, pr.rencontre_id DESC, pr.dateheure DESC")) {
	$participants = array(); 
	$i=1;
	while ($obj = mysqli_fetch_object($result)){
		$participants[$obj->id] = array('rank' => $i, 'pts' => $obj->pts, 'id_fb' => $obj->oauth_uid, 'nom' => $obj->nom, 'provider' => $obj->oauth_provider);
		$i++;
	}
	//$premier = array_pop(array_reverse($participants));
    /* determine number of rows result set */
    $nb_participants = $result->num_rows;
    /* close result set */
    $result->close();
}

//Le classement hebdo de l'utilsateur et son nombre de points -- Ok
if ($result2 = $mysqli->query("SELECT u.id, MAX(pr.id), pr.rencontre_id, u.oauth_uid, u.oauth_provider, u.picture_url, u.nom, COALESCE(SUM(pr.pts_obtenus), 0) as pts 
			FROM `utilisateurs` u
			LEFT JOIN pronostics pr ON u.id = pr.utilisateur_id 
			LEFT JOIN rencontres r ON r.id = pr.rencontre_id 
			WHERE r.id_competition = $idcompetitions and  pr.dateheure > '2019-07-11 20:01:00' and pr.dateheure < '2019-07-19 20:00:00'
			GROUP BY u.`id` ORDER BY pts DESC, pr.id ASC, pr.rencontre_id DESC, pr.dateheure DESC")) {
	$participants_w = array(); $i=1;
	while ($obj = mysqli_fetch_object($result2)){
		$participants_w[$obj->id] = array('rank' => $i, 'pts' => $obj->pts, 'id_fb' => $obj->oauth_uid, 'nom' => $obj->nom, 'provider' => $obj->oauth_provider);
		$i++;
	}
	//$premier = array_pop(array_reverse($participants_w));
    /* determine number of rows result2 set */
    $nb_participantsw = $result2->num_rows;
    /* close result2 set */
    $result2->close();
}

?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class=" js touch" xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#"><!--<![endif]--><head>
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

		<title>33 Export - Prono Foot</title>

		<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no, width=device-width, height=device-height">
		<?php if(!isset($_SESSION['code'])) { ?>
		<meta property="fb:app_id" content="152343107105833">
        
		
		<?php } ?>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<!--<script src="//s3-eu-west-1.amazonaws.com/fbappz/static/core.krds.js?v=1502446663"></script>-->

		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
		<script src="https://www.33export-foot.com/js/modernizr-2.6.2.min.js?2"></script>
		<script src="https://www.33export-foot.com/js/ka.krds.js?v=1502446663"></script>
		<script>
		
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
			// Set the date we're counting down to
			var countDownDate = new Date("Nov 20, 2022 17:00:00").getTime();

			// Update the count down every 1 second
			var x = setInterval(function() {

			  // Get today's date and time
			  var now = new Date().getTime();
				
			  // Find the distance between now and the count down date
			  var distance = countDownDate - now;
				
			  // Time calculations for days, hours, minutes and seconds
			  var days = Math.floor(distance / (1000 * 60 * 60 * 24));
			  var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			  var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
			  var seconds = Math.floor((distance % (1000 * 60)) / 1000);
				
			  // Output the result in an element with id="demo"
			  document.getElementById("demo").innerHTML = "<h1 style='font-size: 2.5em;'>" + days + "jrs " + hours + "h "
			  + minutes + "m " /*+ seconds + "s "*/ + "</h1>";
				
			  // If the count down is over, write some text 
			  if (distance < 0) {
				clearInterval(x);
				document.getElementById("demo").innerHTML = "C'est Parti!";
			  }
			}, 1000);
		</script>
		<script src="https://www.33export-foot.com/jotokyo/webpushr-sw.js"></script>

		<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/ui-lightness/jquery-ui.css" media="screen" rel="stylesheet">
		<link href="https://www.33export-foot.com/css/h5bp.css" rel="stylesheet" media="all">
		<link href="css/wc_pronostics_mobile_style.css?v=1502446663" rel="stylesheet">
		<script type='application/javascript' src='https://www.33export-foot.com/js/fastclick.js'></script>
		<link href="https://www.33export-foot.com/css/retina.css?v=1502446663" rel="stylesheet" media="all and (-webkit-min-device-pixel-ratio:2)">
	<style>@media print {#ghostery-purple-box {display:none !important}}</style>
	
	</head>
	<body cz-shortcut-listen="true">
		<div id="fb-root" class=" fb_reset"></div>
		
		<div id="container">
			
			<div id="main" role="main">
				<header>
					<a href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/index_test.php" class="logo"></a>
					<?php include_once('top_menu_mobile.php'); ?>
				</header>
				<div class="rankHolder">
					<div class="rankUser">
						<?php if($oauth == 'facebook') { ?>
						<a href="#" rel="callback">
						
							<img src="https://graph.facebook.com/<?php echo $oauth_id; ?>/picture?width=200&height=200" alt="">
						</a>
						<p class="fitTextName">
							<fb:name useyou="false" uid="<?php echo $oauth_id; ?>" linked="false" fb-xfbml-state="rendered"><?php echo $name; ?></fb:name>
						</p>
						<?php } ?>
						<?php if($oauth == 'google') { ?>
						<a href="#" rel="callback">
						
							<img src="<?php echo $picture_url; ?>" alt="" width="200px" />
						</a>
						<p class="fitTextName">
							<?php echo $name; ?>
						</p>
						<?php } ?>
						<?php if($oauth == '') { ?>
						<a href="#" rel="callback">
						
							<img src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/img/pp.png" alt="" width="200px" />
						</a>
						<p class="fitTextName">
							<?php echo $name; ?>
						</p>
						<?php } ?>
					</div>
					<div class="rankContent">
						<div class="rankTitle">
							<h1>Points</h1>
							<h1>Classements</h1>
						</div>
						<ul>
							<li>
								<h4 class="infoTitle"><?php  echo translate_day(strftime("%A")) . " " . strftime("%e") . " " . translate_mois(strftime("%B")) . " " . strftime("%Y") ; ?></h4>
								<?php if(isset($participants[$id])) { ?><h2 class="lastScore"><?php echo $participants[$id]["pts"];  if($participants[$id]["pts"] == 1) echo " pt"; else echo " pts"; ?></h2><?php }
										else { echo "<h2 class=\"lastScore\">0 pt</h2>"; }
								?>
							</li>
							<li class="rankSeparator"></li>
							<li>
								<h4 class="infoTitle classementHeading">Hebdo <?php if($participants_w) { ?><span class="genralScore"><?php echo $participants_w[$id]["rank"]; ?><?php if($participants_w[$id]["rank"] == 1) { ?><sup>er</sup><?php } else { ?><sup>ème</sup><?php } ?> sur <?php echo $nb_participantsw ; ?><?php } ?></h4>
								<h4 class="infoTitle classementHeading">Général 
								<?php if(isset($participants[$id])) { ?><span class="genralScore"><?php echo $participants[$id]["rank"]; ?><?php if($participants[$id]["rank"] == 1) { ?><sup>er</sup><?php } else { ?><sup>ème</sup><?php } ?> sur <?php echo $nb_participants ; ?><?php } else echo "<span class=\"genralScore\">Non classé</span>"; ?></span></h4>
							</li>
						</ul>
					</div>
				</div>


	<div class="contentWrapper ">
    <!--<h2 class="title">Pronostiquez les matchs</h2>-->
    
	<div class="">
		<!--Journée n°31 -->
		<div class="matchScheduleTitle">
			<h1>Le tournoi commence dans </h1>
		</div>
		
		<div class=" " style="margin-top: 20px; margin-bottom: 20px; font-size:0.9em">
		<?php foreach($competitions as $competition) { 
		$mysql = "select sum(p.pts_obtenus) as pts from pronostics p 
					left join rencontres r on r.id = p.rencontre_id 
					left join competitions c on c.id = r.id_competition 
					where p.utilisateur_id = ".$id." and c.id = ".$competition['id']." 
					group by p.utilisateur_id;";
			//echo $mysql;			
			if ($result = $mysqli->query($mysql)) {
				
				if( $result->num_rows > 0 ){
					/* fetch object array */
					while ($row = $result->fetch_row()) {
						if(is_null($row[0]))
							$pts = 0;
						else
							$pts = $row[0];
					}				
				} else {
					$pts = 0;
				}
				
				/* free result set */
				$result->close();
			} else $pts = 0;
		
		?><hr />
		<div style="<?php if($idcompetitions == $competition['id']) { ?>background-color: non;<?php } ?> padding: 0.3em;">
			<div id="demo" class="matchScheduleTitle">
			</div>
			<a href="https://www.33export-foot.com/qatar2022/index_pronostics_mobile3.php?idcompetition=<?php echo $competition['id']; ?>">
				<div id="" class="matchScheduleTitle">
					<!--<img src="<?php echo $competition['image']; ?>" class="exterieur" alt="" style="height:100px;cursor:pointer" >-->
					<h1 style="cursor: pointer;width: 99%;font-family:'canal-icons';font-size: 6em;text-transform: unset;">a</h1>
				</div>
			</a>
		</div>	
		<?php } ?>
			<hr />
			<p style="text-align:center;"><a href="https://www.33export-foot.com/qatar2022/index_regles.php?idcompetition=<?php echo $competition['id']; ?>" style="width: 62%;overflow-wrap: break-word;line-height: 4em;font-size: 1.1em;height: 60px;" class="btnBet red valider_btn" ><i>u</i>Lire le règlement</a></p>
		
		</div>
		
		<!--
		<p class="takeAction">
			<a href="https://prono-foot-33export.herokuapp.com/index_pronostics_mobile.php" class="btnBet red valider_btn" style="line-height: 1.6;padding: 1%;" rel="callback">
			faites vos pronostics</a>
		</p>-->
	</div>


<link rel="stylesheet" href="https://www.33export-foot.com/css/bookmarklet.css">
<!--<script type="application/javascript" src="https://m.pronosfoot.mycanal.fr/assets/js/jquery/bookmarklet/bookmarklet.js"></script>-->
<script type="application/javascript" src="https://www.33export-foot.com/js/jquery.fittext.js"></script>
<?php if(!isset($_SESSION['code'])) { ?>
<script type="text/javascript">
window.fbAsyncInit = function() {
			FB.init({
			  appId      : '1289689755102737',
			  cookie     : true,  // enable cookies to allow the server to access 
								  // the session
			  xfbml      : true,  // parse social plugins on this page
			  version    : 'v15.0' // use graph api version 2.8
			});

			// Now that we've initialized the JavaScript SDK, we call 
			// FB.getLoginStatus().  This function gets the state of the
			// person visiting this page and can return one of three states to
			// the callback you provide.  They can be:
			//
			// 1. Logged into your app ('connected')
			// 2. Logged into Facebook, but not your app ('not_authorized')
			// 3. Not logged into Facebook and can't tell if they are logged into
			//    your app or not.
			//
			// These three cases are handled in the callback function.

			FB.api(
			  '/<?php echo $oauth_id; ?>',
			  'GET',
			  {},
			  function(response) {
				  // Insert your code here
				  console.log(response.first_name);
				  console.log(response.last_name);
				  //document.getElementById('firstname').innerHTML = response.first_name;
				  //document.getElementById('lastname').innerHTML = response.last_name;
			  }
			);

		  };

		  // Load the SDK asynchronously
		  (function(d, s, id){
		 var js, fjs = d.getElementsByTagName(s)[0];
		 if (d.getElementById(id)) {return;}
		 js = d.createElement(s); js.id = id;
		 js.src = "https://connect.facebook.net/en_US/sdk.js";
		 fjs.parentNode.insertBefore(js, fjs);
	   }(document, 'script', 'facebook-jssdk'));
			
			var addToHomeConfig = {
				animationIn: 'bubble',
				animationOut: 'drop',
				lifespan: 20000,
				expire: 0,
				touchIcon: true,
				message: "Ajoutez cette application sur votre %device en cliquant sur %icon, puis <strong>Ajouter à l\’écran d\’accueil</strong>."
			};

$(document).ready(function()
{
	//FB.getLoginStatus(function(response) {
      //if (response.status === 'connected') {
		//	  var uid = response.authResponse.userID; 
			//  var accessToken = response.authResponse.accessToken; 
			  //var signedRequest = response.authResponse.signedRequest;
			/*FB.api('/me?fields=id,first_name,last_name,email,name', function(data) {
				jQuery.ajax({
					type: "GET",
					url: "/ajax/save_new_user.php",
					data: {id: data.id, first_name : data.first_name, email : data.email, name : data.name, accessToken: accessToken, signedRequest : signedRequest },
					forceIframeTransport: true, //force use iframe or will no work            
					success: function(result){
						$('.fitTextName').html(data.name);
						setTimeout(function(){ location.reload(); }, 2000);
						
					},
					error: function(errorThrown){
					}
				});			
			});*/
		//} else {
			//alert('User cancelled login or did not fully authorize.');
		  //}
	//});
});
</script>
<?php } ?>
</div>
</div>

	<footer id="sticky">
	  © 33 Export <?php echo date('Y'); ?>  | <a href="#" id="btn_logout" style="display:inline;">Se déconnecter</a> | <a href="https://www.33export-foot.com/qatar2022/terms.html" style="display:inline;">Termes</a>

		<!--
		<div id="fbForceResize" style="display:none; height:20px; width:100px">&nbsp;</div>
		 -->
	</footer>
		
	<script>
	/* Credits */
	$(document).ready(function()
	{
		//alert("IDocument ready");
		$('#btn_logout').off('click').on('click', function(e) {
				e.preventDefault();
				//alert("Il essaie de se deconnecter sans facebook");
			 window.location.href='https://<?php echo $_SERVER['SERVER_NAME']; ?>/qatar2022/index_mobile3.php?action=deconnexion';
		});
	});
	</script>
</div>
<!-- start webpushr tracking code --> 
<script>(function(w,d, s, id) {if(typeof(w.webpushr)!=='undefined') return;w.webpushr=w.webpushr||function(){(w.webpushr.q=w.webpushr.q||[]).push(arguments)};var js, fjs = d.getElementsByTagName(s)[0];js = d.createElement(s); js.id = id;js.async=1;js.src = "https://cdn.webpushr.com/app.min.js";
fjs.parentNode.appendChild(js);}(window,document, 'script', 'webpushr-jssdk'));
webpushr('setup',{'key':'BI_bONt60K1wGwVp1bsivq5UUvWN-Xws1Noh9QZoatPzPE_Up0_VajVU5gLle8xDf0BEte_XFCg-n8bzMK5HGEc' });</script>
<!-- end webpushr tracking code -->
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-7YRDS0SQ0P"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-7YRDS0SQ0P');
</script>
</body>

</html>
<?php 

/* Fermeture de la connexion */
$mysqli->close();

// 

?>