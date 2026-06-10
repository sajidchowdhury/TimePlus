<?php 
class SalesReturn extends Dbh {

        use SharedFunctionalityTrait;


protected function CreateData($cart_data,$customer_id ,$invoice_date ,$sales_id) {
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


           $invoice_date = date("Y-m-d");
        // Step 3: Insert data into `sales_invoices`
        $created_date = date("Y-m-d H:i:s");
                        $time = date("H:i:s") ; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$poster = $_SESSION['admin_access_token'] ?? 0;

        $total_amount = $this->calculateTotalAmount($cart_data);
        

        $stmt = $pdo->prepare("INSERT INTO sales_return (code, invoice_no, customer_id, created_date, sales_id, poster, invoice_date , total_amount,time) 
                                VALUES (:code, :invoice_no, :customer_id, :created_date, :sales_id, :poster, :invoice_date, :total_amount, :time)");
        $stmt->execute([
            ':code' => $code,
            ':invoice_no' => $invoice_no,
            ':customer_id' => $customer_id,
            ':created_date' => $created_date,
            ':sales_id' => $sales_id,
            ':poster' => $poster,
            ':invoice_date' => $invoice_date,
            ':total_amount' => $total_amount,
            ':time' => $time
        ]);

        // Get the last inserted invoice ID
        $invoice_id = $pdo->lastInsertId();

        // Step 4: Insert data into `sales_invoices_items`
        foreach ($cart_data as  $item) {

            $product_id = $item['product_id'];
            $return_now = $item['return_now'];
            $note = $item['note'];
            $price = $item['price'];
            $total_price = $return_now * $price;
            $expiry_date = $item['expiry_date'];


            $stmt = $pdo->prepare("INSERT INTO sales_return_items (invoice_id, sales_id, product_id, quantity, price, expiry_date, poster, total_price, note) 
                                    VALUES (:invoice_id, :sales_id , :product_id, :quantity, :price, :expiry_date , :poster, :total_price, :note)");
            $stmt->execute([
                ':invoice_id' => $invoice_id,
                ':sales_id' => $sales_id,
                ':product_id' => $product_id,
                ':quantity' => $return_now,
                ':price' => $price,
                ':expiry_date' => $expiry_date,
                ':poster' => $poster,
                ':total_price' => $total_price,
                ':note' => $note
            ]);
        }

    

          // Step 5: Insert transaction and retrieve transaction_id
        $TR = new Transaction();
        $transaction_result = $TR->insertTransaction(
            'credit',
            $total_amount,
            6, // Ledger ID for sales return
            $customer_id,
            'customer',
            'Cash',
            'Cash',
            'Product Return',
            $invoice_date,
            $invoice_id,
            $time
        );


          
        if ($transaction_result['status'] !== 'success') {
            throw new Exception("Transaction failed: " . $transaction_result['message']);
        }

        $transaction_id = $transaction_result['transaction_id']; // Retrieve transaction_id



        // Step 6: Update sales_invoices with transaction_id
        $stmt = $pdo->prepare("UPDATE sales_return SET transaction_id = :transaction_id  WHERE id = :invoice_id");
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


   private function generateCode() {
        // Get the last `code` from `sales_invoices`
        $stmt = $this->connect()->query("SELECT code FROM sales_return ORDER BY id DESC LIMIT 1");
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
        $total += $item['return_now'] * $item['price'];
    }
    return $total;
}




   protected function SearchByInvoice($search_text) {
    $conn = $this->connect(); // Get DB connection

    $safe_search = '%' . trim($search_text) . '%';


    $content = '<div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">Invoice Wise Search </h3>
        </div>
        <div class="card-body">
        <div class="table-responsive">';

    $query = "
        SELECT
            A.id,
            A.invoice_date,
            A.invoice_no,
            A.discount,
            A.total_amount,
            c.customer_name,
            c.customer_phone
        FROM sales_invoices A
        JOIN setup_customer c ON A.customer_id = c.id
        WHERE 
            A.invoice_date LIKE :search
            OR A.invoice_no LIKE :search
            OR c.customer_name LIKE :search
            OR c.customer_phone LIKE :search
        ORDER BY A.invoice_date DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(':search', $safe_search, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 


    if (count($result) > 0) {
        $content .= "<table class='table table-bordered' id='example'>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Invoice No</th>
                    <th>Invoice Date</th>
                    <th>Discount</th>
                    <th>Invoice Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($result as $row) {

            $idName = 'SalesReturnInvoiceId' . $row['id'] ; 

            $content .= "<input  type='text' style='display:none' id='{$idName}' value= '{$row['id']}'><tr>
                <td>{$row['customer_name']}</td>
                <td>{$row['customer_phone']}</td>
                <td>{$row['invoice_no']}</td>
                <td>{$row['invoice_date']}</td>
                <td>{$row['discount']}</td>
                <td>{$row['total_amount']}</td>";
                            $content .= ' <td><button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modal-xl"
    id="exampleModalCenter" 
    onclick="openModal(\'Sales_Return_Collection\', \''.$idName.'\', \'Sales Return\');">
    Return
</button> </td>';            


$content .= "</tr>";
        }

        $content .= "</tbody>
         
        </table></div></div>";
    } else {
        $content .= "<p>No records found for the selected date range.</p>";
    }

    $content .= $this->LoadExportScript('Search By Invoice') ; 
   
    return $content;
}





protected function SearchByCustomer($date1,$date2,$customer_id) {


    $conn = $this->connect(); // Get DB connection

$date1 = DateTime::createFromFormat('d/m/Y', $date1)->format('Y-m-d');
$date2 = DateTime::createFromFormat('d/m/Y', $date2)->format('Y-m-d');

    $content = '<div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">Invoice Wise Search </h3>
        </div>
        <div class="card-body">
        <div class="table-responsive">';

    $query = "
        SELECT
            A.id,
            A.invoice_date,
            A.invoice_no,
            A.discount,
            A.total_amount,
            c.customer_name,
            c.customer_phone
        FROM sales_invoices A
        JOIN setup_customer c ON A.customer_id = c.id
        WHERE 
            (A.invoice_date BETWEEN :date1 and :date2 ) AND A.customer_id = :customer_id
        ORDER BY A.invoice_date DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(':date1', $date1, PDO::PARAM_STR);
    $stmt->bindValue(':date2', $date2, PDO::PARAM_STR);
    $stmt->bindValue(':customer_id', $customer_id, PDO::PARAM_STR);

    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 


    if (count($result) > 0) {
        $content .= "<table class='table table-bordered' id='example'>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Invoice No</th>
                    <th>Invoice Date</th>
                    <th>Discount</th>
                    <th>Invoice Price</th>
                    <th class='no-export'>Action</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($result as $row) {

            $idName = 'SalesReturnInvoiceId' . $row['id'] ; 
            $content .= "<input  type='text' style='display:none' id='{$idName}' value= '{$row['id']}'>
<tr>
                <td>{$row['customer_name']}</td>
                <td>{$row['customer_phone']}</td>
                <td>{$row['invoice_no']}</td>
                <td>{$row['invoice_date']}</td>
                <td>{$row['discount']}</td>
                <td>{$row['total_amount']}</td>
                <td class='no-export' >";

$content .= "<div class='btn-group'>
                               ";

                             $content .= ' <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modal-xl"
    id="exampleModalCenter" 
    onclick="openModal(\'Sales_Return_Collection\', \''.$idName.'\', \'Sales Return\');">
    Return
</button> ';


                            $content .= "</div>";

                 $content .= "</td>
            </tr>";
        }

        $content .= "</tbody>
         
        </table></div></div>";
    } else {
        $content .= "<p>No records found for the selected date range.</p>";
    }

    $content .= $this->LoadExportScript('Search By Invoice') ; 
   
   
    return $content;

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
            FROM sales_return_items A
            JOIN sales_return B ON A.invoice_id = B.id
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
        $log_message = "$product_name deleted from sales return invoice $invoice_no.";

        // Step 4: Delete the item
        $stmt = $pdo->prepare("DELETE FROM sales_return_items WHERE id = :item_id");
        if (!$stmt->execute([':item_id' => $item_id])) {
            throw new Exception("Failed to delete item from invoice.");
        }

        // Step 5: Recalculate invoice total
        $stmt = $pdo->prepare("SELECT SUM(total_price) AS new_total FROM sales_return_items WHERE invoice_id = :invoice_id");
        if (!$stmt->execute([':invoice_id' => $invoice_id])) {
            throw new Exception("Failed to recalculate invoice total.");
        }

        $new_total = $stmt->fetchColumn();
        $new_total = $new_total ? (float)$new_total : 0;

        // Step 6: Update invoice total
        $stmt = $pdo->prepare("UPDATE sales_return SET total_amount = :new_total WHERE id = :invoice_id");
        if (!$stmt->execute([':new_total' => $new_total, ':invoice_id' => $invoice_id])) {
            throw new Exception("Failed to update invoice total.");
        }


    // Step 6: Update transaction
        $stmt = $pdo->prepare("UPDATE account_transaction SET amount = :new_total WHERE id = :transaction_id");
        if (!$stmt->execute([':new_total' => $new_total, ':transaction_id' => $item['transaction_id']])) {
            throw new Exception("Failed to update invoice total.");
        }

       

        // Step 7: Log the action
        if (!$this->logAction('Delete Sales Return Invoice Item', $log_message)) {
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
                A.transaction_id
            FROM sales_return A 
            JOIN setup_customer B ON A.customer_id = B.id 
            WHERE A.id = :invoice_id
        ");
        if (!$stmt->execute([':invoice_id' => $invoice_id])) {
            throw new Exception("Failed to fetch invoice data.");
        }

        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$invoice) {
            throw new Exception("Sales Return Invoice not found with ID: $invoice_id");
        }

        $log_message = "Sales Return Invoice no {$invoice['invoice_no']} deleted with amount {$invoice['total_amount']}. Customer: {$invoice['customer_name']}";

        // Step 4: Delete related items
        $stmt = $pdo->prepare("DELETE FROM sales_return_items WHERE invoice_id = :invoice_id");
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

    

        // Step 7: Delete the invoice
        $stmt = $pdo->prepare("DELETE FROM sales_return WHERE id = :invoice_id");
        if (!$stmt->execute([':invoice_id' => $invoice_id])) {
            throw new Exception("Failed to delete invoice.");
        }

        // Step 8: Log the deletion
        if (!$this->logAction('Delete Sales Return Invoice', $log_message)) {
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


  public function InvoiceItems($invoice_id) {
        $stmt = $this->connect()->prepare('SELECT A.*,sum(quantity) as Totalquantity,B.product_orgin,B.code,B.product_description,B.pack_size FROM sales_return_items A JOIN setup_product B ON (A.product_id = B.id) WHERE A.invoice_id = :invoice_id GROUP BY A.product_id' );
        $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT); 
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return is_array($result) ? $result : [];
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


  public function InvoiceDetails($invoice_id) {
        $stmt = $this->connect()->prepare('SELECT 
    A.*, 
    B.customer_name, 
    B.customer_phone, 
    B.address AS customer_address,
    IFNULL(D.amount, 0) AS PaidNow
FROM 
    sales_return A
JOIN 
    setup_customer B ON A.customer_id = B.id

LEFT JOIN 
    account_transaction D ON A.transaction_id = D.id
WHERE   A.id = :invoice_id');
        $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT); 
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $data ?: null;
    }


}
