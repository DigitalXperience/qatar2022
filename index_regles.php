<?php 
header("Content-Type: text/html; charset=UTF-8");

ini_set('display_errors',1); 
error_reporting(E_ALL);
session_start();

if(!isset($_SESSION['id'])) {
	header('Location: index_mobile3.php'); die();	
}

require_once 'includes/config.php';

date_default_timezone_set('Africa/Douala');
include_once 'includes/functions.php';

if(isset($_SESSION['idcompetitions'])) {
	$idcompetitions = $_SESSION['idcompetitions'];
} else $idcompetitions = 1;

if(isset($_GET['idcompetition'])) {
	$idcompetitions = $_GET['idcompetition'];
}

// database access
global $mysqli;

// Check connection
if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

// Traces -- On enregistre les traces du passage de l'utilisateur pour un audits
$date = date('Y-m-d H:i:s');
$sql9 = "INSERT INTO traces VALUES(NULL, 'Affiche regles du jeu de ".$_SESSION['name']." id: ".$_SESSION['id']."', '".$date."')";
$mysqli->query($sql9);
// Traces --

?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class=" js touch" xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#"><!--<![endif]--><head>
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

		<title>33 Export Prono Foot - Règlements du jeu</title>

		<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no, width=device-width, height=device-height">
		<meta property="fb:app_id" content="384046202210940">
		
		<script>
		    var Env = {
				core: {
					locale			: "fr_FR",
					hasCustomLocale	: "false",
					cb				: "1",
                    is_webview_android : "",
				},
				fb: {
					appId			: "384046202210940",
					version			: "v3.3",
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
		<link href="css/h5bp.css" rel="stylesheet" media="all">
		<link href="css/wc_pronostics_mobile_style.css?v=1502446663" rel="stylesheet">
		<script type='application/javascript' src='js/fastclick.js'></script>
		<link href="css/retina.css?v=1502446663" rel="stylesheet" media="all and (-webkit-min-device-pixel-ratio:2)">
	<style>@media print {#ghostery-purple-box {display:none !important}}</style><link type="text/css" rel="stylesheet" href="chrome-extension://pioclpoplcdbaefihamjohnefbikjilc/content.css"><style type="text/css">.fb_hidden{position:absolute;top:-10000px;z-index:10001}.fb_reposition{overflow:hidden;position:relative}.fb_invisible{display:none}.fb_reset{background:none;border:0;border-spacing:0;color:#000;cursor:auto;direction:ltr;font-family:"lucida grande", tahoma, verdana, arial, sans-serif;font-size:11px;font-style:normal;font-variant:normal;font-weight:normal;letter-spacing:normal;line-height:1;margin:0;overflow:visible;padding:0;text-align:left;text-decoration:none;text-indent:0;text-shadow:none;text-transform:none;visibility:visible;white-space:normal;word-spacing:normal}.fb_reset>div{overflow:hidden}.fb_link img{border:none}@keyframes fb_transform{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:scale(1)}}.fb_animate{animation:fb_transform .3s forwards}
.fb_dialog{background:rgba(82, 82, 82, .7);position:absolute;top:-10000px;z-index:10001}.fb_reset .fb_dialog_legacy{overflow:visible}.fb_dialog_advanced{padding:10px;border-radius:8px}.fb_dialog_content{background:#fff;color:#333}.fb_dialog_close_icon{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/yq/r/IE9JII6Z1Ys.png) no-repeat scroll 0 0 transparent;cursor:pointer;display:block;height:15px;position:absolute;right:18px;top:17px;width:15px}.fb_dialog_mobile .fb_dialog_close_icon{top:5px;left:5px;right:auto}.fb_dialog_padding{background-color:transparent;position:absolute;width:1px;z-index:-1}.fb_dialog_close_icon:hover{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/yq/r/IE9JII6Z1Ys.png) no-repeat scroll 0 -15px transparent}.fb_dialog_close_icon:active{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/yq/r/IE9JII6Z1Ys.png) no-repeat scroll 0 -30px transparent}.fb_dialog_loader{background-color:#f6f7f9;border:1px solid #606060;font-size:24px;padding:20px}.fb_dialog_top_left,.fb_dialog_top_right,.fb_dialog_bottom_left,.fb_dialog_bottom_right{height:10px;width:10px;overflow:hidden;position:absolute}.fb_dialog_top_left{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/ye/r/8YeTNIlTZjm.png) no-repeat 0 0;left:-10px;top:-10px}.fb_dialog_top_right{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/ye/r/8YeTNIlTZjm.png) no-repeat 0 -10px;right:-10px;top:-10px}.fb_dialog_bottom_left{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/ye/r/8YeTNIlTZjm.png) no-repeat 0 -20px;bottom:-10px;left:-10px}.fb_dialog_bottom_right{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/ye/r/8YeTNIlTZjm.png) no-repeat 0 -30px;right:-10px;bottom:-10px}.fb_dialog_vert_left,.fb_dialog_vert_right,.fb_dialog_horiz_top,.fb_dialog_horiz_bottom{position:absolute;background:#525252;filter:alpha(opacity=70);opacity:.7}.fb_dialog_vert_left,.fb_dialog_vert_right{width:10px;height:100%}.fb_dialog_vert_left{margin-left:-10px}.fb_dialog_vert_right{right:0;margin-right:-10px}.fb_dialog_horiz_top,.fb_dialog_horiz_bottom{width:100%;height:10px}.fb_dialog_horiz_top{margin-top:-10px}.fb_dialog_horiz_bottom{bottom:0;margin-bottom:-10px}.fb_dialog_iframe{line-height:0}.fb_dialog_content .dialog_title{background:#6d84b4;border:1px solid #365899;color:#fff;font-size:14px;font-weight:bold;margin:0}.fb_dialog_content .dialog_title>span{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/yd/r/Cou7n-nqK52.gif) no-repeat 5px 50%;float:left;padding:5px 0 7px 26px}body.fb_hidden{-webkit-transform:none;height:100%;margin:0;overflow:visible;position:absolute;top:-10000px;left:0;width:100%}.fb_dialog.fb_dialog_mobile.loading{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/ya/r/3rhSv5V8j3o.gif) white no-repeat 50% 50%;min-height:100%;min-width:100%;overflow:hidden;position:absolute;top:0;z-index:10001}.fb_dialog.fb_dialog_mobile.loading.centered{width:auto;height:auto;min-height:initial;min-width:initial;background:none}.fb_dialog.fb_dialog_mobile.loading.centered #fb_dialog_loader_spinner{width:100%}.fb_dialog.fb_dialog_mobile.loading.centered .fb_dialog_content{background:none}.loading.centered #fb_dialog_loader_close{color:#fff;display:block;padding-top:20px;clear:both;font-size:18px}#fb-root #fb_dialog_ipad_overlay{background:rgba(0, 0, 0, .45);position:absolute;bottom:0;left:0;right:0;top:0;width:100%;min-height:100%;z-index:10000}#fb-root #fb_dialog_ipad_overlay.hidden{display:none}.fb_dialog.fb_dialog_mobile.loading iframe{visibility:hidden}.fb_dialog_content .dialog_header{-webkit-box-shadow:white 0 1px 1px -1px inset;background:-webkit-gradient(linear, 0% 0%, 0% 100%, from(#738ABA), to(#2C4987));border-bottom:1px solid;border-color:#1d4088;color:#fff;font:14px Helvetica, sans-serif;font-weight:bold;text-overflow:ellipsis;text-shadow:rgba(0, 30, 84, .296875) 0 -1px 0;vertical-align:middle;white-space:nowrap}.fb_dialog_content .dialog_header table{-webkit-font-smoothing:subpixel-antialiased;height:43px;width:100%}.fb_dialog_content .dialog_header td.header_left{font-size:12px;padding-left:5px;vertical-align:middle;width:60px}.fb_dialog_content .dialog_header td.header_right{font-size:12px;padding-right:5px;vertical-align:middle;width:60px}.fb_dialog_content .touchable_button{background:-webkit-gradient(linear, 0% 0%, 0% 100%, from(#4966A6), color-stop(.5, #355492), to(#2A4887));border:1px solid #29487d;-webkit-background-clip:padding-box;-webkit-border-radius:3px;-webkit-box-shadow:rgba(0, 0, 0, .117188) 0 1px 1px inset, rgba(255, 255, 255, .167969) 0 1px 0;display:inline-block;margin-top:3px;max-width:85px;line-height:18px;padding:4px 12px;position:relative}.fb_dialog_content .dialog_header .touchable_button input{border:none;background:none;color:#fff;font:12px Helvetica, sans-serif;font-weight:bold;margin:2px -12px;padding:2px 6px 3px 6px;text-shadow:rgba(0, 30, 84, .296875) 0 -1px 0}.fb_dialog_content .dialog_header .header_center{color:#fff;font-size:16px;font-weight:bold;line-height:18px;text-align:center;vertical-align:middle}.fb_dialog_content .dialog_content{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/y9/r/jKEcVPZFk-2.gif) no-repeat 50% 50%;border:1px solid #555;border-bottom:0;border-top:0;height:150px}.fb_dialog_content .dialog_footer{background:#f6f7f9;border:1px solid #555;border-top-color:#ccc;height:40px}#fb_dialog_loader_close{float:left}.fb_dialog.fb_dialog_mobile .fb_dialog_close_button{text-shadow:rgba(0, 30, 84, .296875) 0 -1px 0}.fb_dialog.fb_dialog_mobile .fb_dialog_close_icon{visibility:hidden}#fb_dialog_loader_spinner{animation:rotateSpinner 1.2s linear infinite;background-color:transparent;background-image:url(https://static.xx.fbcdn.net/rsrc.php/v3/yD/r/t-wz8gw1xG1.png);background-repeat:no-repeat;background-position:50% 50%;height:24px;width:24px}@keyframes rotateSpinner{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
.fb_iframe_widget{display:inline-block;position:relative}.fb_iframe_widget span{display:inline-block;position:relative;text-align:justify}.fb_iframe_widget iframe{position:absolute}.fb_iframe_widget_fluid_desktop,.fb_iframe_widget_fluid_desktop span,.fb_iframe_widget_fluid_desktop iframe{max-width:100%}.fb_iframe_widget_fluid_desktop iframe{min-width:220px;position:relative}.fb_iframe_widget_lift{z-index:1}.fb_hide_iframes iframe{position:relative;left:-10000px}.fb_iframe_widget_loader{position:relative;display:inline-block}.fb_iframe_widget_fluid{display:inline}.fb_iframe_widget_fluid span{width:100%}.fb_iframe_widget_loader iframe{min-height:32px;z-index:2;zoom:1}.fb_iframe_widget_loader .FB_Loader{background:url(https://static.xx.fbcdn.net/rsrc.php/v3/y9/r/jKEcVPZFk-2.gif) no-repeat;height:32px;width:32px;margin-left:-16px;position:absolute;left:50%;z-index:4}
.fb_customer_chat_bounce_in_v1{animation-duration:250ms;animation-name:fb_bounce_in_v1}.fb_customer_chat_bounce_out_v1{animation-duration:250ms;animation-name:fb_bounce_out_v1}.fb_customer_chat_bounce_in_v2{animation-duration:300ms;animation-name:fb_bounce_in_v2;transition-timing-function:ease-in}.fb_customer_chat_bounce_out_v2{animation-duration:300ms;animation-name:fb_bounce_out_v2;transition-timing-function:ease-in}.fb_customer_chat_bounce_in_v2_mobile_chat_started{animation-duration:300ms;animation-name:fb_bounce_in_v2_mobile_chat_started;transition-timing-function:ease-in}.fb_customer_chat_bounce_out_v2_mobile_chat_started{animation-duration:300ms;animation-name:fb_bounce_out_v2_mobile_chat_started;transition-timing-function:ease-in}.fb_customer_chat_bubble_pop_in{animation-duration:250ms;animation-name:fb_customer_chat_bubble_bounce_in_animation}.fb_customer_chat_bubble_animated_no_badge{box-shadow:0 3px 12px rgba(0, 0, 0, .15);transition:box-shadow 150ms linear}.fb_customer_chat_bubble_animated_no_badge:hover{box-shadow:0 5px 24px rgba(0, 0, 0, .3)}.fb_customer_chat_bubble_animated_with_badge{box-shadow:-5px 4px 14px rgba(0, 0, 0, .15);transition:box-shadow 150ms linear}.fb_customer_chat_bubble_animated_with_badge:hover{box-shadow:-5px 8px 24px rgba(0, 0, 0, .2)}.fb_invisible_flow{display:inherit;height:0;overflow-x:hidden;width:0}.fb_mobile_overlay_active{background-color:#fff;height:100%;overflow:hidden;position:fixed;visibility:hidden;width:100%}@keyframes fb_bounce_in_v1{0%{opacity:0;transform:scale(.8, .8);transform-origin:bottom right}80%{opacity:.8;transform:scale(1.03, 1.03)}100%{opacity:1;transform:scale(1, 1)}}@keyframes fb_bounce_in_v2{0%{opacity:0;transform:scale(0, 0);transform-origin:bottom right}50%{transform:scale(1.03, 1.03);transform-origin:bottom right}100%{opacity:1;transform:scale(1, 1);transform-origin:bottom right}}@keyframes fb_bounce_in_v2_mobile_chat_started{0%{opacity:0;top:20px}100%{opacity:1;top:0}}@keyframes fb_bounce_out_v1{from{opacity:1}to{opacity:0}}@keyframes fb_bounce_out_v2{0%{opacity:1;transform:scale(1, 1);transform-origin:bottom right}100%{opacity:0;transform:scale(0, 0);transform-origin:bottom right}}@keyframes fb_bounce_out_v2_mobile_chat_started{0%{opacity:1;top:0}100%{opacity:0;top:20px}}@keyframes fb_customer_chat_bubble_bounce_in_animation{0%{bottom:6pt;opacity:0;transform:scale(0, 0);transform-origin:center}70%{bottom:18pt;opacity:1;transform:scale(1.2, 1.2)}100%{transform:scale(1, 1)}}</style></head>
<body cz-shortcut-listen="true">
	<div id="container">
		<div id="main" role="main">
			<header>
				<a href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/qatar2022/index_test.php" class="logo"></a>
				<?php include('top_menu_mobile.php'); ?>
			</header>
			<div class="rankHolder">
				<h1 class="rankingTitle">Fête du football africain</h1>
				
			</div>
			
			<ul class="subNavigation">
				<li><a role="link" class="active" style="font-size : 1em;line-height: 20px;">Veuillez lire le règlement</a></li>
			</ul>
			
			<br /><br />
			
			<div id='result' class="result" style="letter-spacing: normal;">
				<div class="img-conatiner">
						
				</div>
				<div id='regles'>
					<h3 class="betTitle"><!--Journée du Dimanche 09 Janv. 2022-->Règlement du jeu</h3>
					<div class="matchScheduleTitle">
						<h3>Challenge : Pronostics jour après jour</h3>
					</div>
<p><span style="color:yellow">1.</span> Le jeu se déroule durant toute la compétition de football. </p>
<p><span style="color:yellow">2.</span> Chaque pronostiqueur doit fournir ses pronostics pour chaque match de la compétition avant le début du premier match de la journée (jusqu’à une minute avant le début du match).</p>
<p><span style="color:yellow">3.</span> 
	Cela se fait selon les modalités suivantes :  
		
		<p>L’utilisateur doit prévoir le résultat de chacun des matches de la journée. Pour chaque match de chaque journée, chaque pronostiqueur met en jeu 14 points.</p>
		<p>Pour les Grands Matchs, le pronostiqueur doit pronostiquer le score (et non seulement l'équipe qui ouvrira le score et l'intervalle de la minute d'ouverture) et chaque pronostiqueur met en jeu 28 points.</p> 
	
</p>
<p><span style="color:yellow">4.</span> Les pronostics tiendront compte uniquement des buts marqués dans le temps des 90 minutes. Les buts marqués pendant les prolongations et les séances de tirs aux buts ne seront pas pris en compte.</p>
<p><span style="color:yellow">5.</span> En fonction de l’actualité, des lots seront distribués aux meilleurs pronostiqueurs de chaque journée. 
Les lots en jeu ainsi que le règlement seront indiqués en page Facebook de la marque <a style="background-color: white;color: #D52E3F" href="https://www.facebook.com/33ExportCameroun" target="_blank">33 Export Cameroun</a>.</p>
<p><span style="color:yellow">6.</span> Dans le but de donner une chance aux joueurs tardifs de remonter dans le classement général, des journées pourront être déclarées "Compte Double". 
Le caractère "Compte Double" de ces journées sera annoncé quelques jours avant la journée en question sur la page Facebook de 33 Export et clairement annoncé sur la page d'accueil du jeu 33 Export Foot Pronostiques.</p>
<p><span style="color:yellow">7.</span> D'autre part vous pouvez cumulez des points bonus de plusieurs façon.</p>
<p><span style="color:yellow">8.</span> Votre classement dans ce jeu sera fonction du nombre de points que vous avez obtenus, et en cas d'égalité avec d'autres participants, l'ordre d'enregistrement des pronostiques sera pris en compte.</p>
<p style="color:yellow">Bonne chance à tous !</p>
		<hr />
		<div class="matchScheduleTitle">
		
			<h1 style="font-size:0.8em;float:none;">
			C'est Parti !!!
			</h1>
			
			<a href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/qatar2022/index_pronostics_mobile3.php?idcompetition=1" rel="callback" style="font-size:5.5em" fastclick class="home">a</a>
			
			
		</div>
				</div>
				<!--<div class="btn-c">
					<button id="retake_btn" class="btn">Retake Quiz</button>
				</div>-->
			</div>

		</div>

</div>

<footer id="sticky">© 33 Export <?php echo date('Y'); ?> | <a href="#" id="btn_logout" style="display:inline;">Se déconnecter</a> | <a href="#" style="display:inline;">Mes Lots</a></footer>

<script async src="https://www.googletagmanager.com/gtag/js?id=G-7YRDS0SQ0P"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-7YRDS0SQ0P');
</script>
</body>
</html>