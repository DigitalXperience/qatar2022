<?php 
header("Content-Type: text/html; charset=UTF-8");
ini_set('display_errors',1); 
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
	
	header('Location: index_mobile3.php');
	die();	
}

// database access
global $mysqli;

// Traces -- On enregistre les traces du passage de l'utilisateur pour un audits
$date = date('Y-m-d H:i:s');
$sql9 = "INSERT INTO traces VALUES(NULL, 'Affiche page classement de ".$_SESSION['name']." id: ".$_SESSION['id']."', '".$date."')";
$mysqli->query($sql9);
// Traces --
	
$params = null;

// Les paramètres  admin -- Ok
$query = "SELECT label, valeur FROM admin_config";
   
if ($result = $mysqli->query($query)) { 
   while ($obj = mysqli_fetch_object($result)){
		$params[$obj->label] = $obj->valeur;
   }
   $result->close();
}

if(!isset($_SESSION['code'])) {
	/* Update la derniere présence sur l'application */
	$sql = "UPDATE `utilisateurs` SET `last_login` = '".date("Y-m-d H:i:s")."' WHERE id = '" . $_SESSION['id'] . "' LIMIT 1; ";
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
	$sql = "UPDATE `utilisateurs` SET `last_login` = '".date("Y-m-d H:i:s")."' WHERE id = '" . $_SESSION['id'] . "' LIMIT 1; ";
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

//Le nombre de participants -- Ok
if ($result = $mysqli->query("
	SELECT 
		u.id, MAX(pr.id), pr.rencontre_id, u.oauth_uid, u.oauth_provider, u.picture_url, u.nom, COALESCE(SUM(pr.pts_obtenus), 0) as pts 
	FROM 
		`utilisateurs` u
	LEFT JOIN 
		pronostics pr ON u.id = pr.utilisateur_id 
	LEFT JOIN 
		rencontres r ON r.id = pr.rencontre_id 
	WHERE 
		r.id_competition = $idcompetitions 
	GROUP BY 
		u.`id` 
	ORDER BY 
		pts DESC, 
		pr.id ASC, 
		pr.rencontre_id DESC, 
		pr.dateheure DESC")) 
		{
	$participants = array(); 
	$i=1;
	$nb_participants = $result->num_rows;
	while ($obj = mysqli_fetch_object($result)){
		$participants[$obj->id] = array('rank' => $i, 'pts' => $obj->pts, 'id_fb' => $obj->oauth_uid, 'nom' => $obj->nom, 'provider' => $obj->oauth_provider);
		$i++;
	}
	// echo "<pre>"; 
	// var_dump($participants);
	
	//$participants = array_reverse($participants);
	
	// var_dump($participants); 
	// die;
	
	//$premier = array_pop($participants);
    /* determine number of rows result set */
    
    /* close result set */
    $result->close();
}

//Le nombre de points de l'utilisateur -- Ok
$sql = "SELECT pr.`utilisateur_id`, pr.`score_eq1`, pr.`score_eq2`, pr.`vainqueur_id`, pr.`dateheure`, SUM(pr.`pts_obtenus`) AS pts 
			FROM `pronostics` pr 
			LEFT JOIN rencontres r ON r.id = pr.rencontre_id 
			WHERE r.id_competition = $idcompetitions AND pr.`pts_obtenus` IS NOT NULL 
			AND pr.`utilisateur_id` = $id  
			GROUP BY pr.`utilisateur_id` ";
	   //var_dump($query);
	   //die;
if ($result = $mysqli->query($sql)) {
	while ($obj = mysqli_fetch_object($result)){
		$pts = $obj->pts;
	}
    /* close result set */
    $result->close();
}
$date = date('Y-m-d H:i:s');
// Les rencontres déjà pronostiquées de l'utilisateur et les scores de ces rencontres
$query = "SELECT p.`score_eq1` as prono_score_eq1, p.`score_eq2` as prono_score_eq2, p.`vainqueur_id` as prono_vainqueur, r.`score_eq1`, r.`score_eq2`,  
			case WHEN r.score_eq1 > r.score_eq2 THEN r.equipe_id1 WHEN r.score_eq2 > r.score_eq1 THEN r.equipe_id2 ELSE NULL END as vainqueur_id 
			FROM `pronostics` p 
			LEFT JOIN `rencontres` r ON r.id = p.rencontre_id 
			WHERE r.date_heure < '$date' AND p.utilisateur_id = $id AND r.id_competition = $idcompetitions";
   //echo "<pre>"; var_dump($query); die;
$rencontres_pronostiquees = array();
if ($result = mysqli_query($mysqli, $query)) { 
	while ($obj = mysqli_fetch_object($result)){
		$rencontres_pronostiquees[] = $obj;
	}
   $result->close();
}

if(count($rencontres_pronostiquees) == 0) {
	$url = "https://www.33export-foot.com/can2022/index_pronostics_mobile3.php?idcompetition=" . $idcompetitions;
	
	header("Location: $url");
	
	die();
}

// Les points découlant des pronostics déjà effectués
$points = 0;
if(count($rencontres_pronostiquees) > 0) {
	foreach($rencontres_pronostiquees as $res) {
		if($res->prono_score_eq1) { // s'il a pronostiqué les scores de la rencontre
			if($res->prono_score_eq1 == $res->score_eq1 && $res->prono_score_eq2 == $res->score_eq2)
				$points = $points + $params['pt_prono_score'];
		} else {
			if($res->vainqueur_id == $res->prono_vainqueur)
				$points = $points + $params['pt_prono_issue'];
		}
	}
}

// Classement des utilisateurs de l'application
$query = "SELECT 
				u.id, MAX(pr.id), pr.rencontre_id, u.oauth_uid, u.oauth_provider, u.picture_url, u.nom, COALESCE(SUM(pr.pts_obtenus), 0) as pts 
			FROM 
				`utilisateurs` u 
			LEFT JOIN 
				pronostics pr ON u.id = pr.utilisateur_id 
			LEFT JOIN 
				rencontres r ON r.id = pr.rencontre_id 
			WHERE 
				r.id_competition = $idcompetitions 
			GROUP BY 
				u.`id` 
			ORDER BY 
				pts DESC, 
				pr.id ASC, 
				pr.rencontre_id DESC, 
				pr.dateheure DESC";

if ($result = mysqli_query($mysqli, $query)) {  
   while ($obj = mysqli_fetch_object($result)){
		$utilisateurs[] = $obj;
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
		
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery_lazyload/1.9.7/jquery.lazyload.js"></script>
		<script type="text/javascript" charset="utf-8">
		$(window).ready(function() {
			$("img.lazy").lazyload({
				effect : "fadeIn"
			});
		});
		</script>
		<!--<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>-->
		<script src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/js/modernizr-2.6.2.min.js?2"></script>
		<script src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/js/ka.krds.js?v=1502446663"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery_lazyload/1.9.7/jquery.lazyload.js"></script>
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
		<link href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/css/h5bp.css" rel="stylesheet" media="all">
		<link href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/css/wc_pronostics_mobile_style.css?v=1502446663" rel="stylesheet">
		<script type='application/javascript' src='https://<?php echo $_SERVER['SERVER_NAME']; ?>/js/fastclick.js'></script>
		<link href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/css/retina.css?v=1502446663" rel="stylesheet" media="all and (-webkit-min-device-pixel-ratio:2)">
	<style>@media print {#ghostery-purple-box {display:none !important}}</style><link type="text/css" rel="stylesheet" href="chrome-extension://pioclpoplcdbaefihamjohnefbikjilc/content.css"><style type="text/css">.fb_hidden{position:absolute;top:-10000px;z-index:10001}.fb_reposition{overflow:hidden;position:relative}.fb_invisible{display:none}.fb_reset{background:none;border:0;border-spacing:0;color:#000;cursor:auto;direction:ltr;font-family:"lucida grande", tahoma, verdana, arial, sans-serif;font-size:11px;font-style:normal;font-variant:normal;font-weight:normal;letter-spacing:normal;line-height:1;margin:0;overflow:visible;padding:0;text-align:left;text-decoration:none;text-indent:0;text-shadow:none;text-transform:none;visibility:visible;white-space:normal;word-spacing:normal}.fb_reset>div{overflow:hidden}.fb_link img{border:none}@keyframes fb_transform{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:scale(1)}}.fb_animate{animation:fb_transform .3s forwards}
.fb_dialog{background:rgba(82, 82, 82, .7);position:absolute;top:-10000px;z-index:10001}.fb_reset .fb_dialog_legacy{overflow:visible}.fb_dialog_advanced{padding:10px;border-radius:8px}.fb_dialog_content{background:#fff;color:#333}.fb_dialog_close_icon{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/yq/r/IE9JII6Z1Ys.png) no-repeat scroll 0 0 transparent;cursor:pointer;display:block;height:15px;position:absolute;right:18px;top:17px;width:15px}.fb_dialog_mobile .fb_dialog_close_icon{top:5px;left:5px;right:auto}.fb_dialog_padding{background-color:transparent;position:absolute;width:1px;z-index:-1}.fb_dialog_close_icon:hover{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/yq/r/IE9JII6Z1Ys.png) no-repeat scroll 0 -15px transparent}.fb_dialog_close_icon:active{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/yq/r/IE9JII6Z1Ys.png) no-repeat scroll 0 -30px transparent}.fb_dialog_loader{background-color:#f6f7f9;border:1px solid #606060;font-size:24px;padding:20px}.fb_dialog_top_left,.fb_dialog_top_right,.fb_dialog_bottom_left,.fb_dialog_bottom_right{height:10px;width:10px;overflow:hidden;position:absolute}.fb_dialog_top_left{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/ye/r/8YeTNIlTZjm.png) no-repeat 0 0;left:-10px;top:-10px}.fb_dialog_top_right{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/ye/r/8YeTNIlTZjm.png) no-repeat 0 -10px;right:-10px;top:-10px}.fb_dialog_bottom_left{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/ye/r/8YeTNIlTZjm.png) no-repeat 0 -20px;bottom:-10px;left:-10px}.fb_dialog_bottom_right{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/ye/r/8YeTNIlTZjm.png) no-repeat 0 -30px;right:-10px;bottom:-10px}.fb_dialog_vert_left,.fb_dialog_vert_right,.fb_dialog_horiz_top,.fb_dialog_horiz_bottom{position:absolute;background:#525252;filter:alpha(opacity=70);opacity:.7}.fb_dialog_vert_left,.fb_dialog_vert_right{width:10px;height:100%}.fb_dialog_vert_left{margin-left:-10px}.fb_dialog_vert_right{right:0;margin-right:-10px}.fb_dialog_horiz_top,.fb_dialog_horiz_bottom{width:100%;height:10px}.fb_dialog_horiz_top{margin-top:-10px}.fb_dialog_horiz_bottom{bottom:0;margin-bottom:-10px}.fb_dialog_iframe{line-height:0}.fb_dialog_content .dialog_title{background:#6d84b4;border:1px solid #365899;color:#fff;font-size:14px;font-weight:bold;margin:0}.fb_dialog_content .dialog_title>span{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/yd/r/Cou7n-nqK52.gif) no-repeat 5px 50%;float:left;padding:5px 0 7px 26px}body.fb_hidden{-webkit-transform:none;height:100%;margin:0;overflow:visible;position:absolute;top:-10000px;left:0;width:100%}.fb_dialog.fb_dialog_mobile.loading{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/ya/r/3rhSv5V8j3o.gif) white no-repeat 50% 50%;min-height:100%;min-width:100%;overflow:hidden;position:absolute;top:0;z-index:10001}.fb_dialog.fb_dialog_mobile.loading.centered{width:auto;height:auto;min-height:initial;min-width:initial;background:none}.fb_dialog.fb_dialog_mobile.loading.centered #fb_dialog_loader_spinner{width:100%}.fb_dialog.fb_dialog_mobile.loading.centered .fb_dialog_content{background:none}.loading.centered #fb_dialog_loader_close{color:#fff;display:block;padding-top:20px;clear:both;font-size:18px}#fb-root #fb_dialog_ipad_overlay{background:rgba(0, 0, 0, .45);position:absolute;bottom:0;left:0;right:0;top:0;width:100%;min-height:100%;z-index:10000}#fb-root #fb_dialog_ipad_overlay.hidden{display:none}.fb_dialog.fb_dialog_mobile.loading iframe{visibility:hidden}.fb_dialog_content .dialog_header{-webkit-box-shadow:white 0 1px 1px -1px inset;background:-webkit-gradient(linear, 0% 0%, 0% 100%, from(#738ABA), to(#2C4987));border-bottom:1px solid;border-color:#1d4088;color:#fff;font:14px Helvetica, sans-serif;font-weight:bold;text-overflow:ellipsis;text-shadow:rgba(0, 30, 84, .296875) 0 -1px 0;vertical-align:middle;white-space:nowrap}.fb_dialog_content .dialog_header table{-webkit-font-smoothing:subpixel-antialiased;height:43px;width:100%}.fb_dialog_content .dialog_header td.header_left{font-size:12px;padding-left:5px;vertical-align:middle;width:60px}.fb_dialog_content .dialog_header td.header_right{font-size:12px;padding-right:5px;vertical-align:middle;width:60px}.fb_dialog_content .touchable_button{background:-webkit-gradient(linear, 0% 0%, 0% 100%, from(#4966A6), color-stop(.5, #355492), to(#2A4887));border:1px solid #29487d;-webkit-background-clip:padding-box;-webkit-border-radius:3px;-webkit-box-shadow:rgba(0, 0, 0, .117188) 0 1px 1px inset, rgba(255, 255, 255, .167969) 0 1px 0;display:inline-block;margin-top:3px;max-width:85px;line-height:18px;padding:4px 12px;position:relative}.fb_dialog_content .dialog_header .touchable_button input{border:none;background:none;color:#fff;font:12px Helvetica, sans-serif;font-weight:bold;margin:2px -12px;padding:2px 6px 3px 6px;text-shadow:rgba(0, 30, 84, .296875) 0 -1px 0}.fb_dialog_content .dialog_header .header_center{color:#fff;font-size:16px;font-weight:bold;line-height:18px;text-align:center;vertical-align:middle}.fb_dialog_content .dialog_content{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/y9/r/jKEcVPZFk-2.gif) no-repeat 50% 50%;border:1px solid #555;border-bottom:0;border-top:0;height:150px}.fb_dialog_content .dialog_footer{background:#f6f7f9;border:1px solid #555;border-top-color:#ccc;height:40px}#fb_dialog_loader_close{float:left}.fb_dialog.fb_dialog_mobile .fb_dialog_close_button{text-shadow:rgba(0, 30, 84, .296875) 0 -1px 0}.fb_dialog.fb_dialog_mobile .fb_dialog_close_icon{visibility:hidden}#fb_dialog_loader_spinner{animation:rotateSpinner 1.2s linear infinite;background-color:transparent;background-image:url(https://static.xx.fbcdn.net/rsrc.php/v3/yD/r/t-wz8gw1xG1.png);background-repeat:no-repeat;background-position:50% 50%;height:24px;width:24px}@keyframes rotateSpinner{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
.fb_iframe_widget{display:inline-block;position:relative}.fb_iframe_widget span{display:inline-block;position:relative;text-align:justify}.fb_iframe_widget iframe{position:absolute}.fb_iframe_widget_fluid_desktop,.fb_iframe_widget_fluid_desktop span,.fb_iframe_widget_fluid_desktop iframe{max-width:100%}.fb_iframe_widget_fluid_desktop iframe{min-width:220px;position:relative}.fb_iframe_widget_lift{z-index:1}.fb_hide_iframes iframe{position:relative;left:-10000px}.fb_iframe_widget_loader{position:relative;display:inline-block}.fb_iframe_widget_fluid{display:inline}.fb_iframe_widget_fluid span{width:100%}.fb_iframe_widget_loader iframe{min-height:32px;z-index:2;zoom:1}.fb_iframe_widget_loader .FB_Loader{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/y9/r/jKEcVPZFk-2.gif) no-repeat;height:32px;width:32px;margin-left:-16px;position:absolute;left:50%;z-index:4}
.fb_customer_chat_bounce_in_v1{animation-duration:250ms;animation-name:fb_bounce_in_v1}.fb_customer_chat_bounce_out_v1{animation-duration:250ms;animation-name:fb_bounce_out_v1}.fb_customer_chat_bounce_in_v2{animation-duration:300ms;animation-name:fb_bounce_in_v2;transition-timing-function:ease-in}.fb_customer_chat_bounce_out_v2{animation-duration:300ms;animation-name:fb_bounce_out_v2;transition-timing-function:ease-in}.fb_customer_chat_bounce_in_v2_mobile_chat_started{animation-duration:300ms;animation-name:fb_bounce_in_v2_mobile_chat_started;transition-timing-function:ease-in}.fb_customer_chat_bounce_out_v2_mobile_chat_started{animation-duration:300ms;animation-name:fb_bounce_out_v2_mobile_chat_started;transition-timing-function:ease-in}.fb_customer_chat_bubble_pop_in{animation-duration:250ms;animation-name:fb_customer_chat_bubble_bounce_in_animation}.fb_customer_chat_bubble_animated_no_badge{box-shadow:0 3px 12px rgba(0, 0, 0, .15);transition:box-shadow 150ms linear}.fb_customer_chat_bubble_animated_no_badge:hover{box-shadow:0 5px 24px rgba(0, 0, 0, .3)}.fb_customer_chat_bubble_animated_with_badge{box-shadow:-5px 4px 14px rgba(0, 0, 0, .15);transition:box-shadow 150ms linear}.fb_customer_chat_bubble_animated_with_badge:hover{box-shadow:-5px 8px 24px rgba(0, 0, 0, .2)}.fb_invisible_flow{display:inherit;height:0;overflow-x:hidden;width:0}.fb_mobile_overlay_active{background-color:#fff;height:100%;overflow:hidden;position:fixed;visibility:hidden;width:100%}@keyframes fb_bounce_in_v1{0%{opacity:0;transform:scale(.8, .8);transform-origin:bottom right}80%{opacity:.8;transform:scale(1.03, 1.03)}100%{opacity:1;transform:scale(1, 1)}}@keyframes fb_bounce_in_v2{0%{opacity:0;transform:scale(0, 0);transform-origin:bottom right}50%{transform:scale(1.03, 1.03);transform-origin:bottom right}100%{opacity:1;transform:scale(1, 1);transform-origin:bottom right}}@keyframes fb_bounce_in_v2_mobile_chat_started{0%{opacity:0;top:20px}100%{opacity:1;top:0}}@keyframes fb_bounce_out_v1{from{opacity:1}to{opacity:0}}@keyframes fb_bounce_out_v2{0%{opacity:1;transform:scale(1, 1);transform-origin:bottom right}100%{opacity:0;transform:scale(0, 0);transform-origin:bottom right}}@keyframes fb_bounce_out_v2_mobile_chat_started{0%{opacity:1;top:0}100%{opacity:0;top:20px}}@keyframes fb_customer_chat_bubble_bounce_in_animation{0%{bottom:6pt;opacity:0;transform:scale(0, 0);transform-origin:center}70%{bottom:18pt;opacity:1;transform:scale(1.2, 1.2)}100%{transform:scale(1, 1)}}</style></head>
	<body cz-shortcut-listen="true">
		<div id="fb-root" class=" fb_reset"><div style="position: absolute; top: -10000px; height: 0px; width: 0px;"><div><iframe name="fb_xdm_frame_https" frameborder="0" allowtransparency="true" allowfullscreen="true" scrolling="no" allow="encrypted-media" id="fb_xdm_frame_https" aria-hidden="true" title="Facebook Cross Domain Communication Frame" tabindex="-1" src="https://staticxx.facebook.com/connect/xd_arbiter/r/mAiQUwlReIP.js?version=42#channel=f1b9b4def4d067c&amp;origin=https%3A%2F%2Fm.pronosfoot.mycanal.fr" style="border: none;"></iframe></div></div><div style="position: absolute; top: -10000px; height: 0px; width: 0px;"><div></div></div></div>
		<form action="" method="post" id="fb-form"><input name="authResponse" value="{&quot;accessToken&quot;:&quot;EAAAAHN9UfZAMBANXUvQLOgZAVDmTuQsQkUfdzWoKq6sI5LYNZCPZC1my1JyRzWl9vMEcyLcVfcSjhfcU0zSO7yIME5cskOmotkoXUFZBFuZCZB2ZAYLavRIaSN1ca9BZAXtkJi1OwSwFfTgCA4t75xek0TTcGZCaPhyNZAa3nPeZCoPKPSqihd9HQCSwfrnf58ZAEKygN5ExZBwQS5qAZDZD&quot;,&quot;userID&quot;:&quot;10155870072269961&quot;,&quot;expiresIn&quot;:5468,&quot;signedRequest&quot;:&quot;zwBGLX-ms1kaLaqqvVdfjTQkGM1ZBsuTg_zPGMwVLaI.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImNvZGUiOiJBUUNEODN3R0JLRkh4TFJtU0lHQ09BM2xKMFRWQ2FSR0VkNkxvOUJUVWFKRFhreUJET0FCQklKOUpvd3lVYWRQaHBOQ3RnZUM1bWFad0lwdTE1bE5ZU0FOb3NXaVU1Sjhja0psSm8wcWNPazZrc0FXVFFaYU9fQmNQR2VaNzRYTzNENWR5MjRta0FZNGh5NFpVUDFSME5GMWFfNHRsUXFSSlhPbWlpM1YxaVdOUTBNQ0NsRGVEUlFCMUhGM09XQXFDbFFuNjZveGgzemtETmczMFQza3Q2bWhUcE0xc2lUakxVVGkwd1RHR1RTYm1FSC05THVGbTdlOTVuWkFFaXg5bngxUF8xU2Z3WjV0d2NDNW5HMEY2dlM5S0c3OE93NllqZC1pQUM1SEZhTHFIODU0OVJjNzdfSVBQeHZNZUluUDJZZlFrUVJlVy0zVmpUemMzcm14M2NRVCIsImlzc3VlZF9hdCI6MTUyOTAwNDUzMiwidXNlcl9pZCI6IjEwMTU1ODcwMDcyMjY5OTYxIn0&quot;,&quot;reauthorize_required_in&quot;:5491795}" type="hidden"></form>

		<div id="container">
			<div id="main" role="main">
			<header>
				<a href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/can2022/index_test.php?idcompetition=<?php echo $idcompetitions; ?>" class="logo"></a>
				<?php include('top_menu_mobile.php'); ?>
			</header><div class="rankHolder">
	<h1 class="rankingTitle">classement</h1>
</div>
<ul class="subNavigation">
<li><a href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/can2022/index_classement_mobile_hebdo.php?list=all&idcompetition=<?php echo $idcompetitions; ?>" class="">Hebdo</a></li>
<li><a href="#" class="active">Général</a></li>
</ul>

<div class="success">
	Classement en fonction du nombre de points durant <b>toute la compétition</b>
</div>

<div class="contentWrapper" style="padding-bottom: 22px;">
	<style type="text/css">
	.userRankInfo {
			border-bottom: 3px solid #88B568;
	}
	.userRankDetails {
		background: url("https://<?php echo $_SERVER['SERVER_NAME']; ?>/img/bg_profile_border.png") no-repeat right center;
	}
	.userRankDetails a {
		margin-left: 0;
	}
</style>

<div class="userRankInfo">
	<div class="userRankDetails">
		<a href="#" rel="callback">
			<?php if($oauth == '') { ?>
				<img src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/img/pp.png" alt="">
			<?php } ?>
			<?php if($oauth == 'facebook') { ?>
				<img src="//graph.facebook.com/<?php echo $oauth_id; ?>/picture?width=200&amp;height=200" alt="">
			<?php } ?>
			<?php if($oauth == 'google') { ?>
				<img src="<?php echo $picture_url; ?>" alt="">
			<?php } ?>
		</a>
		<p><fb:name uid="<?php echo $_SESSION['id']; ?>" useyou="false" linked="false" fb-xfbml-state="rendered"><?php echo $name; ?></fb:name></p>
		<h1><?php if(isset($participants[$id])) { echo $participants[$id]["rank"]; if($participants[$id]["rank"] == 1) { echo "<sup>er</sup>"; } else { echo "<sup>ème</sup>";} } else echo "Non classé"; ?></h1>
		<p>sur <?php echo count($utilisateurs); ?></p>
	</div>
	<h1 class="userScore">
	<?php 
		if(isset($participants[$id])) {
			if(is_null($participants[$id]["pts"])) 
				echo "0"; 
			else 
				echo $participants[$id]["pts"]; 
			echo "<p>pts</p>";
		} else {
			echo "<p>Non classé</p>";
		}
	
	
	?> 
	</h1>
</div>

		<h3 class="betTitle">Classement Général</h3>
		<ul class="rankList">
			<?php 
			$d = 1;
			foreach($utilisateurs as $user) { ?>
				<li>
				<span class="rankNo"><?php echo $d; ?></span>
				<a href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/can2022/index_myprofile_mobile.php?user=<?php echo $user->id; ?>&idcompetition=<?php echo $idcompetitions; ?>" rel="callback" class="playerProfile">
					<div class="playerName">
						<span>
							<?php if($user->oauth_provider == 'facebook') { ?>
								<img data-original="//graph.facebook.com/<?php echo $user->oauth_uid; ?>/picture?width=200&amp;height=200" class="lazy" alt="" />
							<?php } ?>
							<?php if($user->oauth_provider == 'google') { ?>
								<img data-original="<?php echo $user->picture_url; ?>" class="lazy" alt="" />
							<?php } ?>
							<?php if($user->oauth_provider == '') { ?>
								<img data-original="https://<?php echo $_SERVER['SERVER_NAME']; ?>/img/pp.png" alt="" class="lazy" />
							<?php } ?>
						</span>
						<p>				
							<fb:name uid="<?php echo $user->oauth_uid; ?>" useyou="false" linked="false" fb-xfbml-state="rendered">
							<?php echo $user->nom; ?>
							</fb:name>
						</p>
					</div>
					<h3 class="playerPoints"><?php echo $user->pts; ?> <p>pts</p></h3>
				</a>
			</li>
			<?php $d++; } ?>

		</ul>
	</div>
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
	});
</script>

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