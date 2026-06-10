<?php 

class Sales extends Dbh {
 
        use SharedFunctionalityTrait;



protected function CreateData($invoice, $customer_id, $receive_now, $transaction_by_id, $sales_person, $invoice_date, $discount, $adjustment, $cart_data, $user_id) {
    try {
        $pdo = $this->connect();
        if (!$pdo) {
            throw new Exception("Database connection failed.");
        }

        $pdo->beginTransaction();

        // Basic setup
        $code = $this->generateCode();
        $invoice_no = date("Ymd") . '-' . $code;
        $created_date = date("Y-m-d H:i:s");
        $time = time();
        $poster = $_SESSION['admin_access_token'] ?? 'system';
        
        $total_amount = $this->calculateTotalAmount($cart_data);
        $discount_amount = ($total_amount * $discount) / 100;
        $final_invoice_amount = $total_amount - $discount_amount - $adjustment;

        // Step 1: Insert main invoice
        $stmt = $pdo->prepare("INSERT INTO sales_invoices 
            (code, invoice_no, customer_id, created_date, sales_person, poster, invoice_date, 
             discount, adjustment, total_amount, final_amount, time) 
            VALUES 
            (:code, :invoice_no, :customer_id, :created_date, :sales_person, :poster, 
             :invoice_date, :discount, :adjustment, :total_amount, :final_amount, :time )");

        $stmt->execute([
            ':code'              => $code,
            ':invoice_no'        => $invoice_no,
            ':customer_id'       => $customer_id,
            ':created_date'      => $created_date,
            ':sales_person'      => $sales_person,
            ':poster'            => $poster,
            ':invoice_date'      => $invoice_date,
            ':discount'          => (float)$discount,
            ':adjustment'        => (float)$adjustment,
            ':total_amount'      => $total_amount,
            ':final_amount'      => $final_invoice_amount,
            ':time'              => $time
        ]);

        $invoice_id = $pdo->lastInsertId();

        // Step 2: Insert items
        if (!is_array($cart_data) || empty($cart_data)) {
            throw new Exception("Cart data is empty or invalid.");
        }

        foreach ($cart_data as $item) {
            $qty         = (float)($item['quantity'] ?? 0);
            $rate        = (float)($item['price'] ?? 0);
            $total_price = $qty * $rate;
            $product_id  = $item['product_id'];
            $expiry_date = $item['expiry_date'] ?? null;

            $stmt = $pdo->prepare("INSERT INTO sales_invoices_items 
                (invoice_id, product_id, quantity, price, expiry_date, poster, total_price) 
                VALUES (:invoice_id, :product_id, :quantity, :price, :expiry_date, :poster, :total_price)");

            $stmt->execute([
                ':invoice_id'   => $invoice_id,
                ':product_id'   => $product_id,
                ':quantity'     => $qty,
                ':price'        => $rate,
                ':expiry_date'  => $expiry_date,
                ':poster'       => $poster,
                ':total_price'  => $total_price
            ]);
        }

        $TR = new Transaction();

        // Main Transaction
        $transaction_result = $TR->insertTransaction('debit', $total_amount, 3, $customer_id, 'customer', 'Cash', 'Cash', 'Customer Purchase', $invoice_date, $invoice_id, $time);
        if ($transaction_result['status'] !== 'success') {
            throw new Exception("Main transaction failed: " . $transaction_result['message']);
        }
        $transaction_id = $transaction_result['transaction_id'];

        // Discount Transaction
        $discount_transaction_id = null;
        if ($discount > 0) {
            $dis_amount = $total_amount * ($discount / 100);
            $disTR = new Transaction();
            $discount_result = $disTR->insertTransaction('debit', $dis_amount, 2, $customer_id, 'customer', 'Cash', 'Cash', 'Discount', $invoice_date, $invoice_id, $time);
            if ($discount_result['status'] !== 'success') {
                throw new Exception("Discount transaction failed: " . $discount_result['message']);
            }
            $discount_transaction_id = $discount_result['transaction_id'];
        }

        // Adjustment Transaction
        $adjustment_transaction_id = null;
        if ($adjustment > 0) {
            $adjTR = new Transaction();
            $adj_result = $adjTR->insertTransaction(
                'debit', 
                $adjustment, 
                2, 
                $customer_id, 
                'customer', 
                'Cash', 
                'Cash', 
                'Extra Discount / Adjustment', 
                $invoice_date, 
                $invoice_id, 
                $time
            );
            if ($adj_result['status'] !== 'success') {
                throw new Exception("Adjustment transaction failed: " . $adj_result['message']);
            }
            $adjustment_transaction_id = $adj_result['transaction_id'];
        }

        // Receive Now Transaction
        $receive_now_id = null;
        if ($receive_now > 0) {
            $transaction_by = ($transaction_by_id == 0) ? 'Cash' : 'Bank';
            $trans_by_id    = ($transaction_by_id == 0) ? null : $transaction_by_id;

            $reVnow = new Transaction();
            $reVnow_result = $reVnow->insertTransaction(
                'credit', $receive_now, 1, $customer_id, 'customer', 
                $transaction_by, $trans_by_id, 'Invoice wise receive', 
                $invoice_date, $invoice_id, $time
            );

            if ($reVnow_result['status'] !== 'success') {
                throw new Exception("Receive transaction failed: " . $reVnow_result['message']);
            }
            $receive_now_id = $reVnow_result['transaction_id'];
        }

        // Final Update with all transaction IDs
        $stmt = $pdo->prepare("UPDATE sales_invoices SET 
            transaction_id = :transaction_id,
            discount_transaction_id = :discount_transaction_id,
            adjustment_transaction_id = :adjustment_transaction_id,
            receive_now_id = :receive_now_id 
            WHERE id = :invoice_id");

        $stmt->execute([
            ':transaction_id'          => $transaction_id,
            ':discount_transaction_id' => $discount_transaction_id,
            ':adjustment_transaction_id'=> $adjustment_transaction_id,
            ':receive_now_id'          => $receive_now_id,
            ':invoice_id'              => $invoice_id
        ]);

        $pdo->commit();

        return [
            "status"     => "success",
            "message"    => "Invoice created successfully",
            "invoice_id" => $invoice_id,
            "invoice_no" => $invoice_no
        ];

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ["status" => "error", "message" => $e->getMessage()];
    }
}

protected function UpdateData($invoice_no, $customer_id, $receive_now, $transaction_by_id, $sales_person, $invoice_date, $discount, $adjustment, $cart_data, $user_id, $invoice_id) {

    try {
        $pdo = $this->connect();
        if (!$pdo) throw new Exception("Database connection failed.");

        $pdo->beginTransaction();

        // Fetch current invoice data
        $stmt = $pdo->prepare("SELECT * FROM sales_invoices WHERE id = :invoice_id");
        $stmt->execute([':invoice_id' => $invoice_id]);
        $oldInvoice = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$oldInvoice) {
            throw new Exception("Invoice with ID {$invoice_id} not found.");
        }

        // Validate cart
        if (!is_array($cart_data) || empty($cart_data)) {
            throw new Exception('Cart data is empty or invalid.');
        }

        // Step 1: Update / Insert Items
        foreach ($cart_data as $item) {
            $qty         = (float)($item['quantity'] ?? 0);
            $rate        = (float)($item['price'] ?? 0);
            $total_price = $qty * $rate;
            $product_id  = $item['product_id'];
            $expiry_date = $item['expiry_date'] ?? null;

            // Check if item already exists
            $checkStmt = $pdo->prepare("
                SELECT id FROM sales_invoices_items 
                WHERE invoice_id = :invoice_id AND product_id = :product_id AND expiry_date = :expiry_date
            ");
            $checkStmt->execute([
                ':invoice_id' => $invoice_id,
                ':product_id' => $product_id,
                ':expiry_date' => $expiry_date
            ]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Update existing item
                $updateStmt = $pdo->prepare("
                    UPDATE sales_invoices_items 
                    SET quantity = :quantity, price = :price, total_price = :total_price 
                    WHERE id = :id
                ");
                $updateStmt->execute([
                    ':quantity' => $qty,
                    ':price' => $rate,
                    ':total_price' => $total_price,
                    ':id' => $existing['id']
                ]);
            } else {
                // Insert new item
                $insertStmt = $pdo->prepare("
                    INSERT INTO sales_invoices_items 
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

        // Recalculate total amount from items
        $totalStmt = $pdo->prepare("SELECT SUM(total_price) AS total_amount FROM sales_invoices_items WHERE invoice_id = :invoice_id");
        $totalStmt->execute([':invoice_id' => $invoice_id]);
        $totalRow = $totalStmt->fetch(PDO::FETCH_ASSOC);
        $new_total_amount = (float)($totalRow['total_amount'] ?? 0);

        $discount_amount = ($new_total_amount * $discount) / 100;
        $final_amount = $new_total_amount - $discount_amount - $adjustment;

        $TR = new Transaction();

        // Update Main Sales Transaction
        $result1 = $TR->updateTransaction(
            'debit', $new_total_amount, 3, $customer_id, 'customer', 'Cash', 'Cash',
            'Customer Purchase', $invoice_date, $oldInvoice['transaction_id']
        );
        if (!$result1 || $result1['status'] === 'error') {
            throw new Exception("Main transaction update failed");
        }

        // Handle Discount Transaction
        $discount_transaction_id = $oldInvoice['discount_transaction_id'] ?? null;
        if ($discount > 0) {
            $dis_amount = $new_total_amount * ($discount / 100);
            if ($discount_transaction_id) {
                $TR->updateTransaction('debit', $dis_amount, 2, $customer_id, 'customer', 'Cash', 'Cash',
                    'Discount', $invoice_date, $discount_transaction_id);
            } else {
                $disResult = $TR->insertTransaction('debit', $dis_amount, 2, $customer_id, 'customer', 'Cash', 'Cash',
                    'Discount', $invoice_date, $invoice_id, time());
                $discount_transaction_id = $disResult['transaction_id'] ?? null;
            }
        } else {
            $discount_transaction_id = null; // remove if discount becomes 0
        }

        // Handle Adjustment Transaction
        $adjustment_transaction_id = $oldInvoice['adjustment_transaction_id'] ?? null;
        if ($adjustment > 0) {
            if ($adjustment_transaction_id) {
                $TR->updateTransaction('debit', $adjustment, 2, $customer_id, 'customer', 'Cash', 'Cash',
                    'Extra Discount / Adjustment', $invoice_date, $adjustment_transaction_id);
            } else {
                $adjResult = $TR->insertTransaction('debit', $adjustment, 2, $customer_id, 'customer', 'Cash', 'Cash',
                    'Extra Discount / Adjustment', $invoice_date, $invoice_id, time());
                $adjustment_transaction_id = $adjResult['transaction_id'] ?? null;
            }
        } else {
            $adjustment_transaction_id = null;
        }

        // Handle Receive Now Transaction
        $receive_now_id = $oldInvoice['receive_now_id'] ?? null;
        if ($receive_now > 0) {
            $transaction_by = ($transaction_by_id == 0) ? 'Cash' : 'Bank';
            $trans_by_id = ($transaction_by_id == 0) ? null : $transaction_by_id;

            if ($receive_now_id) {
                $TR->updateTransaction('credit', $receive_now, 1, $customer_id, 'customer',
                    $transaction_by, $trans_by_id, 'Invoice wise receive', $invoice_date, $receive_now_id);
            } else {
                $recResult = $TR->insertTransaction('credit', $receive_now, 1, $customer_id, 'customer',
                    $transaction_by, $trans_by_id, 'Invoice wise receive', $invoice_date, $invoice_id, time());
                $receive_now_id = $recResult['transaction_id'] ?? null;
            }
        } else {
            $receive_now_id = null;
        }

        // Update main invoice record
        $updateStmt = $pdo->prepare("
            UPDATE sales_invoices 
            SET total_amount = :total_amount,
                final_amount = :final_amount,
                discount = :discount,
                adjustment = :adjustment,
                invoice_date = :invoice_date,
                customer_id = :customer_id,
                sales_person = :sales_person,
                discount_transaction_id = :discount_transaction_id,
                adjustment_transaction_id = :adjustment_transaction_id,
                receive_now_id = :receive_now_id,
                created_date = NOW()
            WHERE id = :invoice_id
        ");

        $updateStmt->execute([
            ':total_amount'            => $new_total_amount,
            ':final_amount'            => $final_amount,
            ':discount'                => (float)$discount,
            ':adjustment'              => (float)$adjustment,
            ':invoice_date'            => $invoice_date,
            ':customer_id'             => $customer_id,
            ':sales_person'            => $sales_person,
            ':discount_transaction_id' => $discount_transaction_id,
            ':adjustment_transaction_id'=> $adjustment_transaction_id,
            ':receive_now_id'          => $receive_now_id,
            ':invoice_id'              => $invoice_id
        ]);

        $pdo->commit();

        return [
            "status"     => "success",
            "message"    => "Invoice updated successfully",
            "invoice_id" => $invoice_id
        ];

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ["status" => "error", "message" => $e->getMessage()];
    }
}




   public function DeleteItem($item_id) {
    $pdo = null;

    try {
        // Step 1: Connect to the database
        $pdo = $this->connect();
        if (!$pdo) {
            throw new Exception("Database connection failed.");
        }

        // Step 2: Start transaction
        $pdo->beginTransaction();

        // Step 3: Fetch item and invoice info
        $stmt = $pdo->prepare("
            SELECT 
                CONCAT(C.code, ' ', C.product_description) AS ProductName,
                B.invoice_no,
                B.transaction_id,
                A.invoice_id,
                A.total_price 
            FROM sales_invoices_items A
            JOIN sales_invoices B ON A.invoice_id = B.id
            JOIN setup_product C ON A.product_id = C.id
            WHERE A.id = :item_id
        ");
        if (!$stmt->execute([':item_id' => $item_id])) {
            throw new Exception("Failed to fetch item info.");
        }

        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$item) {
            throw new Exception("Item with ID $item_id not found.");
        }

        $invoice_id = (int)$item['invoice_id'];
        $item_price = (float)$item['total_price'];
        $product_name = $item['ProductName'];
        $invoice_no = $item['invoice_no'];
        $log_message = "$product_name deleted from invoice $invoice_no.";

        // Step 4: Delete the item
        $stmt = $pdo->prepare("DELETE FROM sales_invoices_items WHERE id = :item_id");
        if (!$stmt->execute([':item_id' => $item_id])) {
            throw new Exception("Failed to delete item from invoice.");
        }

        // Step 5: Recalculate invoice total
        $stmt = $pdo->prepare("SELECT SUM(total_price) AS new_total FROM sales_invoices_items WHERE invoice_id = :invoice_id");
        if (!$stmt->execute([':invoice_id' => $invoice_id])) {
            throw new Exception("Failed to recalculate invoice total.");
        }

        $new_total = $stmt->fetchColumn();
        $new_total = $new_total ? (float)$new_total : 0;

        // Step 6: Update invoice total
        $stmt = $pdo->prepare("UPDATE sales_invoices SET total_amount = :new_total WHERE id = :invoice_id");
        if (!$stmt->execute([':new_total' => $new_total, ':invoice_id' => $invoice_id])) {
            throw new Exception("Failed to update invoice total.");
        }


    // Step 6: Update transaction
        $stmt = $pdo->prepare("UPDATE account_transaction SET amount = :new_total WHERE id = :transaction_id");
        if (!$stmt->execute([':new_total' => $new_total, ':transaction_id' => $item['transaction_id']])) {
            throw new Exception("Failed to update invoice total.");
        }

       

        // Step 7: Log the action
        if (!$this->logAction('Delete Sales Invoice Item', $log_message)) {
            throw new Exception("Failed to log delete action.");
        }

        // Step 8: Commit the transaction
        $pdo->commit();

        return ["status" => "success", "message" => "Item deleted and invoice updated successfully."];

    } catch (Exception $e) {
        if ($pdo && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ["status" => "error", "message" => "Error: " . $e->getMessage()];
    }
}




public function DeleteInvoice($invoice_id) {
    $pdo = null;

    try {
        // Step 1: Connect to the database
        $pdo = $this->connect();
        if (!$pdo) {
            throw new Exception("Database connection failed.");
        }

        // Step 2: Begin transaction
        $pdo->beginTransaction();

        // Step 3: Fetch invoice details
        $stmt = $pdo->prepare("
            SELECT 
                B.customer_name, 
                A.total_amount, 
                A.invoice_no, 
                A.transaction_id, 
                A.discount_transaction_id 
            FROM sales_invoices A 
            JOIN setup_customer B ON A.customer_id = B.id 
            WHERE A.id = :invoice_id
        ");
        if (!$stmt->execute([':invoice_id' => $invoice_id])) {
            throw new Exception("Failed to fetch invoice data.");
        }

        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$invoice) {
            throw new Exception("Invoice not found with ID: $invoice_id");
        }

        $log_message = "Invoice no {$invoice['invoice_no']} deleted with amount {$invoice['total_amount']}. Customer: {$invoice['customer_name']}";

        // Step 4: Delete related items
        $stmt = $pdo->prepare("DELETE FROM sales_invoices_items WHERE invoice_id = :invoice_id");
        if (!$stmt->execute([':invoice_id' => $invoice_id])) {
            throw new Exception("Failed to delete invoice items.");
        }

        // Step 5: Delete customer payment transaction (if exists)
        if (!empty($invoice['transaction_id'])) {
            $stmt = $pdo->prepare("DELETE FROM account_transaction WHERE id = :transaction_id");
            if (!$stmt->execute([':transaction_id' => $invoice['transaction_id']])) {
                throw new Exception("Failed to delete related transaction.");
            }
        }

        // Step 6: Delete discount transaction (if exists)
        if (!empty($invoice['discount_transaction_id'])) {
            $stmt = $pdo->prepare("DELETE FROM account_transaction WHERE id = :discount_transaction_id");
            if (!$stmt->execute([':discount_transaction_id' => $invoice['discount_transaction_id']])) {
                throw new Exception("Failed to delete discount transaction.");
            }
        }

        // Step 7: Delete the invoice
        $stmt = $pdo->prepare("DELETE FROM sales_invoices WHERE id = :invoice_id");
        if (!$stmt->execute([':invoice_id' => $invoice_id])) {
            throw new Exception("Failed to delete invoice.");
        }

        // Step 8: Log the deletion
        if (!$this->logAction('Delete Sales Invoice', $log_message)) {
            throw new Exception("Failed to log invoice deletion.");
        }

        // Step 9: Commit transaction
        $pdo->commit();

        return ["status" => "success", "message" => "Invoice deleted successfully."];

    } catch (Exception $e) {
        if ($pdo && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ["status" => "error", "message" => "Error: " . $e->getMessage()];
    }
}




    private function generateCode() {
        // Get the last `code` from `sales_invoices`
        $stmt = $this->connect()->query("SELECT code FROM sales_invoices ORDER BY id DESC LIMIT 1");
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
        $stmt = $this->connect()->prepare('SELECT A.*,sum(quantity) as Totalquantity,B.product_orgin,B.code,B.product_description,B.pack_size FROM sales_invoices_items A JOIN setup_product B ON (A.product_id = B.id) WHERE A.invoice_id = :invoice_id GROUP BY A.product_id' );
        $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT); 
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return is_array($result) ? $result : [];
    }



  public function AllInvoice() {
      
           $pdo = $this->connect();

        $stmt = $this->connect()->prepare('SELECT A.*  FROM sales_invoices A ');
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) { 
            
            $b = $row['invoice_no'];
            $c = explode('-',$b);
$InvoiceNo = $c[1] .'-' . $c[0];
$id = $row['id'];
$updateInvoiceStmt = $pdo->prepare("
    UPDATE sales_invoices 
    SET InvoiceNo = :InvoiceNo
    WHERE id = :id
");
$updateInvoiceStmt->execute([
    ':InvoiceNo' => $InvoiceNo,
    ':id' => $id
]);

        }

        }



  public function InvoiceDetailsOnReturn($invoice_id) {
        $stmt = $this->connect()->prepare('SELECT 
    CONCAT(B.product_description," ",B.pack_size) as productdescription,
    s.product_id,
    s.quantity,
    s.price,
    s.expiry_date,
    r.note,
    SUM(s.quantity) AS sold_qty,
    IFNULL(SUM(r.returned_qty), 0) AS returned_qty,
   SUM(s.quantity) - IFNULL(SUM(r.returned_qty), 0) AS remaining_qty


FROM 
    sales_invoices_items s
JOIN setup_product B ON (s.product_id = B.id)    
LEFT JOIN (
    SELECT 
        A.product_id,
        A.note,
        A.expiry_date,
        SUM(A.quantity) AS returned_qty,
        B.sales_id
    FROM 
        sales_return_items A
    JOIN 
        sales_return B ON A.invoice_id = B.id
    GROUP BY 
        A.product_id, A.expiry_date, B.sales_id
) r ON s.product_id = r.product_id 
    AND s.expiry_date = r.expiry_date 
    AND s.invoice_id = r.sales_id
WHERE 
    s.invoice_id = :invoice_id
GROUP BY 
    s.product_id, s.expiry_date' );
        $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT); 
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return is_array($result) ? $result : [];
    }




  public function InvoiceDetails($invoice_id) {
        $stmt = $this->connect()->prepare('SELECT 
    A.*, 
    B.customer_name, 
    B.customer_phone, 
    C.employee_name, 
    C.employee_name AS salesPerson,
    B.address AS customer_address,
    IFNULL(D.amount, 0) AS PaidNow
FROM 
    sales_invoices A
JOIN 
    setup_customer B ON A.customer_id = B.id
JOIN 
    admin C ON A.sales_person = C.id
LEFT JOIN 
    account_transaction D ON A.receive_now_id = D.id
WHERE   A.id = :invoice_id');
        $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT); 
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $data ?: null;
    }


public function convertToBangladeshiWords($number) {
    $words = array(
        '0' => 'Zero', '1' => 'One', '2' => 'Two', '3' => 'Three',
        '4' => 'Four', '5' => 'Five', '6' => 'Six', '7' => 'Seven',
        '8' => 'Eight', '9' => 'Nine', '10' => 'Ten',
        '11' => 'Eleven', '12' => 'Twelve', '13' => 'Thirteen',
        '14' => 'Fourteen', '15' => 'Fifteen', '16' => 'Sixteen',
        '17' => 'Seventeen', '18' => 'Eighteen', '19' => 'Nineteen',
        '20' => 'Twenty', '30' => 'Thirty', '40' => 'Forty',
        '50' => 'Fifty', '60' => 'Sixty', '70' => 'Seventy',
        '80' => 'Eighty', '90' => 'Ninety'
    );

    $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];

    if ($number == 0) return 'Zero';

    $result = '';

    $num = str_pad($number, 9, '0', STR_PAD_LEFT);
    $crore = (int)substr($num, 0, 2);
    $lakh = (int)substr($num, 2, 2);
    $thousand = (int)substr($num, 4, 2);
    $hundred = (int)substr($num, 6, 1);
    $rest = (int)substr($num, 7, 2);

    if ($crore > 0) {
        $result .= $this->numToWords($crore, $words) . ' Crore ';
    }
    if ($lakh > 0) {
        $result .= $this->numToWords($lakh, $words) . ' Lakh ';
    }
    if ($thousand > 0) {
        $result .= $this->numToWords($thousand, $words) . ' Thousand ';
    }
    if ($hundred > 0) {
        $result .= $words[$hundred] . ' Hundred ';
    }
    if ($rest > 0) {
        $result .= 'and ' . $this->numToWords($rest, $words);
    }

    return trim($result);
}

function numToWords($n, $words) {
    if ($n <= 20) return $words[$n];
    elseif ($n < 100) {
        $tens = ((int)($n / 10)) * 10;
        $unit = $n % 10;
        return $words[$tens] . ($unit ? ' ' . $words[$unit] : '');
    }
    return '';
}




    }

    