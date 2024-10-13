<?php 
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Methods,Content-Type,Access-Control-Allow-Methods,Authorization,X-Requested-With');
$data = json_decode(file_get_contents("php://input"),true);
$pdo = new PDO('mysql:host=localhost; port=3306; dbname=library','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$statement = $pdo->prepare('SELECT * FROM students');
$statement->execute();
$Students = $statement->fetchAll(PDO::FETCH_ASSOC);
$s = $pdo->prepare('SELECT * FROM admins');
$s->execute();
$Admins = $s->fetchAll(PDO::FETCH_ASSOC);
$admin_username = $data['admin_username'] ?? null;
$admin_password = $data['admin_password'] ?? null;
$student_username = $data['student_username'] ?? null;

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
include "config.php";

$flag  = 0;
switch($_SERVER['REQUEST_METHOD']){
  case 'DELETE':
    if(checkUsername($admin_username) && checkPassword($admin_password)){
      foreach ($Students as $i => $student) {
        if($student['STUDENT_USERNAME'] === $student_username && $student['APPROVED'] === 1){
          $flag = 1;
          $sql = "DELETE FROM students WHERE STUDENT_USERNAME = '{$student_username}'";
          if(mysqli_query($conn,$sql)){
            echo json_encode(array("result" => 'student record deleted successfully')) . "\n";
          }else{
            echo json_encode(array('result' => 'student record not deleted','reason' => 'SQL query failed')) . "\n";
          }
        }
      }
      if($flag === 0){
        http_response_code(404);
        echo json_encode(array('result' => 'deletion unsuccessful','reason' => 'no such username found'))  . "\n";
      }
    }
    else{
      http_response_code(400);
      echo json_encode(['result' => 'deletion unsuccessful','reason' => 'invalid admin credentials']) . "\n";
    }
    
    break;
    default:
    http_response_code(405);
    echo json_encode(array('result' => 'deletion unsuccessful','reason' => 'method not allowed'));
}
?>