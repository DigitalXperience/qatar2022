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
$sql9 = "INSERT INTO traces VALUES(NULL, 'Affiche page mon profil de ".$_SESSION['name']." id: ".$_SESSION['id']."', '".$date."')";
$mysqli->query($sql9);
// Traces --
	


if(!isset($_SESSION['code'])) {
	/* Update la derniere présence sur l'application */
	$sql = "UPDATE `utilisateurs` SET `last_login` = '".date("Y-m-d H:i:s")."' WHERE id = '" . $_SESSION['id'] . "' LIMIT 1; ";
	$mysqli->query($sql);

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
	/* Update la derniere présence sur l'application */
	$sql = "UPDATE `utilisateurs` SET `last_login` = '".date("Y-m-d H:i:s")."' WHERE id = '" . $_SESSION['id'] . "' LIMIT 1; ";
	$mysqli->query($sql);

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

//Le classement de l'utilsateur et son nombre de points -- Ok
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
	 /* Libération des résultats */
    $result->free();
}
// echo "<pre>";
// var_dump($participants); die;

/* Fermeture de la connexion */
$mysqli->close();

//
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
	<style>@media print {#ghostery-purple-box {display:none !important}}</style>
	<body cz-shortcut-listen="true">
		<form action="" method="post" id="fb-form"><input name="authResponse" value="{&quot;accessToken&quot;:&quot;EAAAAHN9UfZAMBAGZBRhuUKbZC53GFtoRdg5Eb3In4ZB3EUbjYjzyejslCYy4lHYaJz4NZC0cLTIAMKZBmSDnRf9m2PZAJZApXIKnAAP0fGTsWBL2bHS2cNTgy4gHTinzghzgsA5FejLKH7OxQjHLuYmuVPV09tJKReKHaBaBFvUaR8ZC9P21soEwwQ4mFLYOfVFSHdPphl9s6XAZDZD&quot;,&quot;userID&quot;:&quot;10155870072269961&quot;,&quot;expiresIn&quot;:7196,&quot;signedRequest&quot;:&quot;SPtPV99akSyrsjZ6EuMG1emmeFb9Jtvacc3n6i149Vo.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImNvZGUiOiJBUUN3ODMtTjJ4RHk3UWI4VHQ2cEE1bmY5ODFWZG4xdE9NRUpLcl9VbjkxU2l0ODg3cWNYbWNtZXd2aTBicU5KWUloSTRvM0RHZUE2VzRXSjRTbUJqWFNqNlIxNGREaU1MQ1FJOW1FaG1NYkJFWTRfaUZZc1huOUhjOXRCTnlpSDFPMFZzQ3B0TjhoQzFNbnItWDY5UVNWOEZ2U0ZJZzZHX1BER09EZjZFcGRVLW95cUE2NEFxSHIweEdibF9ZQUhhMWU5RnAtZ1htM3llY3Z5NWFwMUtZcHA5T0dHTk5vMkdzbm9OTlZPWi1ManVwQlZOdGEweFloTFJWcGw4V1R5VFNsNFR6eC1jZTJnYU5yT3k5cm5oOFQwckhpeHZtLV91VkdsS0hhbzE5bFVXb1N6bXJVY19lT3ZzZVFqZHNBNVc0bnM5aTEzUEVWNEtpdGVualhZelEyTCIsImlzc3VlZF9hdCI6MTUyOTA0MjQwNCwidXNlcl9pZCI6IjEwMTU1ODcwMDcyMjY5OTYxIn0&quot;,&quot;reauthorize_required_in&quot;:5453923}" type="hidden"></form>

		<div id="container">
			
			<div id="main" role="main">
			<header>
				<a href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/qatar2022/index_test.php?idcompetition=<?php echo $idcompetitions; ?>" class="logo"></a>
				<?php include('top_menu_mobile.php'); ?>
			</header>
<div class="rankHolder">
	<h1 class="rankingTitle">Mon profil</h1>
</div>
<ul class="subNavigation">
<li><a href="#" class="active">Vue d’ensemble</a></li>
<!--<li><a href="https://m.pronosfoot.mycanal.fr/user/forum" class="">Forum</a></li>--></ul>
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
				  if(isset($participants[$id])) {
					$rank = $participants[$id]["rank"];
				  } else {
					  $rank = null;
					  echo "Non classé";
				  }
			if(!is_null($rank)) { 
			if($rank == 1) { echo $rank; ?><sup>er</sup><?php } else { echo $rank; ?><sup>ème</sup><?php } ?></h1>
			<p>sur <?php echo $nb_participants; ?></p>
			<?php } ?>
</div>
	</div>
	<p class="latestBets">
		<?php if(isset($_GET['user'])) { ?>
		<i>d</i><a href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/qatar2022/index_pronostics_user_mobile.php?user=<?php echo $_GET['user']; ?>&idcompetition=<?php echo $idcompetitions; ?>">Voir ses pronostics précédents</a><!---->
		<?php } else { ?>
		<i>d</i><a href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/qatar2022/index_mypronostics_user_mobile.php?idcompetition=<?php echo $idcompetitions; ?>">Voir mes pronostics précédents</a><!--https://prono-foot-33export.herokuapp.com/index_mypronostics_user_mobile.php-->
		<?php } ?>
	</p>

	<h1 class="myLeagueTitle">Mes Bonus</h1>
	<div class="success">
	<?php if(isset($_GET['user'])) { echo $name . " n'a encore aucun point bonus."; } else { ?>Vous n'avez encore aucun point bonus.<?php } ?>
</div>
<!--
	<h1 class="myLeagueTitle">mes trophees</h1>

	<dl class="accordion" id="trophies">
		<dt class="medalTitle bronze"><a href="#">Bronze</a></dt>
		<dd class="trophyList" style="display: none;">
			<div data-id="5" class="achievements">
	<span class="trophyHolder"></span>
	<div class="trophyDetails">
		<h1>Passe à dix</h1>
		<p>J’ai pronostiqué 10 fois d’affilé</p>
	</div>
</div>
<div data-id="9" class="achievements">
	<span class="trophyHolder"><img src="https://pronosfoot.mycanal.fr/assets/front/img/trophies/9.jpg" alt=""></span>
	<div class="trophyDetails">
		<h1>Feuille de match</h1>
		<p>J’ai complété le formulaire</p>
		<a href="#" onclick="Trophy.share('9'); return false" class="btnShareTrophy"><i class="whiteBorder">j</i>Partager</a>
	</div>
</div><div data-id="10" class="achievements">
	<span class="trophyHolder"><img src="https://pronosfoot.mycanal.fr/assets/front/img/trophies/10.jpg" alt=""></span>
	<div class="trophyDetails">
		<h1>Pronostiqueur sans fil</h1>
		<p>J’ai fait mes pronostics au moins une fois via mon téléphone</p>
		<a href="#" onclick="Trophy.share('10'); return false" class="btnShareTrophy"><i class="whiteBorder">j</i>Partager</a>
	</div>
</div><div data-id="6" class="achievements">
	<span class="trophyHolder"></span>
	<div class="trophyDetails">
		<h1>Expert de la palette</h1>
		<p>J’ai pronostiqué 20 journées sur la totalité de la saison</p>
	</div>
</div><div data-id="7" class="achievements">
	<span class="trophyHolder"></span>
	<div class="trophyDetails">
		<h1>Sélectionneur</h1>
		<p>J’ai créé une ligue</p>
	</div>
</div><div data-id="3" class="achievements">
	<span class="trophyHolder"><img src="https://pronosfoot.mycanal.fr/assets/front/img/trophies/3.jpg" alt=""></span>
	<div class="trophyDetails">
		<h1>Capitaine</h1>
		<p>Je suis le meilleur pronostiqueur de la journée parmi mes amis</p>
		<a href="#" onclick="Trophy.share('3'); return false" class="btnShareTrophy"><i class="whiteBorder">j</i>Partager</a>
	</div>
</div><div data-id="4" class="achievements">
	<span class="trophyHolder"></span>
	<div class="trophyDetails">
		<h1>12ème homme</h1>
		<p>J’ai pronostiqué 10 fois le bon résultat pour mon équipe de cœur</p>
	</div>
</div>
		</dd>
		<dt class="medalTitle silver"><a href="#">Argent</a></dt>
		<dd class="trophyList" style="display: none;">
			<div data-id="13" class="achievements">
	<span class="trophyHolder"></span>
	<div class="trophyDetails">
		<h1>Commentateur (Consultant du CFC)</h1>
		<p>J’ai pronostiqué 30 journées sur la totalité de la saison</p>
	</div>
</div><div data-id="14" class="achievements">
	<span class="trophyHolder"></span>
	<div class="trophyDetails">
		<h1>Président</h1>
		<p>J’ai créé une ligue comportant au moins 10 membres</p>
	</div>
</div><div data-id="12" class="achievements">
	<span class="trophyHolder"></span>
	<div class="trophyDetails">
		<h1>Chef des Ultras</h1>
		<p>J’ai pronostiqué 20 fois le bon résultat pour mon équipe de cœur</p>
	</div>
</div><div data-id="11" class="achievements">
	<span class="trophyHolder"></span>
	<div class="trophyDetails">
		<h1>Grands événements (bookmaker)</h1>
		<p>J’ai pronostiqué 30 fois de manière correcte le score d’un grand match</p>
	</div>
</div>
		</dd>
		<dt class="medalTitle gold"><a href="#">Or</a></dt>
		<dd class="trophyList" style="display: none;">
			<div data-id="15" class="achievements">
	<span class="trophyHolder"></span>
	<div class="trophyDetails">
		<h1>Journaliste sportif (Hervé Mathoux)</h1>
		<p>J’ai pronostiqué 38 journées sur la totalité de la saison</p>
	</div>
</div>
		</dd>
	</dl>​-->
</div>

<script>/*
$(document).ready(function(){
	Trophy.init({
		trophies: [{"id_trophy":5,"title":"Passe \u00e0 dix","description":"J\u2019ai pronostiqu\u00e9 10 fois d\u2019affil\u00e9","points":10,"level":1,"image":"https:\/\/pronosfoot.mycanal.fr\/assets\/front\/img\/trophies\/5.jpg","unlocked":0},{"id_trophy":9,"title":"Feuille de match","description":"J\u2019ai compl\u00e9t\u00e9 le formulaire","points":10,"level":1,"image":"https:\/\/pronosfoot.mycanal.fr\/assets\/front\/img\/trophies\/9.jpg","unlocked":1},{"id_trophy":10,"title":"Pronostiqueur sans fil","description":"J\u2019ai fait mes pronostics au moins une fois via mon t\u00e9l\u00e9phone","points":15,"level":1,"image":"https:\/\/pronosfoot.mycanal.fr\/assets\/front\/img\/trophies\/10.jpg","unlocked":1},{"id_trophy":6,"title":"Expert de la palette","description":"J\u2019ai pronostiqu\u00e9 20 journ\u00e9es sur la totalit\u00e9 de la saison","points":15,"level":1,"image":"https:\/\/pronosfoot.mycanal.fr\/assets\/front\/img\/trophies\/6.jpg","unlocked":0},{"id_trophy":7,"title":"S\u00e9lectionneur","description":"J\u2019ai cr\u00e9\u00e9 une ligue","points":20,"level":1,"image":"https:\/\/pronosfoot.mycanal.fr\/assets\/front\/img\/trophies\/7.jpg","unlocked":0},{"id_trophy":3,"title":"Capitaine","description":"Je suis le meilleur pronostiqueur de la journ\u00e9e parmi mes amis","points":30,"level":1,"image":"https:\/\/pronosfoot.mycanal.fr\/assets\/front\/img\/trophies\/3.jpg","unlocked":1},{"id_trophy":4,"title":"12\u00e8me homme","description":"J\u2019ai pronostiqu\u00e9 10 fois le bon r\u00e9sultat pour mon \u00e9quipe de c\u0153ur","points":50,"level":1,"image":"https:\/\/pronosfoot.mycanal.fr\/assets\/front\/img\/trophies\/4.jpg","unlocked":0},{"id_trophy":13,"title":"Commentateur (Consultant du CFC)","description":"J\u2019ai pronostiqu\u00e9 30 journ\u00e9es sur la totalit\u00e9 de la saison","points":80,"level":2,"image":"https:\/\/pronosfoot.mycanal.fr\/assets\/front\/img\/trophies\/13.jpg","unlocked":0},{"id_trophy":14,"title":"Pr\u00e9sident","description":"J\u2019ai cr\u00e9\u00e9 une ligue comportant au moins 10 membres","points":100,"level":2,"image":"https:\/\/pronosfoot.mycanal.fr\/assets\/front\/img\/trophies\/14.jpg","unlocked":0},{"id_trophy":12,"title":"Chef des Ultras","description":"J\u2019ai pronostiqu\u00e9 20 fois le bon r\u00e9sultat pour mon \u00e9quipe de c\u0153ur","points":120,"level":2,"image":"https:\/\/pronosfoot.mycanal.fr\/assets\/front\/img\/trophies\/12.jpg","unlocked":0},{"id_trophy":11,"title":"Grands \u00e9v\u00e9nements (bookmaker)","description":"J\u2019ai pronostiqu\u00e9 30 fois de mani\u00e8re correcte le score d\u2019un grand match","points":150,"level":2,"image":"https:\/\/pronosfoot.mycanal.fr\/assets\/front\/img\/trophies\/11.jpg","unlocked":0},{"id_trophy":15,"title":"Journaliste sportif (Herv\u00e9 Mathoux)","description":"J\u2019ai pronostiqu\u00e9 38 journ\u00e9es sur la totalit\u00e9 de la saison","points":200,"level":3,"image":"https:\/\/pronosfoot.mycanal.fr\/assets\/front\/img\/trophies\/15.jpg","unlocked":0}]
	});

	League.init({
		leagues: []
	});
})

var League =
{
	leagues: {},

	init: function(config)
	{
		if(config.leagues) this.leagues = config.leagues;
	},

	invite: function(id_league)
	{
		var l = League._getLeagueById(id_league);

		if( ! l)
			return;

		FB.ui({
			method		: 'apprequests',
			message		: sprintf("Rejoins ma ligue %s et voyons qui est le meilleur pronostiqueur sur Canal+ Ligue 1.", l.name),
			exclude_ids	: l.ids,
			data		: '{"id_league": "'+id_league+'"}'
		},
		function(response)
		{
			if( ! response)
				return;

			$.ajax({
				cache		:	false,
				type		:	'POST',
				dataType	:	'json',
				url			:	ENV.callback_url + '/user/ajax/addLeagueMembers',
				data		:	{
					'id_league'	:	id_league,
					'members'	:	response.to
				}
			});
		});
	},

	_getLeagueById: function(id_league)
	{
		for(var i = 0; i < League.leagues.length; i++)
		{
			if(League.leagues[i].id_league == id_league)
				return League.leagues[i];
		}

		return false;
	}
}

var Trophy =
{
	trophies: {},

	init: function(config)
	{
		if(config.trophies) this.trophies = config.trophies;

		$('#trophies dd').hide();
		$('#trophies dt a').click(function(){

			var parent_dt = $(this).parent();

			if ($(parent_dt).hasClass('active'))
			{
				$(parent_dt).removeClass('active');
				$(parent_dt).next().slideUp();
			}
			else
			{
				$('#trophies dt').removeClass('active');
				$(parent_dt).addClass('active');
				$('#trophies dd').slideUp();
				$(parent_dt).next().slideDown();
			}
			return false;
		});
	},

	share: function(id_trophy)
	{
		var t = Trophy._getTrophyById(id_trophy);

		if( ! t)
			return;

        var is_uiwebview = /(iPhone|iPod|iPad).*AppleWebKit(?!.*Safari)/i.test(navigator.userAgent);
        var isNativeApp	 = false;

        if(navigator.userAgent.match(/FB/) != null)
        {
            if(navigator.userAgent.match(/Chrome/i) != null)
                isNativeApp	=	true;
        }

        var userAgent = window.navigator.userAgent.toLowerCase();

        var standalone	=	window.navigator.standalone,
            safari		=	/safari/i.test(userAgent),
            fb			=	/fb/i.test(userAgent),
            ios			=	/iphone|ipod|ipad/i.test(userAgent);

        //Secondary Ios webview check
        var is_ioswebview	=	false;

        if(ios && ! standalone && ! safari && ! fb)
            is_ioswebview	=	true;

        if((is_ioswebview || isNativeApp || Env.core.is_webview_android)) {
            var link = Env.fb.callbackUrl + "/og/trophy/?title=" + t.tile + '&description=' + t.description.toLowerCase() + "&id_user=10155870072269961&id_trophy=" + id_trophy;
            var url = Env.fb.callbackUrl + '/user/profile/';

            window.open('https://www.facebook.com/dialog/feed?app_id=' + Env.fb.appId + '&display=touch&link=' + encodeURIComponent(link) + '&redirect_uri=' + encodeURIComponent(url));
        }
        else {
            FB.ui({
                method: 'feed',
                app_id: Env.fb.appId,
                link: Env.fb.callbackUrl + "/og/trophy/?title=" + t.tile + '&description=' + t.description.toLowerCase() + "&id_user=10155870072269961&id_trophy=" + id_trophy,
                redirect_uri: Env.fb.callbackUrl + '/user/profile/'
            });
        }
	},

	_getTrophyById: function(id_trophy)
	{
		for(var i = 0; i < Trophy.trophies.length; i++)
		{
			if(Trophy.trophies[i].id_trophy == id_trophy)
				return Trophy.trophies[i];
		}

		return false;
	}
}*/
</script></div>
<br />
<br />
<br />
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
		</div>
		
		<script>
		/* Credits */
		/*$(document).ready(function()
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
            });
		});*/
		</script>
</body></html>