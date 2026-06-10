<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    header('Content-Type: application/json'); // Ensure JSON response

    // Collecting data from the form submission
    $related_id = $_POST['related_id'];
    $supplier_id = $_POST['supplier_id'];
    $pay_now = $_POST['pay_now'];
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
    $action = new SupplierPaymentContr($supplier_id, $pay_now, $remarks, $mdate,$prev_mdate, $transaction_by , $transaction_by_id, $related_id);
    $result = $action->Action(); 

    // Return the result status
    $response = ["status" => $result['status'], "message" => $result['message']];
  echo json_encode($response);
exit;
}
