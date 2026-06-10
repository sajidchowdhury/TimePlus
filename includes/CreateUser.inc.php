<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    header('Content-Type: application/json'); // Ensure JSON response

    $related_id = $_POST['related_id'];
    $employee_name = $_POST['employee_name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $user_type = $_POST['user_type'];
     $password = $_POST['password'];

    $block_user = $_POST['block_user'];
    $PageName = $_POST['PageName'];


    $login_start = $_POST['login_start'];
    $login_end = $_POST['login_end'];


    // CSRF Protection
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(["status" => "error", "message" => "Invalid CSRF Token!"]);
        exit();
    }

    // Load necessary files
    include "autoloader.inc.php";

    // Create a new user
    $action = new CreateUserContr($employee_name, $email, $related_id, $mobile, $user_type, $block_user,$password,$PageName,$login_start,$login_end);
    $result = $action->Action(); 

    // Ensure JSON response
    echo json_encode(["status" => $result['status'], "message" => $result['message']]);
    exit();
}
