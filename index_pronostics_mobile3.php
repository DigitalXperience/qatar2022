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

// Le nom de la compétition
$query = "SELECT id, nom  FROM competitions WHERE id = '" . $idcompetitions . "' LIMIT 1;";

if ($result = mysqli_query($mysqli, $query)) {  
   while ($obj = mysqli_fetch_object($result)){
		$id_competition = $obj->id;
		$nom_competition = $obj->nom;
   }
   $result->close();
}

if(!isset($_SESSION['code'])) {
	/* Update la derniere présence sur l'application */
	$sql = "UPDATE `utilisateurs` SET `last_login` = '".date("Y-m-d H:i:s")."', last_ip = '".get_client_ip()."'  WHERE id = '" . $_SESSION['id'] . "' LIMIT 1; ";
	$mysqli->query($sql);

	// Les infos de l'utilisateur 
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
	/* Update la derniere présence sur l'application */
	$sql = "UPDATE `utilisateurs` SET `last_login` = '".date("Y-m-d H:i:s")."', last_ip = '".get_client_ip()."'  WHERE id = '" . $_SESSION['id'] . "' LIMIT 1; ";
	$mysqli->query($sql);

	// Les infos de l'utilisateur 
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
			$tel = $obj->numero;
	   }
	   $result->close();
	}
}

// if(is_null($tel) || $tel == "") {
	// header('Location: https://www.33export-foot.com/mes_infos.php?idcompetition='.$idcompetitions);
	// die();
// }

// Les rencontres déjà pronostiquées ou pas de l'utilisateur 
$query = "SELECT r.id, r.date_heure, r.score_eq1, r.score_eq2, e1.name as nom_equipe1, e1.flag as flag1, e2.name as nom_equipe2, e2.flag as flag2, 
			p.`score_eq1` as prono_score_eq1, r.equipe_id1, r.equipe_id2, p.score_ouverture, p.score_min, 
			p.`score_eq2` as prono_score_eq2, p.`vainqueur_id` as prono_vainqueur, ep.name as vainqueur, 
			DATE_FORMAT(r.date_heure, '%a') as jour, DATE_FORMAT(r.date_heure, '%e %M') as date, DATE_FORMAT(r.date_heure, '%k:%i') as heure, s.nom as nom_stade     
		FROM `rencontres` r 
		
		LEFT JOIN `equipes` e1 ON e1.id = r.equipe_id1 
		LEFT JOIN `equipes` e2 ON e2.id = r.equipe_id2 
		LEFT JOIN pronostics p ON p.rencontre_id = r.id AND p.utilisateur_id = $id 
		LEFT JOIN stades s ON r.id_stade = s.id 
		LEFT JOIN equipes ep ON ep.id = p.`vainqueur_id` 
		WHERE r.date_heure > '" . date('Y-m-d H:i:s') . "' AND r.en_avant = 1 AND r.id_competition = $idcompetitions 
		ORDER BY r.date_heure ASC  ";
   //echo "<pre>"; echo($query); die;
$rencontres_nc = array();
if ($result = mysqli_query($mysqli, $query)) {  
   while ($obj = mysqli_fetch_object($result)){
		$rencontres_nc[] = $obj;
   }
   $result->close();
}
//var_dump($rencontres_nc); die;
if(isset($_GET['msg'])) {
	if($_GET['msg'] == 'pronogood')
		$message = "Vos pronostics ont bien été enregistrés ! <br> Vous pouvez encore pronostiquer jusqu'au <b>début de chaque match !";
	else 
		$message = "Votre pronostic n'a pas été enregistré avant le début de la rencontre !";
}

if($_POST) {
	$match = array();
	foreach($_POST as $key => $val) {
		$pronos = explode("_", $key);
		
		if(count($pronos) == 5) {
			$match[$pronos[4]][$pronos[2]] = $val;
			$match[$pronos[4]]['dateheure'] = $_POST[$pronos[4]];
		}
		if(count($pronos) == 3) {
			$match[$pronos[2]][$pronos[0]."_".$pronos[1]] = $val;
		}
	}
	
	foreach($match as $rc => $val) {
		$now = date('Y-m-d H:i:s'); 
		//var_dump($val['score_min']); die;
		//if($val['score_min'] == "0") { $val['score_min'] = null}
		if(strtotime($now) < strtotime($val['dateheure'])) { // On controle que l'heure du pronostique de ce match n'est pas dépassé!!!
			
			$val['score_ouverture'] = !empty($val['score_ouverture']) ? "'".$val['score_ouverture']."'" : "NULL";
			
			if($val['score_min'] == "0")
				$sql = "INSERT INTO pronostics(id, rencontre_id, utilisateur_id, score_eq1, score_eq2, vainqueur_id, score_ouverture, score_min, dateheure, last_ip_for_update) 
					VALUES (NULL, $rc, ".$_POST['id'].", '".$val['eq1']."', '".$val['eq2']."', NULL, ".$val['score_ouverture'].", NULL, '" . date('Y-m-d H:i:s') . "', '".get_client_ip()."')
					ON DUPLICATE KEY UPDATE score_eq1 = ".$val['eq1'].", score_eq2 = ".$val['eq2'].", score_ouverture = ".$val['score_ouverture'].", score_min = NULL, 
					dateheure = '" . date('Y-m-d H:i:s') . "', last_ip_for_update = '".get_client_ip()."' ";
			else
				$sql = "INSERT INTO pronostics(id, rencontre_id, utilisateur_id, score_eq1, score_eq2, vainqueur_id, score_ouverture, score_min, dateheure, last_ip_for_update) 
					VALUES (NULL, $rc, ".$_POST['id'].", '".$val['eq1']."', '".$val['eq2']."', NULL, ".$val['score_ouverture'].", '".$val['score_min']."', '" . date('Y-m-d H:i:s') . "', '".get_client_ip()."')
					ON DUPLICATE KEY UPDATE score_eq1 = ".$val['eq1'].", score_eq2 = ".$val['eq2'].", score_ouverture = ".$val['score_ouverture'].", score_min = '".$val['score_min']."', 
					dateheure = '" . date('Y-m-d H:i:s') . "', last_ip_for_update = '".get_client_ip()."'";
					
			if ($mysqli->query($sql) === TRUE) {
				$message = "Vos pronostics ont été enregistrés!";
				
				
			}
		}
	}
	$url = "https://www.33export-foot.com/qatar2022/index_pronostics_mobile3.php?msg=pronogood&idcompetition=" . $idcompetitions;
	
	header("Location: $url");
	die;
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

		<title>33 Export Prono Foot</title>

		<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no, width=device-width, height=device-height">
		<meta property="fb:app_id" content="152343107105833">
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
					callbackUrl		: "https://www.33export-foot.com/",

					locale			: 'fr_FR',
					signedRequest	: '',
					userId			: '',
					mode			: 'website',
					useCookie		:	true
				}
			};
        </script>

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>

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
		<link href="css/wc_pronostics_mobile_style.css?v=1502446663" rel="stylesheet">
		<script type='application/javascript' src='https://<?php echo $_SERVER['SERVER_NAME']; ?>/js/fastclick.js'></script>
		<link href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/css/retina.css?v=1502446663" rel="stylesheet" media="all and (-webkit-min-device-pixel-ratio:2)">
	<style>@media print {#ghostery-purple-box {display:none !important}}</style><link type="text/css" rel="stylesheet" href="chrome-extension://pioclpoplcdbaefihamjohnefbikjilc/content.css"><style type="text/css">.fb_hidden{position:absolute;top:-10000px;z-index:10001}.fb_reposition{overflow:hidden;position:relative}.fb_invisible{display:none}.fb_reset{background:none;border:0;border-spacing:0;color:#000;cursor:auto;direction:ltr;font-family:"lucida grande", tahoma, verdana, arial, sans-serif;font-size:11px;font-style:normal;font-variant:normal;font-weight:normal;letter-spacing:normal;line-height:1;margin:0;overflow:visible;padding:0;text-align:left;text-decoration:none;text-indent:0;text-shadow:none;text-transform:none;visibility:visible;white-space:normal;word-spacing:normal}.fb_reset>div{overflow:hidden}.fb_link img{border:none}@keyframes fb_transform{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:scale(1)}}.fb_animate{animation:fb_transform .3s forwards}
.fb_dialog{background:rgba(82, 82, 82, .7);position:absolute;top:-10000px;z-index:10001}.fb_reset .fb_dialog_legacy{overflow:visible}.fb_dialog_advanced{padding:10px;border-radius:8px}.fb_dialog_content{background:#fff;color:#333}.fb_dialog_close_icon{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/yq/r/IE9JII6Z1Ys.png) no-repeat scroll 0 0 transparent;cursor:pointer;display:block;height:15px;position:absolute;right:18px;top:17px;width:15px}.fb_dialog_mobile .fb_dialog_close_icon{top:5px;left:5px;right:auto}.fb_dialog_padding{background-color:transparent;position:absolute;width:1px;z-index:-1}.fb_dialog_close_icon:hover{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/yq/r/IE9JII6Z1Ys.png) no-repeat scroll 0 -15px transparent}.fb_dialog_close_icon:active{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/yq/r/IE9JII6Z1Ys.png) no-repeat scroll 0 -30px transparent}.fb_dialog_loader{background-color:#f6f7f9;border:1px solid #606060;font-size:24px;padding:20px}.fb_dialog_top_left,.fb_dialog_top_right,.fb_dialog_bottom_left,.fb_dialog_bottom_right{height:10px;width:10px;overflow:hidden;position:absolute}.fb_dialog_top_left{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/ye/r/8YeTNIlTZjm.png) no-repeat 0 0;left:-10px;top:-10px}.fb_dialog_top_right{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/ye/r/8YeTNIlTZjm.png) no-repeat 0 -10px;right:-10px;top:-10px}.fb_dialog_bottom_left{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/ye/r/8YeTNIlTZjm.png) no-repeat 0 -20px;bottom:-10px;left:-10px}.fb_dialog_bottom_right{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/ye/r/8YeTNIlTZjm.png) no-repeat 0 -30px;right:-10px;bottom:-10px}.fb_dialog_vert_left,.fb_dialog_vert_right,.fb_dialog_horiz_top,.fb_dialog_horiz_bottom{position:absolute;background:#525252;filter:alpha(opacity=70);opacity:.7}.fb_dialog_vert_left,.fb_dialog_vert_right{width:10px;height:100%}.fb_dialog_vert_left{margin-left:-10px}.fb_dialog_vert_right{right:0;margin-right:-10px}.fb_dialog_horiz_top,.fb_dialog_horiz_bottom{width:100%;height:10px}.fb_dialog_horiz_top{margin-top:-10px}.fb_dialog_horiz_bottom{bottom:0;margin-bottom:-10px}.fb_dialog_iframe{line-height:0}.fb_dialog_content .dialog_title{background:#6d84b4;border:1px solid #365899;color:#fff;font-size:14px;font-weight:bold;margin:0}.fb_dialog_content .dialog_title>span{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/yd/r/Cou7n-nqK52.gif) no-repeat 5px 50%;float:left;padding:5px 0 7px 26px}body.fb_hidden{-webkit-transform:none;height:100%;margin:0;overflow:visible;position:absolute;top:-10000px;left:0;width:100%}.fb_dialog.fb_dialog_mobile.loading{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/ya/r/3rhSv5V8j3o.gif) white no-repeat 50% 50%;min-height:100%;min-width:100%;overflow:hidden;position:absolute;top:0;z-index:10001}.fb_dialog.fb_dialog_mobile.loading.centered{width:auto;height:auto;min-height:initial;min-width:initial;background:none}.fb_dialog.fb_dialog_mobile.loading.centered #fb_dialog_loader_spinner{width:100%}.fb_dialog.fb_dialog_mobile.loading.centered .fb_dialog_content{background:none}.loading.centered #fb_dialog_loader_close{color:#fff;display:block;padding-top:20px;clear:both;font-size:18px}#fb-root #fb_dialog_ipad_overlay{background:rgba(0, 0, 0, .45);position:absolute;bottom:0;left:0;right:0;top:0;width:100%;min-height:100%;z-index:10000}#fb-root #fb_dialog_ipad_overlay.hidden{display:none}.fb_dialog.fb_dialog_mobile.loading iframe{visibility:hidden}.fb_dialog_content .dialog_header{-webkit-box-shadow:white 0 1px 1px -1px inset;background:-webkit-gradient(linear, 0% 0%, 0% 100%, from(#738ABA), to(#2C4987));border-bottom:1px solid;border-color:#1d4088;color:#fff;font:14px Helvetica, sans-serif;font-weight:bold;text-overflow:ellipsis;text-shadow:rgba(0, 30, 84, .296875) 0 -1px 0;vertical-align:middle;white-space:nowrap}.fb_dialog_content .dialog_header table{-webkit-font-smoothing:subpixel-antialiased;height:43px;width:100%}.fb_dialog_content .dialog_header td.header_left{font-size:12px;padding-left:5px;vertical-align:middle;width:60px}.fb_dialog_content .dialog_header td.header_right{font-size:12px;padding-right:5px;vertical-align:middle;width:60px}.fb_dialog_content .touchable_button{background:-webkit-gradient(linear, 0% 0%, 0% 100%, from(#4966A6), color-stop(.5, #355492), to(#2A4887));border:1px solid #29487d;-webkit-background-clip:padding-box;-webkit-border-radius:3px;-webkit-box-shadow:rgba(0, 0, 0, .117188) 0 1px 1px inset, rgba(255, 255, 255, .167969) 0 1px 0;display:inline-block;margin-top:3px;max-width:85px;line-height:18px;padding:4px 12px;position:relative}.fb_dialog_content .dialog_header .touchable_button input{border:none;background:none;color:#fff;font:12px Helvetica, sans-serif;font-weight:bold;margin:2px -12px;padding:2px 6px 3px 6px;text-shadow:rgba(0, 30, 84, .296875) 0 -1px 0}.fb_dialog_content .dialog_header .header_center{color:#fff;font-size:16px;font-weight:bold;line-height:18px;text-align:center;vertical-align:middle}.fb_dialog_content .dialog_content{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/y9/r/jKEcVPZFk-2.gif) no-repeat 50% 50%;border:1px solid #555;border-bottom:0;border-top:0;height:150px}.fb_dialog_content .dialog_footer{background:#f6f7f9;border:1px solid #555;border-top-color:#ccc;height:40px}#fb_dialog_loader_close{float:left}.fb_dialog.fb_dialog_mobile .fb_dialog_close_button{text-shadow:rgba(0, 30, 84, .296875) 0 -1px 0}.fb_dialog.fb_dialog_mobile .fb_dialog_close_icon{visibility:hidden}#fb_dialog_loader_spinner{animation:rotateSpinner 1.2s linear infinite;background-color:transparent;background-image:url(https://static.xx.fbcdn.net/rsrc.php/v3/yD/r/t-wz8gw1xG1.png);background-repeat:no-repeat;background-position:50% 50%;height:24px;width:24px}@keyframes rotateSpinner{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
.fb_iframe_widget{display:inline-block;position:relative}.fb_iframe_widget span{display:inline-block;position:relative;text-align:justify}.fb_iframe_widget iframe{position:absolute}.fb_iframe_widget_fluid_desktop,.fb_iframe_widget_fluid_desktop span,.fb_iframe_widget_fluid_desktop iframe{max-width:100%}.fb_iframe_widget_fluid_desktop iframe{min-width:220px;position:relative}.fb_iframe_widget_lift{z-index:1}.fb_hide_iframes iframe{position:relative;left:-10000px}.fb_iframe_widget_loader{position:relative;display:inline-block}.fb_iframe_widget_fluid{display:inline}.fb_iframe_widget_fluid span{width:100%}.fb_iframe_widget_loader iframe{min-height:32px;z-index:2;zoom:1}.fb_iframe_widget_loader .FB_Loader{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/y9/r/jKEcVPZFk-2.gif) no-repeat;height:32px;width:32px;margin-left:-16px;position:absolute;left:50%;z-index:4}
.fb_customer_chat_bounce_in_v1{animation-duration:250ms;animation-name:fb_bounce_in_v1}.fb_customer_chat_bounce_out_v1{animation-duration:250ms;animation-name:fb_bounce_out_v1}.fb_customer_chat_bounce_in_v2{animation-duration:300ms;animation-name:fb_bounce_in_v2;transition-timing-function:ease-in}.fb_customer_chat_bounce_out_v2{animation-duration:300ms;animation-name:fb_bounce_out_v2;transition-timing-function:ease-in}.fb_customer_chat_bounce_in_v2_mobile_chat_started{animation-duration:300ms;animation-name:fb_bounce_in_v2_mobile_chat_started;transition-timing-function:ease-in}.fb_customer_chat_bounce_out_v2_mobile_chat_started{animation-duration:300ms;animation-name:fb_bounce_out_v2_mobile_chat_started;transition-timing-function:ease-in}.fb_customer_chat_bubble_pop_in{animation-duration:250ms;animation-name:fb_customer_chat_bubble_bounce_in_animation}.fb_customer_chat_bubble_animated_no_badge{box-shadow:0 3px 12px rgba(0, 0, 0, .15);transition:box-shadow 150ms linear}.fb_customer_chat_bubble_animated_no_badge:hover{box-shadow:0 5px 24px rgba(0, 0, 0, .3)}.fb_customer_chat_bubble_animated_with_badge{box-shadow:-5px 4px 14px rgba(0, 0, 0, .15);transition:box-shadow 150ms linear}.fb_customer_chat_bubble_animated_with_badge:hover{box-shadow:-5px 8px 24px rgba(0, 0, 0, .2)}.fb_invisible_flow{display:inherit;height:0;overflow-x:hidden;width:0}.fb_mobile_overlay_active{background-color:#fff;height:100%;overflow:hidden;position:fixed;visibility:hidden;width:100%}@keyframes fb_bounce_in_v1{0%{opacity:0;transform:scale(.8, .8);transform-origin:bottom right}80%{opacity:.8;transform:scale(1.03, 1.03)}100%{opacity:1;transform:scale(1, 1)}}@keyframes fb_bounce_in_v2{0%{opacity:0;transform:scale(0, 0);transform-origin:bottom right}50%{transform:scale(1.03, 1.03);transform-origin:bottom right}100%{opacity:1;transform:scale(1, 1);transform-origin:bottom right}}@keyframes fb_bounce_in_v2_mobile_chat_started{0%{opacity:0;top:20px}100%{opacity:1;top:0}}@keyframes fb_bounce_out_v1{from{opacity:1}to{opacity:0}}@keyframes fb_bounce_out_v2{0%{opacity:1;transform:scale(1, 1);transform-origin:bottom right}100%{opacity:0;transform:scale(0, 0);transform-origin:bottom right}}@keyframes fb_bounce_out_v2_mobile_chat_started{0%{opacity:1;top:0}100%{opacity:0;top:20px}}@keyframes fb_customer_chat_bubble_bounce_in_animation{0%{bottom:6pt;opacity:0;transform:scale(0, 0);transform-origin:center}70%{bottom:18pt;opacity:1;transform:scale(1.2, 1.2)}100%{transform:scale(1, 1)}}</style></head>
	<body cz-shortcut-listen="true">
		
		<div id="container">
			
			<div id="main" role="main">
			<header>
				<a href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/qatar2022/index_test.php?idcompetition=<?php echo $idcompetitions; ?>" class="logo"></a>
				<?php include('top_menu_mobile.php'); ?>
			</header>
			<div class="rankHolder">
				<h1 class="rankingTitle">MES PRONOSTICS</h1>
			</div>
			<ul class="subNavigation">
				<li><a role="link" class="active" style="font-size : 1em;line-height: 20px;"><?php echo utf8_encode($nom_competition); ?></a></li>
			</ul>
			<?php if(isset($message)) { ?>
			<div class="success postBetSuccess">
				<?php echo $message;	?>
			</div>
			<?php } ?>

			
<h3 class="betTitle"><!--Journée du <?php echo translate_day(strftime("%A")) . " " . strftime("%d") . " " . translate_mois(strftime("%B")) . " " . strftime("%Y") ; ?>-->Rencontre à venir</h3><br />
<!--<a href="" style="text-align: center;float: none;color: white;position: relative;left: 26%;">Changer le mode de Pronostic</a> <br /> <br />-->

<form action="" method="post" id="bet_form">
<?php 
if(count($rencontres_nc) > 0) {
foreach($rencontres_nc as $r) { ?>
<div class="mainBet" id="<?php echo $r->nom_equipe1 . $r->nom_equipe2; ?>">
	<div class="clubName">
		<span><img src="<?php echo $r->flag1; ?>" alt=""></span>
		<p><?php echo utf8_encode($r->nom_equipe1); ?></p>
	</div>
	<div class="clubName">
		<span><img src="<?php echo $r->flag2; ?>" alt=""></span>
		<p><?php echo utf8_encode($r->nom_equipe2); ?></p>
	</div>
	<div class="predictWinner prediction">
		<span class="predictionDefault selectScore red">
			<select name="prono_score_eq1_<?php echo $r->equipe_id1; ?>_<?php echo $r->id; ?>" tabindex="1" id="head_match1" autocomplete="off">
				<?php
				$i = 0;
				for($i = 0; $i <= 10; $i++) { 
					if($r->prono_score_eq1) 
						$sc1 = $r->prono_score_eq1; 
					else  
						$sc1 = 0;
					if($sc1 == $i) $slc = 'selected="selected"'; else $slc = '';
					?>
					<option value="<?php echo $i; ?>" <?php echo $slc ?>><?php echo $i; ?></option>
				<?php } ?>
			</select>
		</span>
		<span class="predictionDefault selectScore red">
			<select name="prono_score_eq2_<?php echo $r->equipe_id2; ?>_<?php echo $r->id; ?>" tabindex="1" id="head_match2" autocomplete="off">
				<?php
				$i = 0;
				for($i = 0; $i <= 10; $i++) { 
					if($r->prono_score_eq2) 
						$sc1 = $r->prono_score_eq2; 
					else  
						$sc1 = 0;
					if($sc1 == $i) $slc = 'selected="selected"'; else $slc = '';
					?>
					<option value="<?php echo $i; ?>" <?php echo $slc ?>><?php echo $i; ?></option>
				<?php } ?>
			</select>
		</span>
		<p class="result"><b><?php /*echo $r->jour;*/ ?><!--.--> <?php echo $r->date; ?> - <?php echo $r->heure; ?> <!--au <?php echo utf8_encode($r->nom_stade); ?>--></b></p>
		<input type="hidden" name="<?php echo $r->id; ?>" value="<?php echo $r->date_heure; ?>">
	</div>
	<hr />
	<p class="result"><b>Timing ouverture du score</b>
	<span class="predictionDefault selectScore red" style="text-align: center;display: block;position: relative;margin:auto;">
			<select name="score_min_<?php echo $r->id; ?>" tabindex="1" id="score_min_<?php echo $r->id; ?>" style="font-size: 0.75em;">
					<option value="0">-</option>
					<option value="0-15" <?php if($r->score_min == '0-15') echo 'selected="selected"'; ?> >0 - 15</option>
					<option value="15-30" <?php if($r->score_min == '15-30') echo 'selected="selected"'; ?> >15 - 30</option>
					<option value="30-45" <?php if($r->score_min == '30-45') echo 'selected="selected"'; ?> >30 - 45</option>
					<option value="45-60" <?php if($r->score_min == '45-60') echo 'selected="selected"'; ?> >45 - 60</option>
					<option value="60-75" <?php if($r->score_min == '60-75') echo 'selected="selected"'; ?> >60 - 75</option>
					<option value="75-90" <?php if($r->score_min == '75-90') echo 'selected="selected"'; ?> >75 - 90</option>
			
			</select>
		</span></p>
		<hr />
		<p class="result"><b>Ouverture du score par</b></p>
	<span class="predictionDefault selectScore red" style="display: block;margin: auto;border: none;position: relative;border-style: none; width: 75%;background: none;line-height: 1.5;box-shadow: none;font-size:1em">
		<?php if($r->score_ouverture == $r->equipe_id1) { ?>
			<input type="radio" name="score_ouverture_<?php echo $r->id; ?>" value="<?php echo $r->equipe_id1; ?>" checked="checked" /><?php echo utf8_encode($r->nom_equipe1); ?>
			<br />
			<input type="radio" name="score_ouverture_<?php echo $r->id; ?>" value="<?php echo $r->equipe_id2; ?>" /><?php echo utf8_encode($r->nom_equipe2); ?>
			<br />
			<input type="radio" name="score_ouverture_<?php echo $r->id; ?>" value="" />Aucun des 2
		<?php } elseif($r->score_ouverture == $r->equipe_id2) { ?>
			<input type="radio" name="score_ouverture_<?php echo $r->id; ?>" value="<?php echo $r->equipe_id1; ?>" /><?php echo utf8_encode($r->nom_equipe1); ?>
			<br />
			<input type="radio" name="score_ouverture_<?php echo $r->id; ?>" value="<?php echo $r->equipe_id2; ?>" checked="checked" /><?php echo utf8_encode($r->nom_equipe2); ?>
			<br />
			<input type="radio" name="score_ouverture_<?php echo $r->id; ?>" value="" />Aucun des 2
		<?php } else { ?>
		<input type="radio" name="score_ouverture_<?php echo $r->id; ?>" value="<?php echo $r->equipe_id1; ?>" /><?php echo utf8_encode($r->nom_equipe1); ?>
			<br />
			<input type="radio" name="score_ouverture_<?php echo $r->id; ?>" value="<?php echo $r->equipe_id2; ?>" /><?php echo utf8_encode($r->nom_equipe2); ?>
			<br />
			<input type="radio" name="score_ouverture_<?php echo $r->id; ?>" value="" checked="checked" />Aucun des 2
		<?php } ?>
	</span>
</div>
<?php } ?>
<p class="takeAction">
	<a href="#" style="width:62%;overflow-wrap:break-word;line-height:1.3em" class="btnBet red valider_btn" onclick="Bet.validate(); return false;"><i>h</i>Enregistrer mes pronostics</a>
</p>
<?php } else { ?>
<h3 class="betTitle" style="text-transform: none;">Il n'y au aucune rencontre à pronostiquer pour le moment.</h3>
<?php } ?>

<input type="hidden" name="action" value="save">
	<input type="hidden" name="id" value="<?php echo $id; ?>">
</form>

<!--
<div class="success">
    Il n’y a pas de matchs à pronostiquer<br> cette semaine.
</div>-->

<script>

var Bet = {

    share: function(n_round)
    {
        var is_uiwebview = /(iPhone|iPod|iPad).*AppleWebKit(?!.*Safari)/i.test(navigator.userAgent);
        var isNativeApp  = false;

        if(navigator.userAgent.match(/FB/) != null)
        {
            if(navigator.userAgent.match(/Chrome/i) != null)
                isNativeApp =   true;
        }

        var userAgent = window.navigator.userAgent.toLowerCase();

        var standalone  =   window.navigator.standalone,
            safari      =   /safari/i.test(userAgent),
            fb          =   /fb/i.test(userAgent),
            ios         =   /iphone|ipod|ipad/i.test(userAgent);

        //Secondary Ios webview check
        var is_ioswebview   =   false;

        if(ios && ! standalone && ! safari && ! fb)
            is_ioswebview   =   true;

        if((is_ioswebview || isNativeApp || Env.core.is_webview_android)) {
            var link = Env.fb.callbackUrl + '/og/bet/?n_round=' + n_round;
            var url = Env.fb.callbackUrl + '/game/bet/';

            window.open('https://www.facebook.com/dialog/feed?app_id=' + Env.fb.appId + '&display=touch&link=' + encodeURIComponent(link) + '&redirect_uri=' + encodeURIComponent(url));
        }
        else {
            FB.ui({
                method: 'feed',
                app_id: Env.fb.appId,
                link: Env.fb.callbackUrl + '/og/bet/?n_round=' + n_round,
                redirect_uri: Env.fb.callbackUrl + '/game/bet/'
            });
        }
    },

    validate: function()
    {
        if($('#form_dialog').length > 0)
        {
            $('#form_dialog').dialog('open');
            return false;
        }

        /* OG here! */

        Bet.submit();
    },

    submit: function()
    {
        $('#bet_form').submit();
    },

    selectScore: function(id_match, score)
    {
        $('#score_1_' + id_match).removeClass('active');
        $('#score_2_' + id_match).removeClass('active');
        $('#score_3_' + id_match).removeClass('active');

        $('#score_' + score + '_' + id_match).addClass('active');
        $('#input_' + id_match).val(score);
    }
}

</script>
</div>
<?php if(!isset($_SESSION['code'])) { ?>
<script type="text/javascript">
 window.fbAsyncInit = function() {
    FB.init({
      appId      : '152343107105833',
      xfbml      : true,
      version    : 'v12.0'
    });
    FB.AppEvents.logPageView();
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "https://connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
   
</script>
<?php } ?>
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
		<?php if(!isset($_SESSION['code'])) { ?>
		$(document).on('fbready', function() {
			$('#btn_logout').off('click').on('click', function(e) {
				e.preventDefault();
				FB.logout(function(response) {
				   // Person is now logged out
				   window.location.href='https://<?php echo $_SERVER['SERVER_NAME']; ?>/qatar2022/index_mobile3.php?action=deconnexion';
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
			 window.location.href='https://<?php echo $_SERVER['SERVER_NAME']; ?>/qatar2022/index_mobile3.php?action=deconnexion';
		});
		<?php } ?>
	});
	</script>
	
	<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-7YRDS0SQ0P"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-7YRDS0SQ0P');
</script>
</body></html>