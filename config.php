<?php 
$conn = mysqli_connect("localhost","root","","library") or die("Connection Failed");
require 'vendor/autoload.php'; 
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
//$hashing_key = "fvybkeabf86q3yr48eioss.wr,bmwhksnkvdnubnkjvbfehg68y4guvh5uygevbdfireffirejf5uyrehjf9eodiwjkfhey9deiukcj";
$secret_key = "jwt_is_secure";
function createToken($username,$password) {
  $iat = time();
  return JWT::encode(array('issue time' => $iat,'expiration time' => $iat+3600,'username' => $username), $password,"HS256");
}
function checkToken($token,$password) {
  try {
      $decoded = JWT::decode($token, new Key($password,'HS256'));
      return $decoded; 
  } 
  catch (Exception $o) {
      http_response_code(401);
      echo json_encode(array('result' => 'invalid token', 'reason' => $o->getMessage()));
      return null;
  }
}
function encodePassword($password){
  $hash = null;
  for($i = 0; $i < strlen($password); $i++){
    $hash[$i] = ord($password[$i]);
  }
  return $hash;
}
function decodePassword($hash){
  $password = null;
  for($i = 0; $i < sizeof($hash); $i++){
    $password .= chr($hash[$i]);
  }
  return $password;
}

?>