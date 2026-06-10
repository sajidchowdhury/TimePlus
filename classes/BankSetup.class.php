<?php 

class BankSetup extends Dbh {
 
    protected function CreateBankData($bank_name,$ac_number) {
        try {
            $db = $this->connect();
    
            // Check for duplicate  phone
            $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM `setup_bank` WHERE `bank_name` = :bank_name AND `account_no` = :ac_number ");
            $checkStmt->bindParam(':bank_name', $bank_name);
            $checkStmt->bindParam(':ac_number', $ac_number);
            $checkStmt->execute();
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
            if ($result['count'] > 0) {
                return ["status" => "error", "message" => "Duplicate data"];
            }
    
            // Prepare insert statement
            $stmt = $db->prepare("INSERT INTO `setup_bank` 
                (`bank_name`,`account_no`) 
                VALUES (:bank_name, :ac_number)");
    
    
            // Bind parameters
            $stmt->bindParam(':bank_name', $bank_name);
            $stmt->bindParam(':ac_number', $ac_number);

    
            if (!$stmt->execute()) {
                throw new Exception("Database error: Failed to insert .");
            }
    
            return ["status" => "success", "message" => "created successfully."];
    
        } catch (Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        } finally {
            $stmt = null;
        }
    }
    
    protected function UpdateBankData($bank_name,$ac_number, $related_id) {
        try {
            // Check if the new phone already exists in another record
            $checkStmt = $this->connect()->prepare("SELECT COUNT(*) FROM `setup_bank` WHERE `bank_name` = :bank_name AND `account_no` = :ac_number AND `id` != :related_id");
            $checkStmt->bindParam(':bank_name', $bank_name);
            $checkStmt->bindParam(':ac_number', $ac_number);
            $checkStmt->bindParam(':related_id', $related_id);
            $checkStmt->execute();
            $count = $checkStmt->fetchColumn();
    
            if ($count > 0) {
                return ["status" => "error", "message" => "Duplicate entry"];
            }
    
            // Proceed with the update
            $stmt = $this->connect()->prepare("UPDATE `setup_bank` 
                SET `bank_name` = :bank_name,`account_no` = :ac_number
                WHERE `id` = :related_id");
    
    $update_date = date("Y-m-d H:i:s");

            // Bind parameters
            $stmt->bindParam(':bank_name', $bank_name);
            $stmt->bindParam(':ac_number', $ac_number);
            $stmt->bindParam(':related_id', $related_id);
    
            if (!$stmt->execute()) {
                throw new Exception("Database error: Failed to update .");
            }
    
            return ["status" => "success", "message" => "updated successfully."];
    
        } catch (Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        } finally {
            $stmt = null;
        }
    }
    
    public function BankList(){
        $stmt = $this->connect()->prepare('SELECT * FROM setup_bank ');
        $stmt->execute();
        $Data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        return $Data;
    }

    public function SingleBankData($id) {
        $stmt = $this->connect()->prepare('SELECT * FROM setup_bank WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $data ?: null;
    }



    public function BankDetails($transaction_by_id) {
 


     if($transaction_by_id == 'Cash' ){
    $options = '<option value="0">Cash</option>';
     }else{
    $result = $this->SingleBankData($transaction_by_id);
    $options = '<option value="'.$result['id'].'">'.$result['bank_name'].' ::: ' . $result['account_no'].'</option>';
           
     }   

 return $options ; 


    }



}
