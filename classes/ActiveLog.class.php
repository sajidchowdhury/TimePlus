<?php 

class ActiveLog extends Dbh {

    public function CreatLog($user_id, $action_type, $details) {
    try {
        $db = $this->connect();

      $log_date = date("Y-m-d H:i:s");

        // Prepare insert statement
        $stmt = $db->prepare("INSERT INTO `server_log` 
            (`log_date`, `user_id`, `action_type`, `details`) 
            VALUES (:log_date, :user_id, :action_type , :details)");

        // Bind parameters safely
        $stmt->bindParam(':log_date', $log_date);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':action_type', $action_type);
        $stmt->bindParam(':details', $details);

        // Execute the query
        if (!$stmt->execute()) {
            throw new Exception("Database error: Failed to insert user.");
        }

    
        return true;

    } catch (Exception $e) {
        return ["status" => "error", "message" => $e->getMessage()];
    } finally {
        $stmt = null; // Close the statement
    }
}

    




            
}
