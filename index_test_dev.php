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

	<!-- New vision -->
	<div class="col-12 col-md-1024-4 col-lg-3 order-2 order-md-1024-1 ff-px-0">
		<div class="">	
			<div class="d-block d-md-flex d-md-1024-block sponsorship-clock-component_sponsorshipClockContainer__2_YZ8" style="background-color: rgb(85, 0, 101);">
				<div class="col-12 col-md-6 col-md-1024-12">
					<div class="ff-pt-24 ff-pt-md-32 ff-pt-xl-64 ff-py-lg-24 ff-pb-xl-32 col-12 d-flex justify-content-center">
						<div class="col-6">
							<div class="d-block">
								<div class="hublot-component d-flex justify-content-center hublot-watch-tournament_hublotWatchContainerMobile__3zeJV">
									<a href="https://www.hublot.com/?utm_source=FIFA&amp;utm_medium=site&amp;utm_campaign=official-timekeeper" rel="noreferrer" target="_blank" class="global-link">
										<div class="hublot-timekeeper hublot-watch-tournament_hublotWatch__1BIYF visible">
											<div class="hublot-watch">
												<img decoding="async" loading="lazy" height="auto" width="114" src="/fifaplus/static/media/watch.bf980ceb.png" alt="Hublot" title="" class="image_img__3ckHZ hublot-img-watch hublot-img-desktop">
												<div class="hublot-handles hublot-watch-tournament_hublotHandle__3tm4d">
													<div class="handle hublot-hour hublot-watch-tournament_hublotHour__1U45L" style="transform: rotate(165.5deg);"></div>
													<div class="handle hublot-minute hublot-watch-tournament_hublotMinutes__uOdh-" style="transform: rotate(188.012deg);"></div>
													<div class="handle hublot-second hublot-watch-tournament_second__K98bq"></div>
													<div class="handle hublot-seconds-chrono hublot-watch-tournament_seconds__39qys" style="transform: rotate(120.702deg);"></div>
													<div class="logo-glass hublot-watch-tournament_logoGlass__1FVhR"></div>
												</div>
											</div>
										</div>
									</a>
								</div>
								<div class="ff-pt-8 ff-pt-xl-16 ff-pt-xl-8 col-12">
									<div class="ff-py-4 hublot-watch-tournament_hublotLogoContainerMobile__1bfI5">
										<a href="https://www.hublot.com/?utm_source=FIFA&amp;utm_medium=site&amp;utm_campaign=official-timekeeper" rel="noreferrer" target="_blank" class="ff-mb-0 justify-content-center'}">
											<img decoding="async" loading="lazy" height="auto" width="144" src="/fifaplus/static/media/hublot-logo.e0e810d9.svg" alt="Hublot logo" title="Hublot logo" class="image_img__3ckHZ">
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 ff-py-lg-8 ff-py-xl-32">
						<div class="d-flex align-items-center justify-content-evenly ff-py-16 ff-px-32 ff-px-md-16" style="color: rgb(255, 255, 255);">
							<svg width="90" height="10" viewBox="0 0 107 10" xmlns="http://www.w3.org/2000/svg" class="ff-px-md-1024-0 sponsorship-clock-component_lineDiamondLeft__1Qmc-">
							<path d="M106.83 5L102.5 0.669873L98.1699 5L102.5 9.33013L106.83 5ZM0.5 5.75H102.5V4.25H0.5V5.75Z" fill="#DAC96D"></path><defs><linearGradient id="paint0_linear_1425_38312" x1="-2.4312" y1="5.50025" x2="105.455" y2="5.50025" gradientUnits="userSpaceOnUse"><stop stop-color="#DAC96D"></stop><stop offset="0.21" stop-color="#D6C56C"></stop><stop offset="0.38" stop-color="#CDBB6B"></stop><stop offset="0.5" stop-color="#C1AF6A"></stop><stop offset="0.62" stop-color="#CDBB6B"></stop><stop offset="0.79" stop-color="#D6C56C"></stop><stop offset="1" stop-color="#DAC96D"></stop></linearGradient></defs></svg>
							<h4 class="ff-mb-0 ff-px-4">
								<span class="d-md-none">
									<span style="display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;">Tournament starts in</span>
								</span>
								<span class="d-none d-md-block">
									<span style="display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;">Tournament starts in</span>
								</span>
							</h4>
							<svg width="90" height="10" viewBox="0 0 107 10" xmlns="http://www.w3.org/2000/svg" class="ff-px-md-1024-0 sponsorship-clock-component_lineDiamondRight__1J4kZ"><path d="M106.83 5L102.5 0.669873L98.1699 5L102.5 9.33013L106.83 5ZM0.5 5.75H102.5V4.25H0.5V5.75Z" fill="#DAC96D"></path><defs><linearGradient id="paint0_linear_1425_38312" x1="-2.4312" y1="5.50025" x2="105.455" y2="5.50025" gradientUnits="userSpaceOnUse"><stop stop-color="#DAC96D"></stop><stop offset="0.21" stop-color="#D6C56C"></stop><stop offset="0.38" stop-color="#CDBB6B"></stop><stop offset="0.5" stop-color="#C1AF6A"></stop><stop offset="0.62" stop-color="#CDBB6B"></stop><stop offset="0.79" stop-color="#D6C56C"></stop><stop offset="1" stop-color="#DAC96D"></stop></linearGradient></defs></svg>
						</div>
					</div>
					<div class="d-flex align-items-center justify-content-center ff-py-xl-24" style="color: rgb(255, 255, 255);">
						<div class="ff-mx-16 sponsorship-clock-component_countdownValueContainer__ngmAP">
							<div class="ff-mb-8 sponsorship-clock-component_countdownValue__1YdGl">14</div>
							<div class="text-uppercase text-sm">Days</div>
						</div>
						<div class="ff-pb-32"><svg width="20" height="19" viewBox="0 0 20 19" fill="#DAC96D" xmlns="http://www.w3.org/2000/svg"><path d="M10.0036 0.013918L0.00390625 9.51367L10.0036 19.0134L20.0034 9.51367L10.0036 0.013918Z" fill="current"></path></svg></div>
						<div class="ff-mx-16 sponsorship-clock-component_countdownValueContainer__ngmAP">
							<div class="ff-mb-8 sponsorship-clock-component_countdownValue__1YdGl">11</div>
							<div class="text-uppercase text-sm">Hours</div>
						</div>
						<div class="ff-pb-32"><svg width="20" height="19" viewBox="0 0 20 19" fill="#DAC96D" xmlns="http://www.w3.org/2000/svg"><path d="M10.0036 0.013918L0.00390625 9.51367L10.0036 19.0134L20.0034 9.51367L10.0036 0.013918Z" fill="current"></path></svg></div>
						<div class="ff-mx-16 sponsorship-clock-component_countdownValueContainer__ngmAP"><div class="ff-mb-8 sponsorship-clock-component_countdownValue__1YdGl">28</div>
							<div class="text-uppercase text-sm">Minutes</div>
						</div>
					</div>
				</div>
				<div class="col-12 col-md-6 col-md-1024-12 ff-px-16 ff-px-md-32 d-flex align-items-center justify-content-center">
					<a class="col-12" href="/fifaplus/en/match-centre/match/17/255711/285063/400128082?competitionEntryId=17">
						<div class="match-card_matchCard__1ctbJ">
							<div class="match-card_matchCardInfo__3AJXw">
								<div class="d-flex justify-content-between"><div>Match 1, Group A</div><div>20 Nov 2022</div></div><div>Al Bayt Stadium</div>
							</div>
							<div class="match-card_matchCardData__1AuZ_"><div><div class="match-card_matchCardTeam__3YGRQ"><div class="match-card_matchCardTeamLogo__18yc-"><img decoding="async" loading="lazy" height="auto" width="100%" src="https://cloudinary.fifa.com/api/v3/picture/flags-sq-4/QAT?tx=c_fill,g_auto,q_auto" alt="" title="" class="image_img__3ckHZ"></div><div class="match-card_matchCardTeamName__3ETbI ff-pl-8">Qatar</div></div><div class="match-card_matchCardTeam__3YGRQ"><div class="match-card_matchCardTeamLogo__18yc-"><img decoding="async" loading="lazy" height="auto" width="100%" src="https://cloudinary.fifa.com/api/v3/picture/flags-sq-4/ECU?tx=c_fill,g_auto,q_auto" alt="" title="" class="image_img__3ckHZ"></div><div class="match-card_matchCardTeamName__3ETbI ff-pl-8">Ecuador</div></div></div><div class="match-card_matchCardTime__PVur_ d-flex align-items-center">17:00</div></div>
						</div>
					</a>
				</div>
			</div>
		</div>
	</div>
	
	<!-- End new vision -->
	
	
	<div class="contentWrapper ">
    <!--<h2 class="title">Pronostiquez les matchs</h2>-->
    
	<div class="">
		<!--Journée n°31 -->
		<div class="matchScheduleTitle">
			<h1>Cliquez sur le ballon pour pronostiquer</h1>
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
			<a href="https://www.33export-foot.com/qatar2022/index_pronostics_mobile3.php?idcompetition=<?php echo $competition['id']; ?>">
			<div class="matchScheduleTitle">
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
			  appId      : '152343107105833',
			  cookie     : true,  // enable cookies to allow the server to access 
								  // the session
			  xfbml      : true,  // parse social plugins on this page
			  version    : 'v12.0' // use graph api version 2.8
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