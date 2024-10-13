
<?php
require 'vendor/autoload.php'; 
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
$pdo = new PDO('mysql:host=localhost; port=3306; dbname=library','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$statement = $pdo->prepare('SELECT * FROM admins');
$statement->execute();
$Admins = $statement->fetchAll(PDO::FETCH_ASSOC);
$flag = 0;
header('Content-Type: application/json');

header('Access-Control-Allow-Origin: *');

include "config.php";

$data = json_decode(file_get_contents("php://input"),true);

$admin_username = mysqli_real_escape_string($conn,$data['admin_username']) ?? null;

$admin_password = mysqli_real_escape_string($conn,$data['admin_password']) ?? null;

function checkUsername($student_username):bool{
  $flag = 0;
  if($student_username === null || strlen($student_username) === 0){
    http_response_code(400);
    echo json_encode(array('result' => 'login unsuccessful','reason' => 'username field is mandatory'));
    return false;
  }
  else{
    if((strlen($student_username)) < 6 || (strlen($student_username) >= 9)){
      http_response_code(400);
      echo json_encode(array('result' => 'login unsuccessful','reason' => 'username size should be [6,7,8] characters long'));
      return false;
    }
    else{
      for($i = 0; $i < strlen($student_username); $i++){
        if(ctype_alpha($student_username[$i]) === false && ctype_digit($student_username[$i]) === false){
          $flag = 1;
          http_response_code(400);
          echo json_encode(array('result' => 'login unsuccessful','reason' => 'username cannot contain special characters'));
          return false;
          break;
        }
      }
      if($flag === 0){
        return true;
      }
    }
  }
}
function checkPassword($student_password): bool{
  $flag = 0; $caps = 0; $small = 0; $digits = 0; $special = 0;
  if($student_password === null || strlen($student_password) === 0){
    http_response_code(400);
    echo json_encode(array('result' => 'login unsuccessful','reason' => 'password field is mandatory'));
    return false;
  }
  else{
    if((strlen($student_password)) < 6 || (strlen($student_password) >= 9)){
      http_response_code(400);
      echo json_encode(array('result' => 'login unsuccessful','reason' => 'password size should be [6,7,8] characters long'));
      return false;
    }
    else{
      for($i = 0; $i < strlen($student_password); $i++){
        if(ctype_upper($student_password[$i]))
        $caps++;
        else if(ctype_lower($student_password[$i]))
        $small++;
        else if(ctype_digit($student_password[$i]))
        $digits++;
        else
        $special++;
      }
      if($caps === 0 || $small === 0 || $digits === 0 || $special === 0){
        http_response_code(400);
        echo json_encode(array('result' => 'login unsuccessful','reason' => 'password must contain atleast a lowercase, an uppercase, a digit and a special character'));
        return false;
      }
      else
      return true;
    }
  }
}
switch($_SERVER['REQUEST_METHOD']){
  case 'POST':
    if(checkPassword($admin_password) && checkUsername($admin_username)){
      foreach($Admins as $i => $admin){
        if($admin['USERNAME'] === $admin_username && $admin['PASSWORD'] === $admin_password)
          $flag = 1;
      }
      if($flag === 1){
        $jwt = createToken($admin_username,$secret_key);
        echo json_encode(array('result' => 'login successful','token' => $jwt)) . "\n";
      }
      else{
        echo json_encode(array('result' => 'login unsuccessful','reason' => 'account with given credentials do not exist')) . "\n";
        http_response_code(404);
      }
      
    }
    break;
    case 'GET':
    $valid = 0;
    $headers = apache_request_headers();
    if(isset($headers['Authorization'])){
      $authorization = $headers['Authorization'];
      $header = explode(' ',$authorization);
      $jwt =  $header[1];
      try{
        $decoded = JWT::decode($jwt,new Key($secret_key,'HS256'));
        if($decoded){
          foreach($Admins as $i => $admin){
            if($admin['USERNAME'] === $decoded->username){
              echo json_encode(['result' => 'valid token']) . "\n";
              $valid = 1;
            }
          }
          if($valid === 0){
            echo json_encode(['result' => 'invalid token']) . "\n";
            http_response_code(400);
          }
          
        }
      }
      catch(Exception $o){
        echo "Error message: ".$o->getMessage();
        http_response_code(400);
      }
      
    }
    else{
      echo "No authorization header is present!";
      http_response_code(401);
    }
      break;
  default:
  http_response_code(405);
  echo json_encode(array('result' => 'failed to login','reason' => 'method not allowed')) . "\n";
}
?>
