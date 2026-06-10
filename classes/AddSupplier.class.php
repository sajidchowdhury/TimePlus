<?php 

class AddSupplier extends Dbh {
 
    protected function CreateData($supplier_name, $supplier_phone, $address) {
        try {
            $db = $this->connect();
    
            // Check for duplicate customer phone
            $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM `setup_suppliers` WHERE `supplier_phone` = :supplier_phone");
            $checkStmt->bindParam(':supplier_phone', $supplier_phone);
            $checkStmt->execute();
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
            if ($result['count'] > 0) {
                return ["status" => "error", "message" => "Duplicate customer phone number."];
            }
    
            // Prepare insert statement
            $stmt = $db->prepare("INSERT INTO `setup_suppliers` 
                (`supplier_name`, `supplier_phone`) 
                VALUES (:supplier_name, :supplier_phone)");
    
            if (!isset($_SESSION)) {
                session_start();
            }
    
            if (!isset($_SESSION['admin_access_token'])) {
                throw new Exception("Unauthorized: Missing session token.");
            }
    
            $created_date = date("Y-m-d H:i:s");
            $poster = $_SESSION['admin_access_token'];
    
            // Bind parameters
            $stmt->bindParam(':supplier_name', $supplier_name);
            $stmt->bindParam(':supplier_phone', $supplier_phone);
    
            if (!$stmt->execute()) {
                throw new Exception("Database error: Failed to insert customer.");
            }
    
            return ["status" => "success", "message" => "Customer created successfully."];
    
        } catch (Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        } finally {
            $stmt = null;
        }
    }
    
    protected function UpdateData($supplier_name, $supplier_phone, $related_id) {
        try {
            // Check if the new phone already exists in another record
            $checkStmt = $this->connect()->prepare("SELECT COUNT(*) FROM `setup_suppliers` WHERE `supplier_phone` = :supplier_phone AND `id` != :related_id");
            $checkStmt->bindParam(':supplier_phone', $supplier_phone);
            $checkStmt->bindParam(':related_id', $related_id);
            $checkStmt->execute();
            $count = $checkStmt->fetchColumn();
    
            if ($count > 0) {
                return ["status" => "error", "message" => "Duplicate entry: This phone number already exists."];
            }
    
            // Proceed with the update
            $stmt = $this->connect()->prepare("UPDATE `setup_suppliers` 
                SET `supplier_name` = :supplier_name, `supplier_phone` = :supplier_phone
                WHERE `id` = :related_id");
    
    $update_date = date("Y-m-d H:i:s");

            // Bind parameters
            $stmt->bindParam(':supplier_name', $supplier_name);
            $stmt->bindParam(':supplier_phone', $supplier_phone);
            $stmt->bindParam(':related_id', $related_id);
    
            if (!$stmt->execute()) {
                throw new Exception("Database error: Failed to update customer.");
            }
    
            return ["status" => "success", "message" => "Customer updated successfully."];
    
        } catch (Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        } finally {
            $stmt = null;
        }
    }
    
    public function ListData(){
        $stmt = $this->connect()->prepare('SELECT * FROM setup_suppliers');
        $stmt->execute();
        $Data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        return $Data;
    }

    public function SingleData($id) {
        $stmt = $this->connect()->prepare('SELECT * FROM setup_suppliers WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $data ?: null;
    }
}
