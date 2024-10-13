<?php 
include "config.php";

$pdo = new PDO('mysql:host=localhost; port=3306; dbname=library','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$s = $pdo->prepare('SELECT * FROM courses');
$s->execute();
$Courses = $s->fetchAll(PDO::FETCH_ASSOC);
$statement = $pdo->prepare('SELECT * FROM students');
$statement->execute();
$Students = $statement->fetchAll(PDO::FETCH_ASSOC);
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
function checkCourse($student_course){
$pdo = new PDO('mysql:host=localhost; port=3306; dbname=library','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);  
$s = $pdo->prepare('SELECT * FROM courses');
$s->execute();
$Courses = $s->fetchAll(PDO::FETCH_ASSOC);
  foreach($Courses as $i => $course){
    if($course['COURSE_NAME'] === $student_course)
    return true;
  }
  return false;
}
$courses = [];
echo "AVAILABLE COURSES \n";
foreach($Courses as $i => $course){
  array_push($courses,$course);
  echo json_encode($course['COURSE_NAME'])."\n";
}


$data = json_decode(file_get_contents("php://input"),true);
$student_username = $data['student_username'] ?? null;
$student_password = $data['student_password'] ?? null;
$student_course = $data['student_course'] ??null;
switch($_SERVER['REQUEST_METHOD']){
  case 'POST':
    if(checkPassword($student_password) && checkUsername($student_username) && checkCourse($student_course)){
      $flag = 0;
      foreach($Students as $i => $student){
        if($student['STUDENT_USERNAME'] === $student_username){
          $flag = 1;
          echo json_encode(array('reason' => 'an account already exists with given username','result' => 'register unsuccessful'));
          http_response_code(409);
          break;
        }
      }
      if($flag === 0){
          $sql = "INSERT INTO students (STUDENT_USERNAME , COURSE , PASSWORD ,APPROVED) VALUES('{$student_username}','{$student_course}','{$student_password}',0)";
          $result = mysqli_query($conn,$sql) or die('SQL query failed!');
          if($result){
            echo json_encode(array('result' =>'signup successful'));
          }
      }
    }
    else{
      http_response_code(422);
      echo json_encode(['result' => 'registration unsuccessful','reason' => 'invalid credentials given']);
    }
    break;
  default:
  http_response_code(405);
  echo json_encode(array('result' => 'sign up unsuccessful','reason' => 'method not allowed'));
}

?>