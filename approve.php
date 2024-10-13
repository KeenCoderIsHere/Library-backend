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

$data = json_decode(file_get_contents("php://input"),true);
$result = false;
$student_username = $data['student_username'] ?? null;
$student_approved = $data['student_approved'] ?? null;
if($student_approved !== 0 && $student_approved !== 1){
  http_response_code(422);
  echo json_encode(array('result' => 'updation unsuccessful','reason' => 'student_approved must be either 0 or 1'));
}
else{
  switch($_SERVER['REQUEST_METHOD']){
    case 'PUT':
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
      if(checkUsername($student_username)){
        $flag = 0;
        foreach($Students as $i => $student){
          if($student['STUDENT_USERNAME'] === $student_username)
            $flag = 1;
            $sql = "UPDATE students SET APPROVED = {$student_approved} WHERE STUDENT_USERNAME = '{$student_username}'";
            $result = mysqli_query($conn,$sql) or die("SQL query failed");
        }
        if($flag === 0){
          http_response_code(404);
          echo json_encode(array('result' => 'updation unsuccessful','reason' => 'no such username found'));
        }
        if($result)
        echo json_encode(array('result' => 'updation successful'));
      }
      break;
      default:
      http_response_code(405);
      
  }  
}

?>