<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    header('Content-Type: application/json'); // Ensure JSON response

    $related_id = $_POST['related_id'];
    $bank_name = $_POST['bank_name'];
    $account_no = $_POST['account_no'];

    // CSRF Protection
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(["status" => "error", "message" => "Invalid CSRF Token!"]);
        exit();
    }

    // Load necessary files
    include "autoloader.inc.php";

    // Create a new customer entry
    $action = new BankSetupContr($bank_name, $account_no, $related_id);
    $result = $action->Action(); 

    // Ensure JSON response
    echo json_encode(["status" => $result['status'], "message" => $result['message']]);
    exit();
}
