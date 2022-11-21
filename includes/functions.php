<?php

global $conn;
@session_start();


# list of useful function to the program

function get_id() {
	
}

function pronostiquer_vainqueur() {
	
}

function pronostiquer_scores($id_rencontre, $score_eq1, $score_eq2, $vainqueur_id = null) {
	$sql = "INSERT INTO `pronostics` (`id`, `rencontre_id`, `utilisateur_id`, `score_eq1`, `score_eq2`, `vainqueur_id`) 
			VALUES (NULL, '1', '1', '3', '0', NULL), (NULL, '2', '1', NULL, NULL, '4');";
}

function update_scores() {
	
}

function get_team_info($id) {
	
}

function get_rencontres_du_jour() {
	
}

function periode_poule() {
	
}

function translate_day($day) {
	$translation = null;
	switch ($day) {
		case "Sunday" : $translation = "Dimanche"; 
	}
	return $translation;
}

function get_params(){
   global $conn;
   var_dump($conn);
   
   $data = array();
   
   $query = "SELECT label, valeur FROM admin_config";
   var_dump($query);
   
   $result = mysqli_query($conn, $query);
   
   while ($obj=mysqli_fetch_object($result)){
		$data[$obj->label] = $obj->valeur;
   }
   
   var_dump($data);
   
   return $data;
}

function translate_mois($mois) {
	$translation = null;
	switch ($mois) {
		case "April" : 
		case "04" : 
		case "4" : $translation = "Avril"; break;
		case "May" : 
		case "05" : 
		case "5" : $translation = "Mai"; break;
		case "June" : 
		case "06" : 
		case "6" : $translation = "Juin";break;
		case "July" : 
		case "07" : 
		case "7" : $translation = "Juillet";break;
		case "September" : 
		case "09" : 
		case "9" : $translation = "Septembre";break;
		case "October" : 
		case "10" : $translation = "Octobre";break;
		case "November" : 
		case "11" : $translation = "Novembre";break;
		case "December" : 
		case "12" : $translation = "Décembre";break;
	}
	return $translation;
}

function translate_jour($jr) {
	$translation = null;
	switch ($jr) {
		case "Mon" : 
		case "MON" : $translation = "Lundi"; break;
		case "Tue" : 
		case "TUE" : $translation = "Mardi"; break;
		case "Wed" : 
		case "WED" : $translation = "Mercredi"; break;
		case "Thu" : 
		case "THU" : $translation = "Jeudi"; break;
		case "Fri" : 
		case "FRI" : $translation = "Vendredi"; break;	
		case "Sat" : 
		case "SAT" : $translation = "Samedi"; break;
		case "Sun" : 
		case "SUN" : $translation = "Dimanche"; break;		
	}
	return $translation;
}

function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}


/* Quizz functions */

function getQuestions()
{
    global $mysqli;
	$nq = 11;
    $stmt = $mysqli->prepare("
		(SELECT * FROM `questions` WHERE level = 1 ORDER by rand() limit $nq) 
		UNION 
		(SELECT * FROM `questions` WHERE level = 2 ORDER BY RAND() limit $nq)
		UNION 
		(SELECT * FROM `questions` WHERE id > 100 ORDER BY RAND() limit $nq)		
	");
    if (!($stmt)) {
        throw new Exception("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
    }

    if (!($res = $stmt->get_result())) {
        throw new Exception("Getting result set failed: (" . $stmt->errno . ") " . $stmt->error);
    }
    return $res->fetch_all(MYSQLI_ASSOC);
    
}

/**
 * Get Answer
 */
function getAnswer($question_id)
{
    global $mysqli;
    $stmt = $mysqli->prepare("select answer1 from questions where id = '$question_id'");
    if (!($stmt)) {
        throw new Exception("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
    }

    if (!($res = $stmt->get_result())) {
        throw new Exception("Getting result set failed: (" . $stmt->errno . ") " . $stmt->error);
    }
    return $res->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get Results
 */
function getResults($postData)
{
    global $mysqli;
	date_default_timezone_set('Africa/Douala');
	
	// Traces -- On enregistre les traces du passage de l'utilisateur pour un audits
	$date = date('Y-m-d H:i:s');
	$sql9 = "INSERT INTO traces VALUES(NULL, 'Calcul points du quizz de ".$_SESSION['name']." id: ".$_SESSION['id']."', '".$date."')";
	$mysqli->query($sql9);
	// Traces --
	
	date_default_timezone_set('Africa/Douala');
	$date = new DateTime("now");
	$results = getAnswers();
    $i = 1;
    $right_answer = 0;
    $wrong_answer = 0;
    $unanswered = 0;
    $pts = 0;
	$user = $postData['user']; 
	$status = 'fini'; 
	unset($postData['user']);
	unset($postData['question_id']);
	unset($postData['authResponse']);
	
	
	
	
    
	// Préparation de la commande d'insertion
	$stmt = $mysqli->prepare("INSERT INTO reponses (iduser, question_id, reponse) VALUES (?, ?, ?)");
	$stmt->bind_param("sss", $iduser, $question_id, $reponse);
	
	foreach ($postData as $q => $a) {
		//var_dump($a);
		// switch ($a) {
			// case 'answer1':
				// if($results[$q-1]['level'] == "1") {
				// $pts = $pts + 5;
				// }
				// if($results[$q-1]['level'] == "2") {
					// $pts = $pts + 10;
				// }
				// if($results[$q-1]['level'] == "3") {
					// $pts = $pts + 20;
				// }
				// $right_answer++;
				// break;
			// case 5:
				// $unanswered++;
				// break;
			// default:
				// $wrong_answer++;
				// break;
		// }
		
		if($a == 'answer1') {
			if($results[$q-1]['level'] == "1") {
				$pts = $pts + 5;
			}
			if($results[$q-1]['level'] == "2") {
				$pts = $pts + 10;
			}
			if($results[$q-1]['level'] == "3") {
				$pts = $pts + 15;
			}
			$right_answer++;
		} else if($a == 5){
            $unanswered++;
        }
        else{
            $wrong_answer++;
        }
		
		// set parameters and execute
		$iduser = $user;
		$question_id = $q; 
		$reponse = $a;
		if(!$stmt->execute()){
			trigger_error("there was an error.:".$mysqli->error, E_USER_WARNING);
		}
		
	}
	
	$sql = "UPDATE `participations` SET `points` = '" . $pts . "', `heure_fin` = '".date("Y-m-d H:i:s")."', `status` = '" . $status . "' WHERE `participations`.`iduser` = '" . $_SESSION['id'] . "' LIMIT 1; ";
	//var_dump($sql); die;
	$mysqli->query($sql);
	$datetime1 = new DateTime($_SESSION['debut_quizz']);
	$datetime2 = new DateTime(date("Y-m-d H:i:s"));
	$interval = $datetime1->diff($datetime2);
	$timing =  $interval->format('%i')." Min ".$interval->format('%s')." Sec";
	
	//die;
    $stmt->close();
    	
    return [
        "right_answer"=> $right_answer,
        "wrong_answer"=> $wrong_answer,
        "unanswered"=> $unanswered,
        "duree"=> $timing,
        "pts"=> $pts
    ];
}

function saveResponse($postData)
{
    global $mysqli;
	$results = getQuestions();
    $i = 1;
    $right_answer = 0;
    $wrong_answer = 0;
    $unanswered = 0;
    $pts = 0;
    
	
    foreach ($results as $key => $result) {
        $k = $key + 1;
        if($result['answer'] == $postData["$k"]) {
			if($result['level'] == "1") {
				$pts = $pts + 5;
			}
			if($result['level'] == "2") {
				$pts = $pts + 10;
			}
			if($result['level'] == "3") {
				$pts = $pts + 15;
			}
            $right_answer++;
        } else if($postData["$k"] == 5){
            $unanswered++;
        }
        else{
            $wrong_answer++;
        }
			
		// prepare and bind
		$stmt = $mysqli->prepare("INSERT INTO reponses (iduser, question_id, reponse) VALUES (?, ?, ?)");
		$stmt->bind_param("sss", $iduser, $question_id, $reponse);

		// set parameters and execute
		$iduser = $_SESSION['id'];
		$question_id = $result['id'];
		$reponse = $result['answer'];
		$stmt->execute();
			
        $i++;
    }
	
	

    return [
        "right_answer"=> $right_answer,
        "wrong_answer"=> $wrong_answer,
        "unanswered"=> $unanswered,
        "pts"=> $pts
    ];
}

function custom_shuffle($my_array = array()) {
  $copy = array();
  while (count($my_array)) {
    // takes a rand array elements by its key
    $element = array_rand($my_array);
    // assign the array and its value to an another array
    $copy[$element] = $my_array[$element];
    //delete the element from source array
    unset($my_array[$element]);
  }
  return $copy;
}

/**
 * Get Questions and Answers
 */
function getAnswers()
{
    global $mysqli;
    $stmt = $mysqli->prepare("select * from questions");
    if (!($stmt)) {
        throw new Exception("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
    }

    if (!($res = $stmt->get_result())) {
        throw new Exception("Getting result set failed: (" . $stmt->errno . ") " . $stmt->error);
    }
    return $res->fetch_all(MYSQLI_ASSOC);
    
}

/**
 * Genere un nombre aléatoire de 4 chiffres
 */
function createCode()
{
	return rand(1000,9999);
}

/*
 * 
 */
function sendSMS($message, $recp)
{
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'https://sms.etech-keys.com/ss/sendsms.php',
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'POST',
	  CURLOPT_POSTFIELDS => array(  'sender_id' => '33export','destinataire' => '237'.$recp,'message' => $message,'login' => '699124249',
									'password' => 'as!69@81','ext_id' => '12345','programmation' => '0'),
	));

	$response = curl_exec($curl);

	curl_close($curl);
	//echo $response;
}

function sendCodebySMS($number, $code)
{
	//$code = createCode();
	$message = "Votre code de connexion est le : $code";
	sendSMS($message, $number);
}