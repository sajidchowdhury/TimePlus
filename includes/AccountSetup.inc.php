<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    header('Content-Type: application/json'); // Ensure JSON response

    $related_id = $_POST['related_id'];
    $ledger_id = $_POST['ledger_id'];
    $account_name = $_POST['account_name'];

    // CSRF Protection
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(["status" => "error", "message" => "Invalid CSRF Token!"]);
        exit();
    }

    // Load necessary files
    include "autoloader.inc.php";

    // Create a new customer entry
    $action = new LedgerSetupContr($ledger_id,$related_id, $account_name);
    $result = $action->AccountAction(); 

    // Ensure JSON response
    echo json_encode(["status" => $result['status'], "message" => $result['message']]);
    exit();
}
