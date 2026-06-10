<?php 

class Transaction extends Dbh {

         use SharedFunctionalityTrait;


    public function insertTransaction($transaction_type, $amount, $ledger_id, $account_id, $entity_type, $transaction_by, $transaction_by_id, $description, $transaction_date, $related_id,$time = 'null'  ) {
    try {
        $pdo = $this->connect();
        if (!$pdo) {
            throw new Exception("Database connection failed.");
        }

        // Generate a unique transaction code
        $code = $this->generateCode($pdo);
        $invoice_no = $code . '-' . date("Ymd");

        $transaction_by_id = ($transaction_by_id === 'Cash') ? null : $transaction_by_id;
        

        date_default_timezone_set('Asia/Dhaka');
        $time = ($time === 'null') ? time() : $time;



        $stmt = $pdo->prepare("
            INSERT INTO account_transaction 
            (code, invoice_no, transaction_type, amount, ledger_id, account_id, entity_type, transaction_by, transaction_by_id, description, transaction_date, create_date, time, poster, update_date, status, related_id) 
            VALUES 
            (:code, :invoice_no, :transaction_type, :amount, :ledger_id, :account_id, :entity_type, :transaction_by, :transaction_by_id, :description, :transaction_date, :create_date, :time, :poster, NULL, 'Done', :related_id)
        ");

        $stmt->execute([
            ':code' => $code,
            ':invoice_no' => $invoice_no,
            ':transaction_type' => $transaction_type,
            ':amount' => $amount,
            ':ledger_id' => $ledger_id,
            ':account_id' => $account_id,
            ':entity_type' => $entity_type,
            ':transaction_by' => $transaction_by,
            ':transaction_by_id' => $transaction_by_id,
            ':description' => $description,
            ':transaction_date' => $transaction_date,
            ':create_date' => date("Y-m-d"),
            ':time' => $time,
            ':poster' => $_SESSION['admin_access_token'] ?? 0,
            ':related_id' => $related_id
        ]);

        // Get the last inserted ID
        $lastInsertId = $pdo->lastInsertId();

        return ["status" => "success", "message" => "Transaction recorded successfully", "transaction_id" => $lastInsertId];

    } catch (PDOException $e) {
        return ["status" => "error", "message" => "Transaction failed: " . $e->getMessage()];
    }
}


    public function updateTransaction($transaction_type, $amount, $ledger_id, $account_id, $entity_type, $transaction_by, $transaction_by_id, $description, $transaction_date, $id) {
    try {
        $pdo = $this->connect();
        if (!$pdo) {
            throw new Exception("Database connection failed.");
        }

        // Check if a transaction exists for the given id
        $stmt = $pdo->prepare("SELECT id FROM account_transaction WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$transaction) {
            throw new Exception("Transaction not found for id: " . $id);
        }

        // Handle cash transactions (set transaction_by_id to NULL if Cash)
        $transaction_by_id = ($transaction_by_id === 'Cash') ? null : $transaction_by_id;

        // Update the transaction record
        $stmt = $pdo->prepare("
            UPDATE account_transaction 
            SET 
                transaction_type = :transaction_type,
                amount = :amount,
                ledger_id = :ledger_id,
                account_id = :account_id,
                entity_type = :entity_type,
                transaction_by = :transaction_by,
                transaction_by_id = :transaction_by_id,
                description = :description,
                transaction_date = :transaction_date,
                update_date = NOW(),
                poster = :poster
            WHERE id = :id
        ");

        $stmt->execute([
            ':transaction_type' => $transaction_type,
            ':amount' => $amount,
            ':ledger_id' => $ledger_id,
            ':account_id' => $account_id,
            ':entity_type' => $entity_type,
            ':transaction_by' => $transaction_by,
            ':transaction_by_id' => $transaction_by_id,
            ':description' => $description,
            ':transaction_date' => $transaction_date,
            ':poster' => $_SESSION['admin_access_token'] ?? 'system',
            ':id' => $id
        ]);

        return ["status" => "success", "message" => "Transaction updated successfully"];

    } catch (PDOException $e) {
        return ["status" => "error", "message" => "Transaction update failed: " . $e->getMessage()];
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

if (!isset($_SESSION['admin_access_token'])) {
session_start(); 
}


          // Fetch transaction details
        $stmt = $pdo->prepare('SELECT A.description, A.invoice_no, A.amount FROM account_transaction A WHERE A.id = :invoice_id');
        $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if transaction exists
        if (!$item) {
            throw new Exception("Transaction record not found.");
        }
        $mess = "{$item['description']} Deleted, Invoice no {$item['invoice_no']}, amount {$item['amount']}";


            // Prepare the SQL statement to delete the transaction record
            $stmt = $pdo->prepare('DELETE FROM account_transaction WHERE id = :invoice_id');
            $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT);
            $stmt->execute();




 $this->logAction('Delete Transaction', $mess);


            // Commit transaction
            $pdo->commit();

            return ["status" => "success", "message" => "Transaction deleted successfully."];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }


    private function generateCode($pdo) {
        // Fetch the last code from the database
        $stmt = $pdo->query("SELECT code FROM account_transaction ORDER BY id DESC LIMIT 1");
        $lastCode = $stmt->fetchColumn();

        if (!$lastCode) {
            return "0001"; // Start from 0001 if no previous record exists
        }

        // Extract numeric part of the code (e.g., "INV-0001" -> "0001")
        preg_match('/\d+$/', $lastCode, $matches);
        $num = isset($matches[0]) ? (int)$matches[0] : 0;

        // Increment and format as 4 digits
        return str_pad($num + 1, 4, '0', STR_PAD_LEFT);
    }



  public function InvoiceDetails($invoice_id) {
    
        $stmt = $this->connect()->prepare('SELECT * FROM account_transaction WHERE id = :invoice_id');
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
