<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    header('Content-Type: application/json'); // Ensure JSON response

        $related_id = $_POST['related_id'];
        $ledger_id = $_POST['ledger_id'];
        $account_id = $_POST['account_id'];
        $transaction_by = $_POST['transaction_by'];

        $transaction_by_id = $_POST['transaction_by_id'];
        $transaction_type = $_POST['transaction_type'];
        $amount = $_POST['amount'];
        $remarks = $_POST['remarks'];
        $mdate = $_POST['mdate'];
    $prev_mdate = $_POST['prev_mdate'];

       

    // CSRF Protection
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(["status" => "error", "message" => "Invalid CSRF Token!"]);
        exit();
    }

    // Load necessary files
    include "autoloader.inc.php";

    // Create a new customer entry

    $action = new VoucherContr($ledger_id, $account_id, $transaction_by, $transaction_by_id , $transaction_type ,$amount ,  $remarks  , $mdate, $prev_mdate , 
        $related_id);
    $result = $action->Action(); 

    // Ensure JSON response
    echo json_encode(["status" => $result['status'], "message" => $result['message']]);
    exit();
}
