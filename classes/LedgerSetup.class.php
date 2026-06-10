<?php 

class LedgerSetup extends Dbh {
 
    protected function CreateLedgerData($ledger_name) {
        try {
            $db = $this->connect();
    
            // Check for duplicate  phone
            $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM `setup_ledger` WHERE `ledger_name` = :ledger_name");
            $checkStmt->bindParam(':ledger_name', $ledger_name);
            $checkStmt->execute();
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
            if ($result['count'] > 0) {
                return ["status" => "error", "message" => "Duplicate data"];
            }
    
            // Prepare insert statement
            $stmt = $db->prepare("INSERT INTO `setup_ledger` 
                (`ledger_name`) 
                VALUES (:ledger_name)");
    
    
            // Bind parameters
            $stmt->bindParam(':ledger_name', $ledger_name);

    
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
    
    protected function UpdateLedgerData($ledger_name, $related_id) {
        try {
            // Check if the new phone already exists in another record
            $checkStmt = $this->connect()->prepare("SELECT COUNT(*) FROM `setup_ledger` WHERE `ledger_name` = :ledger_name AND `id` != :related_id");
            $checkStmt->bindParam(':ledger_name', $ledger_name);
            $checkStmt->bindParam(':related_id', $related_id);
            $checkStmt->execute();
            $count = $checkStmt->fetchColumn();
    
            if ($count > 0) {
                return ["status" => "error", "message" => "Duplicate entry: This phone number already exists."];
            }
    
            // Proceed with the update
            $stmt = $this->connect()->prepare("UPDATE `setup_ledger` 
                SET `ledger_name` = :ledger_name
                WHERE `id` = :related_id");
    
    $update_date = date("Y-m-d H:i:s");

            // Bind parameters
            $stmt->bindParam(':ledger_name', $ledger_name);
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
    
    public function ListLedgerData(){
        $stmt = $this->connect()->prepare('SELECT * FROM setup_ledger where special = "No" ');
        $stmt->execute();
        $Data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        return $Data;
    }

    public function SingleLedgerData($id) {
        $stmt = $this->connect()->prepare('SELECT * FROM setup_ledger WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $data ?: null;
    }


    protected function CreateAccountData($ledger_id,$account_name) {
        try {
            $db = $this->connect();
    
            // Check for duplicate  phone
            $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM `setup_account` WHERE `ledger_id` = :ledger_id AND `account_name` = :account_name");
            $checkStmt->bindParam(':ledger_id', $ledger_id);
            $checkStmt->bindParam(':account_name', $account_name);

            $checkStmt->execute();
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
            if ($result['count'] > 0) {
                return ["status" => "error", "message" => "Duplicate data"];
            }
    
            // Prepare insert statement
            $stmt = $db->prepare("INSERT INTO `setup_account` 
                (`ledger_id`,`account_name`) 
                VALUES (:ledger_id, :account_name)");
    
    
            // Bind parameters
            $stmt->bindParam(':ledger_id', $ledger_id);
            $stmt->bindParam(':account_name', $account_name);

    
            if (!$stmt->execute()) {
                throw new Exception("Database error: Failed to insert.");
            }
    
            return ["status" => "success", "message" => "created successfully."];
    
        } catch (Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        } finally {
            $stmt = null;
        }
    }
    
    protected function UpdateAccountData($ledger_id,$account_name , $related_id) {
        try {
            // Check if the new phone already exists in another record
            $checkStmt = $this->connect()->prepare("SELECT COUNT(*) FROM `setup_account` WHERE `ledger_id` = :ledger_id AND `account_name` = :account_name AND `id` != :related_id");
            $checkStmt->bindParam(':ledger_id', $ledger_id);
            $checkStmt->bindParam(':account_name', $account_name);
            $checkStmt->bindParam(':related_id', $related_id);
            $checkStmt->execute();
            $count = $checkStmt->fetchColumn();
    
            if ($count > 0) {
                return ["status" => "error", "message" => "Duplicate entry: This phone number already exists."];
            }
    
            // Proceed with the update
            $stmt = $this->connect()->prepare("UPDATE `setup_account` 
                SET `ledger_id` = :ledger_id,`account_name` = :account_name
                WHERE `id` = :related_id");
    

            // Bind parameters
            $stmt->bindParam(':ledger_id', $ledger_id);
            $stmt->bindParam(':account_name', $account_name);

            $stmt->bindParam(':related_id', $related_id);
    
            if (!$stmt->execute()) {
                throw new Exception("Database error: Failed to update.");
            }
    
            return ["status" => "success", "message" => "updated successfully."];
    
        } catch (Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        } finally {
            $stmt = null;
        }
    }
    
    public function ListAccountData(){
        $stmt = $this->connect()->prepare('SELECT A.*,B.ledger_name FROM setup_account A JOIN setup_ledger B ON (A.ledger_id = B.id)  ');
        $stmt->execute();
        $Data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        return $Data;
    }


public function LedgerWiseAccountData($ledger_id) {
    $stmt = $this->connect()->prepare('SELECT id, account_name FROM setup_account WHERE ledger_id = :ledger_id');
    $stmt->bindParam(':ledger_id', $ledger_id, PDO::PARAM_INT);
    $stmt->execute();
    $Data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $Data; // Do not set $stmt = null before returning data
}


    public function SingleAccountData($id) {
        $stmt = $this->connect()->prepare('SELECT * FROM setup_account WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $data ?: null;
    }

}
