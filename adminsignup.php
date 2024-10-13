<?php 

header('Content-Type: application/json');

header('Access-Control-Allow-Origin: *');

include "config.php";

$pdo = new PDO('mysql:host=localhost; port=3306; dbname=library','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$statement = $pdo->prepare('SELECT * FROM admins');
$statement->execute();
$Admins = $statement->fetchAll(PDO::FETCH_ASSOC);

$data = json_decode(file_get_contents("php://input"),true);

$admin_password = mysqli_real_escape_string($conn,$data['admin_password']) ?? null;
$admin_email = mysqli_real_escape_string($conn,$data['admin_email_id']) ?? null;
$admin_username = mysqli_real_escape_string($conn,$data['admin_username']) ?? null;

function checkUsername($student_username):bool{
  $flag = 0;
  if($student_username === null || strlen($student_username) === 0){
    echo json_encode(array('result' => 'login unsuccessful','reason' => 'username field is mandatory'));
    http_response_code(400);
    return false;
  }
  else{
    if((strlen($student_username)) < 6 || (strlen($student_username) >= 9)){
      echo json_encode(array('result' => 'login unsuccessful','reason' => 'username size should be [6,7,8] characters long'));
      http_response_code(400);
      return false;
    }
    else{
      for($i = 0; $i < strlen($student_username); $i++){
        if(ctype_alpha($student_username[$i]) === false && ctype_digit($student_username[$i]) === false){
          $flag = 1;
          echo json_encode(array('result' => 'login unsuccessful','reason' => 'username cannot contain special characters'));
          http_response_code(400);
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
    echo json_encode(array('result' => 'login unsuccessful','reason' => 'password field is mandatory'));
    http_response_code(400);
    return false;
  }
  else{
    if((strlen($student_password)) < 6 || (strlen($student_password) >= 9)){
      echo json_encode(array('result' => 'login unsuccessful','reason' => 'password size should be [6,7,8] characters long'));
      http_response_code(400);
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
        echo json_encode(array('result' => 'login unsuccessful','reason' => 'password must contain atleast a lowercase, an uppercase, a digit and a special character'));
        http_response_code(400);
        return false;
      }
      else
      return true;
    }
  }
}
function checkEmail($admin_email):bool{
  if(str_ends_with($admin_email,"@gmail.com")){
    if(strlen($admin_email) > 10)
    return true;
  }
  http_response_code(400);
  echo json_encode(array('result' => 'signup unsuccessful','reason' => 'invalid email id'));
    return false;
}
switch($_SERVER['REQUEST_METHOD']){
  case 'POST':
    if(checkPassword($admin_password) && checkUsername($admin_username) && checkEmail($admin_email)){
      $flag = 0;
      foreach($Admins as $i => $admin){
        if($admin['EMAIL_ID'] === $admin_email){
          $flag = 1;
          echo json_encode(array('reason' => 'an account already exists with given email id','result' => 'signup unsuccessful'));
          http_response_code(409);
          break;
        }
      }
      if($flag === 0){
          $hash_password = password_hash($admin_password,PASSWORD_DEFAULT);
          $sql = "INSERT INTO admins(USERNAME , EMAIL_ID , PASSWORD) VALUES('{$admin_username}','{$admin_email}','{$hash_password}')";
          $result = mysqli_query($conn,$sql) or die('SQL query failed!');
          if($result){
            echo json_encode(array('result' =>'signup successful'));
          }
      }
    }
    break;
  default:
  http_response_code(405);
  echo json_encode(array('result' => 'sign up unsuccessful','reason' => 'method not allowed'));
}

?>