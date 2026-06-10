<?php


if (isset($_POST['action']) && $_POST['action'] == 'delete_from_cart'  )  {


    $item_id = $_POST['item_id']; // Get product ID from AJAX request
    $delete_type = $_POST['delete_type']; // Get product ID from AJAX request

    include "autoloader.inc.php"; // Include the autoloader for classes


$sales = new SalesReturnContr($delete_type,$item_id);
echo json_encode($sales->DeleteAction()); 
 exit();


}






