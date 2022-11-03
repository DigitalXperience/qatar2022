<?php
header('Content-Type: application/json');

define('DB_HOST', 'localhost');
// define('DB_NAME', 'healtheland');
define('DB_NAME', 'a5a6325_tbc');
//define('DB_USER','root');
define('DB_USER','a5a6325_pollux');
//define('DB_PASSWORD','');
define('DB_PASSWORD','30Decembre?');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
    exit();
}

$signed_request = $_POST['signed_request'];
$data = parse_signed_request($signed_request);
$user_id = $data['user_id'];

// Start data deletion

date_default_timezone_set('Africa/Douala');
$date = date('Y-m-d H:i:s');
$sql = "UPDATE internautes SET suppression = '$date' WHERE idF = '".$user_id ."' LIMIT 1;";
$mysqli->query($sql);

$status_url = 'https://www.33export-foot.com/deletion.php?id=' . $user_id; // URL to track the deletion
$confirmation_code = 'DF-2563' . $user_id; // unique code for the deletion request

$data = array(
  'url' => $status_url,
  'confirmation_code' => $confirmation_code
);
echo json_encode($data);

function parse_signed_request($signed_request) {
  list($encoded_sig, $payload) = explode('.', $signed_request, 2);

  $secret = "appsecret"; // Use your app secret here

  // decode the data
  $sig = base64_url_decode($encoded_sig);
  $data = json_decode(base64_url_decode($payload), true);

  // confirm the signature
  $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
  if ($sig !== $expected_sig) {
    error_log('Bad Signed JSON signature!');
    return null;
  }

  return $data;
}

function base64_url_decode($input) {
  return base64_decode(strtr($input, '-_', '+/'));
}
?>