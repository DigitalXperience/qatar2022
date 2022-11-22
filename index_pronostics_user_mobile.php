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

$sql = "SELECT 
			p.score_eq1 as pr_eq1, p.score_eq2 as pr_eq2, p.pts_obtenus, r.score_eq1, r.score_eq2, e1.name as eq1, e1.flag as flag1, e2.name as eq2, e2.flag as flag2, u.nom   
		FROM 
			pronostics p 
		LEFT JOIN 
			rencontres r ON r.id = p.rencontre_id 
		LEFT JOIN 
			equipes e1 ON e1.id = r.equipe_id1 
		LEFT JOIN 
			equipes e2 ON e2.id = r.equipe_id2 
		LEFT JOIN 
			utilisateurs u ON u.id = p.utilisateur_id 
		WHERE 
			p.utilisateur_id = '" . $_GET['user'] . "' AND p.pts_obtenus IS NOT NULL  and r.id_competition = '".$idcompetitions."'
		ORDER BY 
			r.date_heure DESC";
	 
$pronos = null;
if ($result = mysqli_query($mysqli, $sql)) {  
   while ($obj = mysqli_fetch_object($result)){
		$pronos[] = $obj;
   }
   $result->close();
}

//Le nombre de participants -- Ok
if ($result = $mysqli->query("SELECT u.id, MAX(pr.id), pr.rencontre_id, u.oauth_uid, u.oauth_provider, u.picture_url, u.nom, COALESCE(SUM(pr.pts_obtenus), 0) as pts 
			FROM `utilisateurs` u
			LEFT JOIN pronostics pr ON u.id = pr.utilisateur_id 
			LEFT JOIN rencontres r ON r.id = pr.rencontre_id 
			WHERE r.id_competition = $idcompetitions 
			GROUP BY u.`id` ORDER BY pts DESC, pr.id ASC, pr.rencontre_id DESC, pr.dateheure DESC")) {
	$participants = array(); $i=1;
	while ($obj = mysqli_fetch_object($result)){
		$participants[$obj->id] = array('rank' => $i, 'pts' => $obj->pts, 'id_fb' => $obj->oauth_uid, 'nom' => $obj->nom, 'provider' => $obj->oauth_provider);
		$i++;
	}
	//$premier = array_pop(array_reverse($participants));
    /* determine number of rows result set */
    $nb_participants = $result->num_rows;
	 /* Libération des résultats */
    $result->free();
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
					appId			: "152343107105833",
					version			: "v12.0",
					callbackUrl		: "https://www.33export-foot.com/qatar2022/",

					locale			: 'fr_FR',
					signedRequest	: '',
					userId			: '',
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
		<link href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/css/wc_pronostics_mobile_style.css?v=1502446663" rel="stylesheet">
		<script type='application/javascript' src='https://<?php echo $_SERVER['SERVER_NAME']; ?>/js/fastclick.js'></script>
		<link href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/css/retina.css?v=1502446663" rel="stylesheet" media="all and (-webkit-min-device-pixel-ratio:2)">
	<style>@media print {#ghostery-purple-box {display:none !important}}</style><link type="text/css" rel="stylesheet" href="chrome-extension://pioclpoplcdbaefihamjohnefbikjilc/content.css"></head>
	<body cz-shortcut-listen="true">
		<div id="fb-root" class=" fb_reset"><div style="position: absolute; top: -10000px; height: 0px; width: 0px;"><div><iframe name="fb_xdm_frame_https" frameborder="0" allowtransparency="true" allowfullscreen="true" scrolling="no" allow="encrypted-media" id="fb_xdm_frame_https" aria-hidden="true" title="Facebook Cross Domain Communication Frame" tabindex="-1" src="https://staticxx.facebook.com/connect/xd_arbiter/r/qMnGlIs-JNW.js?version=42#channel=f2f22fc076706a8&amp;origin=https%3A%2F%2Fm.pronosfoot.mycanal.fr" style="border: none;"></iframe></div></div><div style="position: absolute; top: -10000px; height: 0px; width: 0px;"><div></div></div></div>
		<form action="" method="post" id="fb-form"><input name="authResponse" value="{&quot;accessToken&quot;:&quot;EAAAAHN9UfZAMBACKDPx7RrSqIiKkZCbZC1BJxZCGvcFbjgXqVpUafjgXFUZAuVSmXE4hUZC3MLCFZAFfxDBIJpxc65tZAD8dnMmS824xtYGx4Ni5S0A2bvr6K4nlRToJywuJ7huiSa9G5s9m1harExwoudtAWemwo3ZCKNUlyiDd1EkURbqqLQ1iYruntFyNYenbWi9dKIZBeU1AZDZD&quot;,&quot;userID&quot;:&quot;10155870072269961&quot;,&quot;expiresIn&quot;:5508,&quot;signedRequest&quot;:&quot;vSqipLt4VuyVmq-6xdwSzfJeothHH-ZQnX_FvV2EgMc.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImNvZGUiOiJBUUFBQmlvVEl4QWpjRmJ5V2FhN3FYUmNkbUNCN3RFRUU3SDlkR2htWnlBWjhkUmhNOVBPdEZway1kc29Rc1l0cktPRklaM21FLXF2WTFSY2NtS3RJVFJHRWFMUXBUWWlKQ0FtWm5yWWdnUmo5VHhhWjdZeG8yb0NDczNRX05OQlBEd2xlaUhXVzI3aFNhaUZwTWx0MS1Ob0xyd2NGYmRJWjR0eXc2YzlvN3NwWVVzZ3R2c3JveWxXYjFJNF9BZ0s3MElyS2ZxNC1NMGYzWVpMM01jVWtCcVFveXZKWHV0X1lmeDlwcEJkOHJUbGY1c210S01Ca1YtbE5wNm1YalNnM3BvRVlsalU4aGF1ZTM1cnVmMEdhTTk3RkI1dXhCZnIwb24xLWxGRVAyTk5yVjVGLTdqcGJYdnBsZTVMUG1aOWpWejY2SUF1dERsbWpjenlETmplWVEyRSIsImlzc3VlZF9hdCI6MTUyOTQ2MTY5MiwidXNlcl9pZCI6IjEwMTU1ODcwMDcyMjY5OTYxIn0&quot;,&quot;reauthorize_required_in&quot;:5034635}" type="hidden"></form>

		<div id="container">
			<header>

			</header>
			<div id="main" role="main">
			<header>
				<header>
				<a href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/qatar2022/index_test.php?idcompetition=<?php echo $idcompetitions; ?>" class="logo"></a>
				<?php include('top_menu_mobile.php'); ?>
			</header>
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
	<h1 class="rankingTitle">Mes pronostics</h1>
</div>
<ul class="subNavigation">
<li><a href="#" class="active">Points</a></li>
<!--<li><a href="https://m.pronosfoot.mycanal.fr/user/forum" class="">Forum</a></li>-->
</ul>
<div class="contentWrapper">
<div class="userRankInfo">
	<div class="userRankDetails">
			<?php if($oauth == 'facebook') { ?>
				<a href="#" rel="callback">
				
					<img src="https://graph.facebook.com/<?php echo $oauth_id; ?>/picture?width=200&height=200" alt="">
				</a>
			<?php } 
			 if($oauth == 'google') { ?>
				<a href="#" rel="callback">
				
					<img src="<?php echo $picture_url; ?>" alt="">
				</a>
			<?php } 
			 if($oauth == '') { ?>
				<a href="#" rel="callback">
				
					<img src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/img/pp.png" alt="" width="200px" />
				</a>
			<?php } ?>
<p>
	<fb:name useyou="false" uid="<?php echo $oauth_id; ?>" linked="false" fb-xfbml-state="rendered"><?php echo $name; ?></fb:name>
</p>

			
		</div>
	<div class="myScore">
		<h1><?php if(isset($_GET['user'])) 
				$rank = $participants[$_GET['user']]["rank"]; 
			  else 
				$rank = $participants[$id]["rank"];
			
			echo $rank; ?>
			<?php if($rank == 1) { ?><sup>er</sup><?php } else { ?><sup>ème</sup><?php } ?></h1>
		<p>sur <?php echo $nb_participants; ?></p>
	</div>
</div>
</div>
<!--
<div class="betDateSelection">
	<div class="caroufredsel_wrapper" style="display: block; text-align: start; float: none; position: relative; top: auto; right: auto; bottom: auto; left: auto; z-index: auto; width: 318px; height: 68px; margin: 0px; overflow: hidden;"><ul id="rounds_carousel" style="text-align: left; float: none; position: absolute; top: 0px; right: auto; bottom: auto; left: 0px; margin: 0px; z-index: auto; width: 1590px; height: 68px;">
		<li class="" style="width: 106px;"><a class="jsRoundBtn" href="https://m.pronosfoot.mycanal.fr/user/bets?id_round=33&amp;id_user=100006607981744">33<sup>ème</sup></a></li><li class="" style="width: 106px;"><a class="jsRoundBtn" href="https://m.pronosfoot.mycanal.fr/user/bets?id_round=34&amp;id_user=100006607981744">34<sup>ème</sup></a></li><li class="active" style="width: 106px;"><a class="jsRoundBtn" href="https://m.pronosfoot.mycanal.fr/user/bets?id_round=36&amp;id_user=100006607981744">36<sup>ème</sup></a></li>
	<li class="" style="width: 106px;"><a class="jsRoundBtn" href="https://m.pronosfoot.mycanal.fr/user/bets?id_round=30&amp;id_user=100006607981744">30<sup>ème</sup></a></li><li class="" style="width: 106px;"><a class="jsRoundBtn" href="https://m.pronosfoot.mycanal.fr/user/bets?id_round=31&amp;id_user=100006607981744">31<sup>ème</sup></a></li><li class="" style="width: 106px;"><a class="jsRoundBtn" href="https://m.pronosfoot.mycanal.fr/user/bets?id_round=32&amp;id_user=100006607981744">32<sup>ème</sup></a></li></ul></div>
</div>-->


<div id="js_errors" style="display: none" class="error"></div>
<h3 class="betTitle">Les pronostics de <?php echo $name; ?></h3>
<?php 
if($pronos) {
	foreach($pronos as $p) { ?>
<div class="mainBet">
	<div class="clubName">
		<span><img src="<?php echo $p->flag1; ?>" alt=""></span>
		<p><?php echo utf8_encode($p->eq1); ?></p>
	</div>
	<div class="clubName">
		<span><img src="<?php echo $p->flag2; ?>" alt=""></span>
		<p><?php echo utf8_encode($p->eq2); ?></p>
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
<?php }
} ?>

</div>

<footer id="sticky">
	  © 33 Export <?php echo date('Y'); ?>  | <a href="#" id="btn_logout" style="display:inline;">Se déconnecter</a> | <a href="#" style="display:inline;">Mes Lots</a>

		<!--
		<div id="fbForceResize" style="display:none; height:20px; width:100px">&nbsp;</div>
		 -->
	</footer>
		</div>
		<script>
		/* Credits */
		$(document).ready(function()
		{
			
            $(document).on('fbready', function() {
                $('#btn_logout').off('click').on('click', function(e) {
                    e.preventDefault();

                    FB.api('/me/permissions', 'delete', function()
                    {
                        FB.getLoginStatus(function(r)
                        {
                            Facebook.redirect(ENV.callback_url);
                        }, true);

                        Facebook.isUserConnected = false;
                    });
                });
				FB.api('/<?php echo $idfb; ?>',
						'GET',
						{fields: 'last_name'}, function(response) {
				  console.log(response);
				});
            });
		});
		</script>
</body></html>