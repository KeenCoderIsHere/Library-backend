<?php 
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Methods,Content-Type,Access-Control-Allow-Methods,Authorization,X-Requested-With');

include "config.php";
$data = json_decode(file_get_contents("php://input"),true);

$pdo = new PDO('mysql:host=localhost; port=3306; dbname=library','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$statement = $pdo->prepare('SELECT * FROM students');
$statement->execute();
$Students = $statement->fetchAll(PDO::FETCH_ASSOC);
$s = $pdo->prepare('SELECT * FROM admins');
$s->execute();
$Admins = $s->fetchAll(PDO::FETCH_ASSOC);
$data = json_decode(file_get_contents("php://input"),true);
$column = $data['column'] ?? null;
$value = $data['value'] ?? null;
$admin_username = $data['admin_username'] ?? null;
$admin_password = $data['admin_password'] ??  null;
$student_username = $data['student_username'] ?? null;
function checkBook($student_book_name):bool{
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
$val = 0;
foreach($Admins as $i => $admin){
  if($admin['USERNAME'] === $admin_username && $admin_password === $admin['PASSWORD'] && checkUsername($admin_username) && checkPassword($admin_password) && $column && $value)
  $val = 1;
}
$column_array = ['COURSE','DATE_OF_ISSUE','DATE_TO_BE_RETURNED','BOOK_NAME','PASSWORD'];
switch($_SERVER['REQUEST_METHOD']){
  case 'PUT':
    if($val === 1){
      if(in_array(strtoupper($column),$column_array) && checkUsername($student_username)){
        $sql = "UPDATE students SET {$column} = '{$value}' WHERE STUDENT_USERNAME = '{$student_username}'";
        $result = mysqli_query($conn,$sql);
        echo ($result === true) ? json_encode(['result' => 'successfully updated the details']) : json_encode(['result' => 'updation failed','reason' => 'query failed']);
      }
      else{
        http_response_code(400);
        echo json_encode(['result' => 'updation failed','reason' => 'such column doesnt exist / invalid student username']);
      }
    }
    else{
      http_response_code(404);
      echo json_encode(['result' => 'updation failed','reason' => 'invalid admin details / null values given']);
    }
    break;
  default:
    http_response_code(405);
    echo json_encode(['result' => 'updation failed','reason' => 'method not allowed']);
}
?>