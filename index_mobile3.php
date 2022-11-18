<?php
//ini_set('display_errors',1); 
//error_reporting(E_ALL);
session_start();
require_once 'includes/config.php';
date_default_timezone_set('Africa/Douala');
global $mysqli;

// var_dump($_COOKIE['age']);
// die;

// On vérifie que l'age est correctement fixé
// if(!isset($_COOKIE['age'])) { // Si on n'a pas encore vérifié l'age
    // header('Location: check_age.php');
	// die();
// } else { // Si oui on vérifie qu'il est majeur
    // if($_COOKIE['age'] == "+21") {
		
	// } else { // S'il n'est pas majeur on le renvoie au controle d'age
		// header('Location: check_age.php');
		// die();
	// }
// }

$message = "";
if(isset($_GET["action"])) {
	
	if($_GET['action'] == "deconnexion") { 
		$message = "Vous êtes déconnecté!";
		session_destroy();
	}
	
}
if($_POST) {
	$_SESSION['code'] = $_POST['code'];
	$_SESSION['numero'] = $_POST['numero'];
	//echo "<pre>";
	//var_dump($_POST['numero']);

	
	// Check connection
	if ($mysqli->connect_error) {
		die("Connection failed: " . $mysqli->connect_error);
	}

	$sql = "SELECT * FROM utilisateurs WHERE numero = '".trim(($_POST['numero']))."' AND code = '".trim($_POST['code'])."'; ";
	$result = $mysqli->query($sql);


	if ($row = $result->fetch_assoc()) {
		
		$_SESSION['id'] = $row["id"];
		$_SESSION['oauth_provider'] = $row["oauth_provider"];
		header('Location: index_test.php');
		die();
		
	} else {
		$message = "<b>Votre code est incorrect!</b>";
	}
}
?>
<html lang="fr">
<head>

	<meta charset="UTF-8">
	<title>33 Export Pronostics - Connexion</title>
	<meta name="viewport" content="width=device-width, initial-scale=1  maximum-scale=1 user-scalable=no">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-touch-fullscreen" content="yes">
	<meta name="HandheldFriendly" content="True">
	<meta name="google-signin-client_id" content="113152187179-vpmsd977s7ispastovpk7b5fanaiu6lu.apps.googleusercontent.com">

	<link rel="stylesheet" href="css3/materialize.css">
	<link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="css3/normalize.css">
	<link rel="stylesheet" href="css3/owl.carousel.css">
	<link rel="stylesheet" href="css3/owl.theme.css">
	<link rel="stylesheet" href="css3/owl.transitions.css">
	<link rel="stylesheet" href="css3/fakeLoader.css">
	<link rel="stylesheet" href="css3/style.css">
	
	<link rel="shortcut icon" href="img/favicon.png">
	<script src="https://apis.google.com/js/platform.js?onload=onLoadCallback" async defer></script>
	
	<script>
	  var googleUser = {};
	 
		window.onLoadCallback = function(){
		gapi.load('auth2', function(){
		  // Retrieve the singleton for the GoogleAuth library and set up the client.
		  auth2 = gapi.auth2.init({
			client_id: '113152187179-vpmsd977s7ispastovpk7b5fanaiu6lu.apps.googleusercontent.com',
			cookiepolicy: 'single_host_origin',
			// Request scopes in addition to 'profile' and 'email'
			//scope: 'additional_scope'
		  });
		   console.log("Début Google SignIn"); 
		   attachSignin(document.getElementById('loginBtn'));
		   console.log("Fin Google SignIn"); 
		});
		}
	 

	  function attachSignin(element) {
		console.log(element.id);
		auth2.attachClickHandler(element, {},
			function(googleUser) {
			  //document.getElementById('name').innerText = "Signed in: " + googleUser.getBasicProfile().getName();
			   
			  var profile = googleUser.getBasicProfile();
			   document.getElementById('name').innerText = "Signed in: " + googleUser.getBasicProfile().getName();
				console.log("ID: " + profile.getId()); // Don't send this directly to your server!
				console.log('Full Name: ' + profile.getName());
				console.log('Given Name: ' + profile.getGivenName());
				console.log('Family Name: ' + profile.getFamilyName());
				console.log("Image URL: " + profile.getImageUrl());
				console.log("Email: " + profile.getEmail());
				// The ID token you need to pass to your backend:
				var id_token = googleUser.getAuthResponse().id_token;
				console.log("ID Token: " + id_token);
				
				if(profile) {
					$.ajax({
						type: 'POST',
						url: '_ajax/login_gmail.php',
						data: {id:profile.getId(), name:profile.getName(), email:profile.getEmail(), image:profile.getImageUrl()}
					}).done(function(data) {
						console.log(data);
						window.location.href='index_test.php';
					}).fail();
				}
				
			}, function(error) {
			  alert(JSON.stringify(error, undefined, 2));
			});
	  }
	  </script>
	
	<script>
		
		
		logInWithFacebook = function() {
			//function getFbUserData(){
					FB.api('/me', {fields: 'id,name,email'},
					function (response) {
					//console.log(JSON.stringify(response));
					console.log('id : '+response.id);
					console.log('Email : '+response.email);
					console.log('Name : '+response.name);
						/*document.getElementById('fbLink').setAttribute("onclick","fbLogout()");
						document.getElementById('fbLink').innerHTML = 'Logout from Facebook';
						document.getElementById('status').innerHTML = 'Thanks for logging in, ' + response.first_name + '!';
						document.getElementById('userData').innerHTML = '<p><b>FB ID:</b> '+response.id+'</p><p><b>Name:</b> '+response.first_name+' '+response.last_name+'</p><p><b>Email:</b> '+response.email+'</p><p><b>Gender:</b> '+response.gender+'</p><p><b>Locale:</b> '+response.locale+'</p><p><b>Picture:</b> <img src="'+response.picture.data.url+'"/></p><p><b>FB Profile:</b> <a target="_blank" href="'+response.link+'">click to view profile</a></p>';
						*/
						$.post('_ajax/login_facebook.php', {oauth_provider:'facebook',userData: JSON.stringify(response)}, function(data){ return true; }).done(function(data) {
							//console.log(data);
							if(response.id === undefined)
								window.location.href='index_mobile3.php';
							else
								window.location.href='index_test.php'; 
						});
					});
				//}
			// } else {
				//The person is not logged into this app or we are unable to tell. 
				// console.log('You are not connected yet.');
			// }
			return false;
		}
	</script>
	<script>
	  window.fbAsyncInit = function() {
		FB.init({
		  appId      : '1289689755102737',
		  cookie     : true,
		  xfbml      : true,
		  version    : 'v15.0'
		});
		
	<?php if(isset($_GET["action"])) {
			if($_GET['action'] == "deconnexion") {  ?>
		FB.logout(function(response) {
		   // Person is now logged out
		   
		});
	<?php } 
	} else {
	?>
		/*
		FB.getLoginStatus(function(response) {
			if (response.status === 'connected') {
				//display user data
				//getFbUserData();
				//alert("On détecte qu'il est connecté");
				FB.api('/me', {fields: 'id,name,email,picture'},
					function (response) {
						console.log(JSON.stringify(response));
						console.log('Email : '+response.email);
						console.log('Name : '+response.name);
						console.log('Image : '+response.picture.data.url);
						//document.getElementById('fbLink').setAttribute("onclick","fbLogout()");
						//document.getElementById('fbLink').innerHTML = 'Logout from Facebook';
						//document.getElementById('status').innerHTML = 'Thanks for logging in, ' + response.first_name + '!';
						//document.getElementById('userData').innerHTML = '<p><b>FB ID:</b> '+response.id+'</p><p><b>Name:</b> '+response.first_name+' '+response.last_name+'</p><p><b>Email:</b> '+response.email+'</p><p><b>Gender:</b> '+response.gender+'</p><p><b>Locale:</b> '+response.locale+'</p><p><b>Picture:</b> <img src="'+response.picture.data.url+'"/></p><p><b>FB Profile:</b> <a target="_blank" href="'+response.link+'">click to view profile</a></p>';
						
						$.post('_ajax/continue_with_facebook.php', {oauth_provider:'facebook',userData: JSON.stringify(response)}, function(data){ return true; }).done(function() { 
							window.location.href='index_test.php'; 
						});
					});
			}
		});*/
	<?php } ?>
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
	<style>
	/* Shared */
#loginBtn {
  box-sizing: border-box;
  position: relative;
  /* width: 13em;  - apply for fixed size */
  margin: 0.2em;
  padding: 0 15px 0 46px;
  border: none;
  text-align: left;
  line-height: 34px;
  white-space: nowrap;
  border-radius: 0.2em;
  font-size: 16px;
  color: #FFF;
}
#loginBtn:before {
  content: "";
  box-sizing: border-box;
  position: absolute;
  top: 0;
  left: 0;
  width: 34px;
  height: 100%;
}
#loginBtn:focus {
  outline: none;
}
#loginBtn:active {
  box-shadow: inset 0 0 0 32px rgba(0,0,0,0.1);
}

.loginBtn {
  box-sizing: border-box;
  position: relative;
  /* width: 13em;  - apply for fixed size */
  margin: 0.2em;
  padding: 0 15px 0 46px;
  border: none;
  text-align: left;
  line-height: 34px;
  white-space: nowrap;
  border-radius: 0.2em;
  font-size: 16px;
  color: #FFF;
}
.loginBtn:before {
  content: "";
  box-sizing: border-box;
  position: absolute;
  top: 0;
  left: 0;
  width: 34px;
  height: 100%;
}
.loginBtn:focus {
  outline: none;
}
.loginBtn:active {
  box-shadow: inset 0 0 0 32px rgba(0,0,0,0.1);
}

/* Facebook */
.loginBtn--facebook {
  background-color: #4C69BA;
  background-image: linear-gradient(#4C69BA, #3B55A0);
  font-family: "Helvetica neue", Helvetica Neue, Helvetica, Arial, sans-serif;
  text-shadow: 0 -1px 0 #354C8C;
}
.loginBtn--facebook:before {
  border-right: #364e92 1px solid;
  background: url('https://s3-us-west-2.amazonaws.com/s.cdpn.io/14082/icon_facebook.png') 6px 6px no-repeat;
}
.loginBtn--facebook:hover,
.loginBtn--facebook:focus {
  background-color: #5B7BD5;
  background-image: linear-gradient(#5B7BD5, #4864B1);
}


/* Google */
.loginBtn--google {
  /*font-family: "Roboto", Roboto, arial, sans-serif;*/
  background: #DD4B39;
}
.loginBtn--google:before {
  border-right: #BB3F30 1px solid;
  background: url('https://s3-us-west-2.amazonaws.com/s.cdpn.io/14082/icon_google.png') 6px 6px no-repeat;
}
.loginBtn--google:hover,
.loginBtn--google:focus {
  background: #E74B37;
}

</style>
</head>
<body class="login-register-wrap">
<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v15.0&appId=1289689755102737&autoLogAppEvents=1" nonce="uD8S6Aia"></script>

	<!-- login register -->
	<div class="login-register-wrap-home">
		<div class="container">
			<div class="content">	
				<h1><img src="img/33_transparent_1.png" alt="" height="120px" width="120px"></h1>
				<h6>33 Export Foot</h6>	
				<div id="response">
					<?php //echo $live_url;
					echo $message;
				?>
				</div>
				<div class="fb-login-button" data-width="" data-size="large" data-scope="email" data-button-type="login_with" data-layout="rounded" data-auto-logout-link="false" data-use-continue-as="true" data-onlogin="logInWithFacebook()"></div>
				<!--<button class="loginBtn loginBtn--facebook" onClick="logInWithFacebook()">
				  Connexion via Facebook
				</button>-->
				<button id="loginBtn" class=" loginBtn--google" style="margin-top:15px">
				  Connexion via Google
				</button>
				<form action="index_mobile3.php" id="form_login" style="margin-top:15px" method="post">
					<div class="form-label-divider" style="margin-top:15px"><span style="display:inline">OU</span></div>
					<div id="name"></div>
					<input type="text" id="numero" value="<?php if($_POST) { echo $_POST['numero']; } ?>" placeholder="Votre Numero de Téléphone" name="numero" />
					<input type="password" id="code" placeholder="****" name="code" /> 
					<span><a href="#">Code Oublié?</a></span>
					<input type="submit" class="button-default" id="register" value="Connexion" style="background-color: #B51729;">
					<h6>Vous n'avez pas de compte ? <a href="index_mobile_register.php">S'enregistrer</a></h6>
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
	<script>
	// Google Login
	//startApp();
    </script>
<div class="hiddendiv common"></div>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-7YRDS0SQ0P"></script>
<script>
	window.onload = function() {
		setTimeout(function() {
		  if ( typeof(window.google_jobrunner) === "undefined" ) {
			console.log("ad blocker installed");
			//alert("Pour vous connecter avec Facebook veuillez désactiver votre bloqueur de publicité svp.");
		  } else {
			console.log("no ad blocking found.");
		  }
		}, 10000);
	};

  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-7YRDS0SQ0P');
</script>
</body>
</html>