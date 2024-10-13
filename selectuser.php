<?php 
header('Content-Type: application/json');

header('Access-Control-Allow-Origin: *');

include "config.php";

$data = json_decode(file_get_contents("php://input"),true);

$user = $data['user_type'] ?? null;
switch($_SERVER['REQUEST_METHOD']){
  case 'POST':
    if(strtolower($user) === 'student'){
      echo json_encode(array('user' => $user));
    }
    else if(strtolower($user) === 'admin'){
      echo json_encode(array('user' => $user));
    }
    else{
      echo json_encode(['result'=>'user selection failed','reason'=>'invalid user selected']);
    }
    break;
  default:
    http_response_code(405);
    echo json_encode(['result' => 'user selection failed','reason' => 'method not allowed']);
}

?>