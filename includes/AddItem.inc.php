<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    header('Content-Type: application/json'); // Ensure JSON response

    $related_id = $_POST['related_id'];
    $code = $_POST['code'];
    $product_group = $_POST['product_group'];
        $product_orgin = $_POST['product_orgin'];

    $product_description = $_POST['product_description'];
    $pack_size = $_POST['pack_size'];
     $sales_price = $_POST['sales_price'];

 
    // CSRF Protection
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(["status" => "error", "message" => "Invalid CSRF Token!"]);
        exit();
    }

    // Load necessary files
    include "autoloader.inc.php";

    // Create a new product entry
    $action = new AddItemContr($code, $product_group, $product_orgin , $product_description, $pack_size, $sales_price, $related_id);
    $result = $action->Action(); 

    // Ensure JSON response
    echo json_encode(["status" => $result['status'], "message" => $result['message']]);
    exit();
}
