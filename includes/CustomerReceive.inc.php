<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    header('Content-Type: application/json'); // Ensure JSON response

    // Collecting data from the form submission
    $related_id = $_POST['related_id'];
    $customer_id = $_POST['customer_id'];
    $discount = $_POST['discount'];
    $receive = $_POST['receive'];
    $remarks = $_POST['remarks'];
    $mdate = $_POST['mdate'];
    $prev_mdate = $_POST['prev_mdate'];


 $transaction_by = $_POST['transaction_by'];
 $transaction_by_id = $_POST['transaction_by_id'];


    // CSRF Protection
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(["status" => "error", "message" => "Invalid CSRF Token!"]);
        exit();
    }

    // Load necessary files
    include "autoloader.inc.php";

    // Create a new customer entry
    $action = new CustomerReceiveContr($customer_id, $discount, $receive, $remarks, $transaction_by,$transaction_by_id ,$mdate, $prev_mdate, $related_id);
    $result = $action->Action(); 

    // Return the result status
    echo json_encode(["status" => $result['status'], "message" => $result['message']]);
    exit();
}
