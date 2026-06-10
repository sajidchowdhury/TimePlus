<?php 

include "autoloader.inc.php";

$action = new Dashboard();

// Set response as JSON
header('Content-Type: application/json');

// Combine both responses into a single JSON object
$response = [
    "salesData" => $action->getSalesData(),
    "transactionSummary" => $action->getTodayTransactionSummary()
];

// Debugging: Print raw response before encoding
error_log(print_r($response, true)); // Logs output to server log

echo json_encode($response);
