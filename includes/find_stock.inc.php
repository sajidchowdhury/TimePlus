<?php 
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    include "autoloader.inc.php";

    $action = new StockContr('Stock-By-Expiry', $product_id);
    $stockData = $action->Report();

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($stockData);
    exit();
}
