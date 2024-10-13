<?php
include "config.php";

$pdo = new PDO('mysql:host=localhost; port=3306; dbname=library','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$statement = $pdo->prepare('SELECT * FROM students');
$statement->execute();
$Students = $statement->fetchAll(PDO::FETCH_ASSOC);
$flag = 0;
$bit = 0;
$data = json_decode(file_get_contents("php://input"),true);
$student_username = $data['student_username'] ?? null;
$student_password = $data['student_password'] ?? null;
function checkUsername($student_username):bool{
  $flag = 0;
  if($student_username === null || strlen($student_username) === 0){
    echo json_encode(array('result' => 'login unsuccessful','reason' => 'username field is mandatory'));
    return false;
  }
  else{
    if((strlen($student_username)) < 6 || (strlen($student_username) >= 9)){
      echo json_encode(array('result' => 'login unsuccessful','reason' => 'username size should be [6,7,8] characters long'));
      return false;
    }
    else{
      for($i = 0; $i < strlen($student_username); $i++){
        if(ctype_alpha($student_username[$i]) === false && ctype_digit($student_username[$i]) === false){
          $flag = 1;
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
    echo json_encode(array('result' => 'login unsuccessful','reason' => 'password field is mandatory'));
    return false;
  }
  else{
    if((strlen($student_password)) < 6 || (strlen($student_password) >= 9)){
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
        echo json_encode(array('result' => 'login unsuccessful','reason' => 'password must contain atleast a lowercase, an uppercase, a digit and a special character'));
        return false;
      }
      else
      return true;
    }
  }
}
$username_validity = checkUsername($student_username);
$password_validity = checkPassword($student_password);
switch($_SERVER['REQUEST_METHOD']){
  case 'POST':
    if($username_validity && $password_validity){
      foreach($Students as $i => $student){
        if($student['STUDENT_USERNAME'] === $student_username && $student_password === $student['PASSWORD']){
          if($student['APPROVED'] === 1){
            $bit = 1;
            echo json_encode(array('date of issue' => $student['DATE_OF_ISSUE'],'date to be returned' => $student['DATE_TO_BE_RETURNED'])) . "\n";
          }
          break;
        }
      }
    }
   if($bit === 0){
    http_response_code(400);
    echo json_encode(array('result' => 'retrieval unsuccessful','reason' => 'account with given credentials doesnt exist')) . "\n";
   }
  break;
  default:
  http_response_code(405);
  echo json_encode(array('result' => 'retrieval unsuccessful','reason' => 'method not allowed')) . "\n";
}

?>