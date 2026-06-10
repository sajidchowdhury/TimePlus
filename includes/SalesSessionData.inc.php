<?php

session_start();

// Check if the user is logged in
if (!isset($_SESSION['admin_access_token'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['admin_access_token'];

// Initialize cart if not exists
if (!isset($_SESSION['sales_cart'][$user_id])) {
    $_SESSION['sales_cart'][$user_id] = [];
}

if (isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {

    $product_id = $_POST['product_id']; 
    $qty        = $_POST['qty'];               
    $rate       = $_POST['rate'];        
    $invoice    = $_POST['invoice'];            
    $customer_id = $_POST['customer_id'];       
    $user_id_post = $_POST['user_id'];            
    $prev_invoice_date = $_POST['prev_invoice_date'];            
    $invoice_date = $_POST['invoice_date'];            
    $expiry_date  = $_POST['product_stock_select']; 
    $product_stock = $_POST['selected_stock']; 
    $related_id = $_POST['related_id']; 
    $sales_person = $_POST['sales_person'];                 
    $discount = $_POST['discount']; 
    $receive_now = $_POST['receive_now'];                 
    $transaction_by_id = $_POST['transaction_by_id'];      

    // Basic validation
    if ($product_id == '' || $qty == '' || $rate == '') {   
        echo json_encode(['status' => 'error', 'message' => 'Fill up all data']);
        exit;
    }

    // Stock check
    if ($product_stock < $qty) {
        echo json_encode(['status' => 'error', 'message' => 'Not enough in stock']);
        exit;
    }

    if ($related_id == 'New') {

        $product_key = $product_id . '___' . $expiry_date;

        if (!isset($_SESSION['sales_cart'][$user_id][$product_key])) {
            $_SESSION['sales_cart'][$user_id][$product_key] = [
                'product_id' => $product_id,
                'expiry_date' => $expiry_date,
                'quantity' => $qty,
                'price' => $rate
            ];
            echo json_encode(['status' => 'success', 'message' => 'Item added to cart.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Item with same expiry already exists.']);
        }
        exit();

    } else {
        // Edit mode - direct update
        $cart_data = [[
            'product_id' => $product_id,
            'quantity'   => $qty,
            'price'      => $rate,
            'expiry_date'=> $expiry_date,
            'invoice_id' => $related_id 
        ]];

        include "autoloader.inc.php";

        $sales = new SalesContr(
            $related_id, 
            $invoice, 
            $customer_id, 
            $receive_now,
            $transaction_by_id, 
            $sales_person, 
            $prev_invoice_date,  
            $invoice_date,
            $discount,
            0.00,           // adjustment = 0 for single item add
            $cart_data, 
            $user_id
        );

        echo json_encode($sales->Action());
        exit();
    }
}

// ===================== DELETE =====================
if (isset($_POST['action']) && $_POST['action'] == 'delete_from_cart') {

    $item_id = $_POST['item_id'];
    $invoice_id = $_POST['invoice_id']; 

    if ($invoice_id == 'New') {
        if (isset($_SESSION['sales_cart'][$user_id][$item_id])) {
            unset($_SESSION['sales_cart'][$user_id][$item_id]);
            echo json_encode(['status' => 'success', 'message' => 'Item removed from cart.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Item not found in cart.']);
        }
    } else {
        include "autoloader.inc.php";
        $sales = new SalesContr($item_id);
        echo json_encode($sales->DeleteAction()); 
    }
    exit();
}

// ===================== DELETE FULL INVOICE =====================
if (isset($_POST['action']) && $_POST['action'] == 'delete_invoice') {
    $invoice_id = $_POST['related_id'];
    include "autoloader.inc.php";
    $sales = new SalesContr($invoice_id);
    echo json_encode($sales->DeleteFullInvoice()); 
    exit();
}

// ===================== SUBMIT CART =====================
if (isset($_POST['action']) && $_POST['action'] == 'submit_cart') {

    $invoice           = $_POST['invoice'];
    $customer_id       = $_POST['customer_id'];
    $related_id        = $_POST['related_id']; 
    $prev_invoice_date = $_POST['prev_invoice_date']; 
    $invoice_date      = $_POST['invoice_date']; 
    $discount          = $_POST['discount']; 
    $sales_person      = $_POST['sales_person'];                 
    $receive_now       = $_POST['receive_now'];                 
    $transaction_by_id = $_POST['transaction_by_id'];                 
    $adjustment        = isset($_POST['adjustment']) ? (float)$_POST['adjustment'] : 0.00;

    if ($related_id == 'New') {
        if (!isset($_SESSION['sales_cart'][$user_id]) || empty($_SESSION['sales_cart'][$user_id])) {
            echo json_encode(['status' => 'error', 'message' => 'No items in the cart.']);
            exit;
        }
        $cart_data = $_SESSION['sales_cart'][$user_id];
    } else {
        if (!isset($_SESSION['sales_edit_cart'][$user_id]) || empty($_SESSION['sales_edit_cart'][$user_id])) {
            echo json_encode(['status' => 'error', 'message' => 'No items in the cart.']);
            exit;
        }
        $cart_data = $_SESSION['sales_edit_cart'][$user_id];
    }

    include "autoloader.inc.php";

    $contr = new SalesContr(
        $related_id,
        $invoice,
        $customer_id,
        $receive_now,
        $transaction_by_id,
        $sales_person,
        $prev_invoice_date,
        $invoice_date,
        $discount,
        $adjustment,           // ← Correctly passed
        $cart_data,
        $user_id
    );

    $result = $contr->Action();

    // Clear session cart after successful submit
    if ($related_id == 'New') {
        unset($_SESSION['sales_cart'][$user_id]);
    } else {
        unset($_SESSION['sales_edit_cart'][$user_id]);
    }

    echo json_encode($result);
    exit();
}