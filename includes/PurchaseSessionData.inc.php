<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['admin_access_token'])) {
    // Redirect to login if user is not logged in
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['admin_access_token']; // Safe to use

// Now you can proceed to access the session data specific to the user
if (!isset($_SESSION['cart'][$user_id])) {
    $_SESSION['cart'][$user_id] = []; // Initialize the cart if it doesn't exist
}


if (isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {



    $product_id = $_POST['product_id'];  // Get product ID from AJAX request
    $qty = $_POST['qty'];                // Get quantity from AJAX request
    $rate = $_POST['rate'];              // Get purchase rate from AJAX request

    $invoice = $_POST['invoice'];            
    $related_id = $_POST['related_id'];            
    $supplier_id = $_POST['supplier_id'];   
    $prev_invoice_date = $_POST['prev_invoice_date'];            
    $invoice_date = $_POST['invoice_date'];  
    $expiry_date = $_POST['expiry_date'];  


if($related_id  == 'New'){


    $product_key = $product_id . '___' . $expiry_date;


    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['cart'][$user_id])) {
        $_SESSION['cart'][$user_id] = [];
    }

    // Check if product already exists in cart
    if (!isset($_SESSION['cart'][$user_id][$product_key])) {



        $_SESSION['cart'][$user_id][$product_key] = [

            'product_id' => $product_id,
            'quantity' => $qty,
            'expiry_date' => $expiry_date,
            'price' => $rate
        ];



        echo json_encode(['status' => 'success', 'message' => 'Item added to cart.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item already exists in cart.']);
    }

}else{




    $cart_data = [
        [
            'product_id' => $product_id,
            'quantity' => $qty,
            'expiry_date' => $expiry_date,
            'price' => $rate,
            'invoice_id' => $related_id ,
            'supplier_id' => $supplier_id,

        ]
    ];

    include "autoloader.inc.php"; // Include the autoloader for classes


    $purchase = new PurchaseContr($related_id, $invoice, $supplier_id, $prev_invoice_date,  $invoice_date , $cart_data, $user_id);
    echo $result = json_encode($purchase->Action()); 

    exit();




}


}


if (isset($_POST['action']) && $_POST['action'] == 'delete_from_cart') {




    $item_id = $_POST['item_id']; // Get product ID from AJAX request
    $invoice_id = $_POST['invoice_id']; 
    


if($invoice_id  == 'New'){

 // Check if the product exists in the cart
    if (isset($_SESSION['cart'][$user_id][$item_id])) {
        unset($_SESSION['cart'][$user_id][$item_id]); // Remove the product from the session cart
        echo json_encode(['status' => 'success', 'message' => 'Item removed from cart.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found in cart.']);
    }


}else{

    include "autoloader.inc.php"; // Include the autoloader for classes


$purchase = new PurchaseContr($item_id);
echo json_encode($purchase->DeleteAction()); 
 exit();

     

}
  

}


if (isset($_POST['action']) && $_POST['action'] == 'delete_invoice') {


$invoice_id = $_POST['related_id'];


include "autoloader.inc.php"; // Include the autoloader for classes


$purchase = new PurchaseContr($invoice_id);
echo json_encode($purchase->DeleteFullInvoice()); 
 exit();

    

}


if (isset($_POST['action']) && $_POST['action'] == 'submit_cart') {



    $invoice = $_POST['invoice'];          // Get invoice number from form
    $supplier_id = 1;   // Get supplier name from form
    $user_id = $_SESSION['admin_access_token']; // Get logged-in user ID
    $related_id = $_POST['related_id']; 
    $prev_invoice_date = $_POST['prev_invoice_date'];            
    $invoice_date = $_POST['invoice_date'];  



if($related_id == 'New' ){

    if (!isset($_SESSION['cart'][$user_id]) || empty($_SESSION['cart'][$user_id])) {
        echo json_encode(['status' => 'error', 'message' => 'No items in the cart.']);
        exit;
    }
  
    // Send cart data to the controller
    $cart_data = $_SESSION['cart'][$user_id];


}else{


    if (!isset($_SESSION['purchase_edit_cart'][$user_id]) || empty($_SESSION['purchase_edit_cart'][$user_id])) {
        echo json_encode(['status' => 'error', 'message' => 'No items in the cart.']);
        exit;
    }
  
    // Send cart data to the controller
    $cart_data = $_SESSION['purchase_edit_cart'][$user_id];



}



    include "autoloader.inc.php"; // Include the autoloader for classes
 


        $purchase = new PurchaseContr($related_id, $invoice, $supplier_id, $prev_invoice_date,$invoice_date, $cart_data,$user_id);

        unset($_SESSION['cart'][$user_id]);
        echo $result = json_encode($purchase->Action()); 
        exit();


}

