<?php 
include "config.php";
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Methods,Content-Type,Access-Control-Allow-Methods,Authorization,X-Requested-With');

$flag = 0;

$pdo = new PDO('mysql:host=localhost; port=3306; dbname=library','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$statement = $pdo->prepare('SELECT * FROM students');
$statement->execute();
$Students = $statement->fetchAll(PDO::FETCH_ASSOC);
$s = $pdo->prepare('SELECT * FROM admins');
$s->execute();
$Admins = $s->fetchAll(PDO::FETCH_ASSOC);
$data = json_decode(file_get_contents("php://input"),true);

$student_username = $data['student_username'] ?? null;
$student_book_name = $data['student_book_name'] ?? null;
$student_date_to_be_returned = $data['student_date_to_be_returned'] ?? null;
$student_date_of_issue = $data['student_date_of_issue'] ?? null;
$student_course = $data['student_course'] ?? null;
$student_approved = $data['student_approved'] ?? null;
$student_password = $data['student_password'] ?? null;

$admin_username = mysqli_real_escape_string($conn,$data['admin_username']) ?? null;

$admin_password = mysqli_real_escape_string($conn,$data['admin_password']) ?? null;

function valid(...$credentials):bool{
  for($i = 0; $i < count($credentials); $i++){
    if($credentials[$i] === null && isset($credentials[$i]) === false)
    return false;
  }
  return true;
}

function checkBook($student_book_name):bool
{
  $pdo = new PDO('mysql:host=localhost; port=3306; dbname=library','root','');
  $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $statement = $pdo->prepare('SELECT * FROM books');
  $statement->execute();
  $Books = $statement->fetchAll(PDO::FETCH_ASSOC);
  foreach($Books as $i => $book){
    if($book['BOOK_NAME'] === $student_book_name)
    return true;
  }
  return false;
}

function checkCourse($student_course):bool{
  $pdo = new PDO('mysql:host=localhost; port=3306; dbname=library','root','');
  $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $statement = $pdo->prepare('SELECT * FROM courses');
  $statement->execute();
  $Courses = $statement->fetchAll(PDO::FETCH_ASSOC);
  foreach($Courses as $i => $course){
    if($course['COURSE_NAME'] === $student_course)
    return true;
  }
  return false;
}
$not = 0;
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
switch($_SERVER['REQUEST_METHOD']){
  case 'POST':
    if(checkUsername($student_username) && checkPassword($student_password) && checkBook($student_book_name) && checkCourse($student_course) && ($student_date_to_be_returned !== null) && ($student_date_of_issue !== null) && ($student_approved !== null) && checkUsername($admin_username) && checkPassword($admin_password)){
      foreach($Admins as $i => $admin){
        if($admin['PASSWORD'] === $admin_password && $admin['USERNAME'] === $admin_username){
          $admin_valid = 1;
          foreach($Students as $i => $student){
            if($student['STUDENT_USERNAME'] === $student_username)
            $not = 1;
          }
        }
      }
      if($admin_valid === 1 && $not === 0){
        $sql = "INSERT INTO students(STUDENT_USERNAME , BOOK_NAME , DATE_TO_BE_RETURNED , DATE_OF_ISSUE , COURSE , APPROVED, PASSWORD) VALUES ('{$student_username}','{$student_book_name}','{$student_date_to_be_returned}','{$student_date_of_issue}','{$student_course}',{$student_approved},'{$student_password}')";
        if(mysqli_query($conn,$sql)){
          echo json_encode(array('result' => 'student record insertion successful'));
        }
        else{
          http_response_code(400);
          echo json_encode(array('result' => 'student record insertion unsuccessful','reason' => 'SQL query failed'));
        }
      }
      else{
        http_response_code(409);
        echo json_encode(['result' => 'insertion successful','reason' => 'there already exists an account with the given username']);
      }
    }
      else{
        echo json_encode(array('result' => 'insertion unsuccessful','reason' => 'invalid credentials'));
        http_response_code(422);
      }
      

    break;
  default:
    http_response_code(405);
    echo json_encode(['result' => 'insertion unsuccesful','reason' => 'method not allowed']);
}

?>