<?php 

class Purchase extends Dbh {

         use SharedFunctionalityTrait;


    
           protected function CreateData($invoice, $supplier_id, $invoice_date, $cart_data, $user_id) {


    try {
        // Start a transaction
        $pdo = $this->connect();
        if (!$pdo) {
            throw new Exception("Database connection failed.");
        }

        $pdo->beginTransaction(); // ✅ Ensure transaction starts

        // Step 1: Generate new `code`
        $code = $this->generateCode();

        // Step 2: Create `invoice_no`
        $invoice_no = $code . '-' . date("Ymd");

        // Step 3: Insert data into `purchase_invoices`
        $created_date = date("Y-m-d H:i:s");
        $time = time() ; 

        $poster = $_SESSION['admin_access_token'] ?? 'system';
        $total_amount = $this->calculateTotalAmount($cart_data);

        $stmt = $pdo->prepare("INSERT INTO purchase_invoices (code, invoice_no, supplier_id, created_date, sales_person, poster, invoice_date, total_amount,time) 
                                VALUES (:code, :invoice_no, :supplier_id, :created_date, :sales_person, :poster, :invoice_date, :total_amount, :time)");
        $stmt->execute([
            ':code' => $code,
            ':invoice_no' => $invoice_no,
            ':supplier_id' => $supplier_id,
            ':created_date' => $created_date,
            ':sales_person' => "Unknown",
            ':poster' => $poster,
            ':invoice_date' => $invoice_date,
            ':total_amount' => $total_amount,
            ':time' => $time
        ]);

        // Get the last inserted invoice ID
        $invoice_id = $pdo->lastInsertId();

        // Step 4: Insert data into `sales_invoices_items`
        foreach ($cart_data as  $item) {

            $qty = $item['quantity'];
            $rate = $item['price'];
            $total_price = $qty * $rate;
            $product_id = $item['product_id'];
            $expiry_date = $item['expiry_date'];

            $stmt = $pdo->prepare("INSERT INTO purchase_invoices_items (invoice_id, product_id, quantity, price, expiry_date, poster, total_price) 
                                    VALUES (:invoice_id, :product_id, :quantity, :price, :expiry_date , :poster, :total_price)");
            $stmt->execute([
                ':invoice_id' => $invoice_id,
                ':product_id' => $product_id,
                ':quantity' => $qty,
                ':price' => $rate,
                ':expiry_date' => $expiry_date,
                ':poster' => $poster,
                ':total_price' => $total_price
            ]);
        }

    

          // Step 5: Insert transaction and retrieve transaction_id
        $TR = new Transaction();
        $transaction_result = $TR->insertTransaction(
             'credit', 
            $total_amount, 
            4, 
            $supplier_id, 
            'supplier', 
            'Cash',
            'Cash',
            'Product Purchase', 
            $invoice_date,
            $invoice_id,
            $time
        );


          
        if ($transaction_result['status'] !== 'success') {
            throw new Exception("Transaction failed: " . $transaction_result['message']);
        }

        $transaction_id = $transaction_result['transaction_id']; // Retrieve transaction_id



        // Step 6: Update sales_invoices with transaction_id
        $stmt = $pdo->prepare("UPDATE purchase_invoices SET transaction_id = :transaction_id  WHERE id = :invoice_id");
        $stmt->execute([
            ':transaction_id' => $transaction_id,
            ':invoice_id' => $invoice_id
        ]);

        // Step 7: Commit the transaction
        $pdo->commit();


        return ["status" => "success", "message" => "Invoice created successfully" , "invoice_id" => $invoice_id,"transaction_id" => $transaction_id ];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ["status" => "error", "message" => $e->getMessage()];
    }
    
}
    
       protected function UpdateData($invoice_no, $supplier_id, $invoice_date, $cart_data, $user_id, $invoice_id) {



    try {
        $pdo = $this->connect();
        if (!$pdo) throw new Exception("Database connection failed.");

        $pdo->beginTransaction();

        // Fetch the invoice
        $stmt = $pdo->prepare("SELECT * FROM purchase_invoices WHERE id = :invoice_id");
        $stmt->execute([':invoice_id' => $invoice_id]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$invoice) throw new Exception("Invoice with ID {$invoice_id} not found.");

        // Validate cart
        if (!is_array($cart_data) || empty($cart_data)) {
            throw new Exception('Invalid cart data: ' . json_encode($cart_data));
        }

    

        foreach ($cart_data as $item) {

            if (!isset($item['product_id'], $item['quantity'], $item['price'])) {
                throw new Exception("Incomplete item data.");
            }

            $product_id = (int) $item['product_id'];
            $qty =  $item['quantity'];
            $rate =  $item['price'];
            $expiry_date = $item['expiry_date'];
            $total_price = $qty * $rate;

            // Check if product with same expiry already exists
            $checkStmt = $pdo->prepare("
                SELECT id 
                FROM purchase_invoices_items 
                WHERE invoice_id = :invoice_id AND product_id = :product_id AND expiry_date = :expiry_date
            ");
            $checkStmt->execute([
                ':invoice_id' => $invoice_id,
                ':product_id' => $product_id,
                ':expiry_date' => $expiry_date
            ]);
            $existingItem = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($existingItem) && isset($existingItem['id'])) {
                // Update existing item
                $updateStmt = $pdo->prepare("
                    UPDATE purchase_invoices_items 
                    SET quantity = :quantity, price = :price, total_price = :total_price 
                    WHERE id = :id
                ");
                $updateStmt->execute([
                    ':quantity' => $qty,
                    ':price' => $rate,
                    ':total_price' => $total_price,
                    ':id' => $existingItem['id']
                ]);
            } else {

                // Insert new item
                $insertStmt = $pdo->prepare("
                    INSERT INTO purchase_invoices_items 
                    (invoice_id, product_id, quantity, price, expiry_date, poster, total_price) 
                    VALUES (:invoice_id, :product_id, :quantity, :price, :expiry_date, :poster, :total_price)
                ");
                $insertStmt->execute([
                    ':invoice_id' => $invoice_id,
                    ':product_id' => $product_id,
                    ':quantity' => $qty,
                    ':price' => $rate,
                    ':expiry_date' => $expiry_date,
                    ':poster' => $_SESSION['admin_access_token'] ?? 'system',
                    ':total_price' => $total_price
                ]);
            }
        }

        // Recalculate total amount
        $totalStmt = $pdo->prepare("SELECT SUM(total_price) AS total_amount FROM purchase_invoices_items WHERE invoice_id = :invoice_id");
        $totalStmt->execute([':invoice_id' => $invoice_id]);
        $totalRow = $totalStmt->fetch(PDO::FETCH_ASSOC);
        $new_total_amount = $totalRow['total_amount'] ?? 0;

        // Update financial transactions
        $TR = new Transaction();

        // Main transaction update
        $result1 = $TR->updateTransaction(
            'debit',
            $new_total_amount,
            3,
            $supplier_id,
            'supplier',
            'Cash',
            'Cash',
            'Supplier Purchase',
            $invoice_date,
            $invoice['transaction_id']
        );
        if (!$result1 || $result1['status'] === 'error') {
            throw new Exception("Main transaction update failed: " . ($result1['message'] ?? 'Unknown error'));
        }




     // Update invoice
$updateInvoiceStmt = $pdo->prepare("
    UPDATE purchase_invoices 
    SET total_amount = :total_amount, 
        invoice_date = :invoice_date, 
        supplier_id = :supplier_id
    WHERE id = :invoice_id
");

$updateInvoiceStmt->execute([
    ':total_amount' => $new_total_amount,
    ':invoice_date' => $invoice_date,
    ':supplier_id' => $supplier_id,
    ':invoice_id' => $invoice_id
]);

        $pdo->commit();

        return [
            "status" => "success",
            "message" => 'Invoice Update Success',
            "invoice_id" => $invoice_id,
            "transaction_id" => $invoice['transaction_id']
        ];

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
 if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    return [
        "status" => "error",
        "message" => "Error: " . $e->getMessage()
    ];
        }
        return [
            "status" => "error",
            "message" => "Error: " . $e->getMessage()
        ];
    }
}




 public function DeleteItem($item_id) {
    try {
        $pdo = $this->connect();
        if (!$pdo) {
            throw new Exception("Database connection failed.");
        }

        // Start transaction
        $pdo->beginTransaction();





        // Step 1: Get invoice_id and total_price of the item to be deleted
        $stmt = $pdo->prepare('SELECT CONCAT(C.code," " , C.product_description) as ProductName, B.invoice_no,A.invoice_id, A.total_price FROM purchase_invoices_items A 
            JOIN purchase_invoices B ON (A.invoice_id = B.id)
            JOIN setup_product C ON (A.product_id = C.id)  
            WHERE A.id = :item_id');
        $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            throw new Exception($item_id);
        }

        $invoice_id = $item['invoice_id'];
        $item_price = $item['total_price'];


        $mess = "$item[ProductName] deleted from  $item[invoice_no] invoice.Price $item_price ." ;



        // Step 2: Delete the item
        $stmt = $pdo->prepare('DELETE FROM purchase_invoices_items WHERE id = :item_id');
        $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $stmt->execute();

        // Step 3: Recalculate the total amount for the invoice
        $stmt = $pdo->prepare('SELECT SUM(total_price) AS new_total FROM purchase_invoices_items WHERE invoice_id = :invoice_id');
        $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT);
        $stmt->execute();
        $newTotal = $stmt->fetch(PDO::FETCH_ASSOC)['new_total'] ?? 0;

        // Step 4: Update the total amount in purchase_invoices
        $stmt = $pdo->prepare('UPDATE purchase_invoices SET total_amount = :new_total WHERE id = :invoice_id');
        $stmt->execute([
            ':new_total' => $newTotal,
            ':invoice_id' => $invoice_id
        ]);


       $this->logAction('Delete Purchase Invoice Item', $mess);


        // Step 5: Commit transaction
        $pdo->commit();

        return ["status" => "success", "message" => "Item deleted and invoice updated successfully."];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ["status" => "error", "message" => $e->getMessage()];
    }
}



public function DeleteInvoice($invoice_id) {
    try {
        $pdo = $this->connect();
        if (!$pdo) {
            throw new Exception("Database connection failed.");
        }

        // Start transaction
        $pdo->beginTransaction();

        // Step 1: Get invoice_id and total_price of the item to be deleted




        $stmt = $pdo->prepare('SELECT B.supplier_name,A.total_amount,A.invoice_no,A.transaction_id FROM purchase_invoices A  JOIN setup_suppliers B ON (A.supplier_id = B.id ) WHERE A.id = :invoice_id');
        $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            throw new Exception($invoice_id);
        }

        $transaction_id = $item['transaction_id'];

        $mess = "Invoice no $item[invoice_no] deleted with invoice price $item[total_amount] . Supplier Name $item[supplier_name] " ;


        // Step 2: Delete the item
        $stmt = $pdo->prepare('DELETE FROM purchase_invoices_items WHERE invoice_id = :invoice_id');
        $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT);
        $stmt->execute();


if(!empty($item['transaction_id'])){

    $stmt = $pdo->prepare('DELETE FROM account_transaction WHERE id = :transaction_id');
    $stmt->bindParam(':transaction_id', $item['transaction_id'], PDO::PARAM_INT);
    $stmt->execute();


}
     
    $stmt = $pdo->prepare('DELETE FROM purchase_invoices WHERE id = :invoice_id');
    $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT);
    $stmt->execute();


 $this->logAction('Delete Purchase Invoice', $mess);

        // Step 5: Commit transaction
        $pdo->commit();

        return ["status" => "success", "message" => "Invoice deleted successfully."];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ["status" => "error", "message" => $e->getMessage()];
    }
}


    
    private function generateCode() {
        // Get the last `code` from `purchase_invoices`
        $stmt = $this->connect()->query("SELECT code FROM purchase_invoices ORDER BY id DESC LIMIT 1");
        $lastCode = $stmt->fetchColumn();

        if (!$lastCode) {
            return "0001"; // Start from 0001 if no record exists
        }

        // Increment the last code (convert to integer, add 1, then format as 4 digits)
        return str_pad((int)$lastCode + 1, 4, '0', STR_PAD_LEFT);
    }

private function calculateTotalAmount($cart_data) { 
    $total = 0;
    foreach ($cart_data as $item) {
        $total += $item['quantity'] * $item['price'];
    }
    return $total;
}


public function InvoiceItems($invoice_id) {
        $stmt = $this->connect()->prepare('SELECT * FROM purchase_invoices_items WHERE invoice_id = :invoice_id');
        $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT); 
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return is_array($result) ? $result : [];
    }


  public function InvoiceDetails($invoice_id) {
        $stmt = $this->connect()->prepare('SELECT * FROM purchase_invoices WHERE id = :invoice_id');
        $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT); 
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $data ?: null;
    }



    }

    