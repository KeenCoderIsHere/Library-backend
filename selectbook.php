<?php 
include "config.php";
$pdo = new PDO('mysql:host=localhost; port=3306; dbname=library','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$statement = $pdo->prepare('SELECT * FROM students');
$statement->execute();
$Students = $statement->fetchAll(PDO::FETCH_ASSOC);
$s = $pdo->prepare('SELECT * FROM books');
$s->execute();
$Books = $s->fetchAll(PDO::FETCH_ASSOC);
$books = [];
foreach($Books as $i => $book){
  array_push($books,$book['BOOK_NAME']);
}
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
$flag = 0;$v = 0;$bit = 0;$t = 0;
$data = json_decode(file_get_contents("php://input"),true);
$student_username = $data['student_username'] ?? null;
$student_password = $data['student_password'] ?? null;
$student_book_name = $data['student_book_name'] ?? null;
switch($_SERVER['REQUEST_METHOD']){
  case 'POST':
    if(checkUsername($student_username) && checkPassword($student_password)){
      foreach($Students as $i => $student){
        if($student['APPROVED'] === 0 && $student_username === $student['STUDENT_USERNAME'] && $student_password === $student['PASSWORD']){
          $flag = 1;$t = 1;
          if(in_array($student_book_name,$books)){
            $sql = "UPDATE students SET BOOK_NAME = '{$student_book_name}' WHERE STUDENT_USERNAME = '{$student_username}' AND PASSWORD = '{$student_password}'";
            $result = mysqli_query($conn,$sql) or die('SQL query failed');
            if($result) echo json_encode(['result' => 'selection successful']);
          }
          else{
            http_response_code(403);
            echo json_encode(['result' => 'selection unsuccessful','reason' => 'book name doesnt exist in library']);
          }
        }
        else if($student['APPROVED'] === 1 && $student_username === $student['STUDENT_USERNAME'] && $student_password === $student['PASSWORD']){
          http_response_code(401);
          $t = 1;
          echo json_encode(['result' => 'selection unsuccessful','reason' => 'access denied as you have been approved by the admin and you cannot choose books now']);
        }
      }
      if($t === 0){
        http_response_code(404);
        echo json_encode(['result' => 'selection unsuccessful','reason' => 'invalid credentials']);
    }
    }
    else{
        http_response_code(401);
        echo json_encode(['result' => 'selection unsuccessful','reason' => 'invalid credentials']);
      
    }
    break;
  default:
    http_response_code(405);
    echo json_encode(['result' => 'selection unsucessful','reason' => 'method not allowed']);
}
?>