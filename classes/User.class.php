<?php 

class User extends Dbh {

    protected function CreateData($employee_name, $email, $employee_number, $user_type, $password, $block_user,$login_start,$login_end) {
    try {
        $db = $this->connect();

        // Check for duplicate email or phone number
        $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM `admin` WHERE `email` = :email OR `employee_number` = :employee_number");
        $checkStmt->bindParam(':email', $email);
        $checkStmt->bindParam(':employee_number', $employee_number);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            return ["status" => "error", "message" => "Duplicate email or phone."];
        }

        // Prepare insert statement
        $stmt = $db->prepare("INSERT INTO `admin` 
            (`email`, `employee_name`, `employee_number`, `user_type`, `hash_pass`, `plainPassword`, `created_date`, `poster`, `block_user`,`login_start`,`login_end`) 
            VALUES (:email, :employee_name, :employee_number, :user_type, :hash_pass, :plainPassword, :created_date, :poster, :block_user, :login_start, :login_end)");

        // Secure session start
        if (!isset($_SESSION)) {
            session_start();
        }

        // Ensure session variable exists
        if (!isset($_SESSION['admin_access_token'])) {
            throw new Exception("Unauthorized: Missing session token.");
        }

        // Hash password
        $hash_pass = password_hash($password, PASSWORD_BCRYPT);
        $created_date = date("Y-m-d H:i:s"); // Simplified timestamp
        $poster = $_SESSION['admin_access_token'];

        // Bind parameters safely
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':employee_name', $employee_name);
        $stmt->bindParam(':employee_number', $employee_number);
        $stmt->bindParam(':user_type', $user_type);
        $stmt->bindParam(':hash_pass', $hash_pass);
        $stmt->bindParam(':plainPassword', $password);
        $stmt->bindParam(':created_date', $created_date);
        $stmt->bindParam(':poster', $poster);
        $stmt->bindParam(':block_user', $block_user);
        $stmt->bindParam(':login_start', $login_start);
        $stmt->bindParam(':login_end', $login_end);

        // Execute the query
        if (!$stmt->execute()) {
            throw new Exception("Database error: Failed to insert user.");
        }

        // Get last inserted user ID
        $lastUserId = $db->lastInsertId();

        // Select all menus
        $menuStmt = $db->prepare("SELECT `id` FROM `menus`");
        $menuStmt->execute();
        $menus = $menuStmt->fetchAll(PDO::FETCH_ASSOC);

        // Insert default permissions for each menu
        $permStmt = $db->prepare("INSERT INTO `user_permissions` (`user_id`, `menu_id`, `can_backdate`, `can_edit`, `can_delete`) 
                                  VALUES (:user_id, :menu_id, 0, 0, 0)");

        foreach ($menus as $menu) {
            $permStmt->bindParam(':user_id', $lastUserId);
            $permStmt->bindParam(':menu_id', $menu['id']);
            $permStmt->execute();
        }

        return ["status" => "success", "message" => "User created successfully and permissions assigned."];

    } catch (Exception $e) {
        return ["status" => "error", "message" => $e->getMessage()];
    } finally {
        $stmt = null; // Close the statement
    }
}

    





    protected function UpdateData($employee_name, $email, $related_id, $mobile, $user_type,$password,$block_user,$login_start,$login_end) {
        try {
            $stmt = $this->connect()->prepare("UPDATE `admin` 
                SET `employee_name` = :employee_name, `email` = :email, `employee_number` = :mobile, 
                    `user_type` = :user_type, `block_user` = :block_user , `hash_pass` = :hash_pass , `plainPassword` = :plainPassword ,`update_date` = :update_date ,`login_start` = :login_start ,`login_end` = :login_end 
                WHERE `id` = :related_id");


$update_date = date("Y-m-d H:i:s");

            // Bind parameters
            $stmt->bindParam(':employee_name', $employee_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':mobile', $mobile);
            $stmt->bindParam(':user_type', $user_type);
            $stmt->bindParam(':block_user', $block_user);
            $hash_pass = password_hash($password, PASSWORD_BCRYPT);
            $stmt->bindParam(':hash_pass', $hash_pass);
            $stmt->bindParam(':plainPassword', $password);
            $stmt->bindParam(':update_date', $update_date);
            $stmt->bindParam(':related_id', $related_id);
            $stmt->bindParam(':login_start', $login_start);
            $stmt->bindParam(':login_end', $login_end);
            if (!$stmt->execute()) {
                throw new Exception("Database error: Failed to update user.");
            }

            return ["status" => "success", "message" => "User updated successfully."];

        } catch (Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        } finally {
            $stmt = null;
        }
    }


    public function ListData(){
        $stmt = $this->connect()->prepare('SELECT * FROM admin  ');
        $stmt->execute();
        $Data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        return  $Data;
       
    }

    public function SingleData($id) {
        $stmt = $this->connect()->prepare('SELECT * FROM admin WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch single record
    
        return $data ?: null; // Return null if no record found
    }
    
 
    function getUserMenus($userId) {
  // Fetch menu items for the given user
  $stmt = $this->connect()->prepare("
  SELECT m.id, m.parent_id, m.menu_name, m.menu_link, m.icon
  FROM menus m
  INNER JOIN user_permissions up ON m.id = up.menu_id
  WHERE up.user_id = :userId
  ORDER BY up.menu_id,m.sort_order
");
$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
$stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getUserSingleMenus($userId,$menuId) {
        // Fetch menu items for the given user
        $stmt = $this->connect()->prepare("
        SELECT m.id, m.parent_id, m.menu_name, m.menu_link, m.icon
        FROM menus m
        INNER JOIN user_permissions up ON m.id = up.menu_id
        WHERE up.user_id = :userId AND up.menu_id = :menuId
        ORDER BY up.menu_id
      ");
      $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
      $stmt->bindParam(':menuId', $menuId, PDO::PARAM_INT);

      $stmt->execute();
              return $stmt->fetchAll(PDO::FETCH_ASSOC);
          }
      

    function AllMenu() {
        // Fetch menu items for the given user
        $stmt = $this->connect()->prepare("
        SELECT *
        FROM menus 
        WHERE parent_id is null 
        ORDER BY id
      ");
      $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
          }
      

          function MenuByParent($parent_id) {
            // Fetch menu items for the given user
            $stmt = $this->connect()->prepare("
             SELECT m.id, m.parent_id, m.menu_name, m.menu_link, m.icon
        FROM menus m
        WHERE m.parent_id = :parent_id
        ORDER BY m.id
          ");
          $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
    
          $stmt->execute();
                  return $stmt->fetchAll(PDO::FETCH_ASSOC);
              }
          


protected function addUserPermission($user_id, $menu_id, $permission_type, $status) {
    try {
        $db = $this->connect();

        // Check if the permission exists
        $checkStmt = $db->prepare("SELECT * FROM `user_permissions` WHERE `user_id` = :user_id AND `menu_id` = :menu_id");
        $checkStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $checkStmt->bindParam(':menu_id', $menu_id, PDO::PARAM_INT);
        $checkStmt->execute();
        $existingPermission = $checkStmt->fetch(PDO::FETCH_ASSOC);

        // If $permission_type is empty, handle insert/remove logic
        if ($permission_type == 'MAINMENU') {
            if ($status == 1) {
                // Insert new permission if not exists
                if (!$existingPermission) {
                    $stmt = $db->prepare("INSERT INTO `user_permissions` (`user_id`, `menu_id`) VALUES (:user_id, :menu_id)");
                    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt->bindParam(':menu_id', $menu_id, PDO::PARAM_INT);
                    $stmt->execute();

                    return ["status" => "success", "message" => "Menu permission granted"];
                }
                return ["status" => "error", "message" => "Permission already exists."];
            } else {
                // Remove user permission if it exists
                if ($existingPermission) {
                    $stmt = $db->prepare("DELETE FROM `user_permissions` WHERE `user_id` = :user_id AND `menu_id` = :menu_id");
                    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt->bindParam(':menu_id', $menu_id, PDO::PARAM_INT);
                    $stmt->execute();

                    return ["status" => "success", "message" => "Menu permission removed"];
                }
                return ["status" => "error", "message" => "No permission found to remove."];
            }
        }

        // Define valid permission columns
        $validPermissions = [
            'backdate' => 'can_backdate',
            'edit' => 'can_edit',
            'delete' => 'can_delete'
        ];

        // Check if permission type is valid
        if (!array_key_exists($permission_type, $validPermissions)) {
            return ["status" => "error", "message" => "Invalid permission type"];
        }

        // Ensure menu permission exists before assigning sub-permissions
        if (!$existingPermission) {
            return ["status" => "error", "message" => "Please provide menu permission first"];
        }

        // Toggle specific permission
        $column = $validPermissions[$permission_type];
        $stmt = $db->prepare("UPDATE `user_permissions` SET `$column` = :status WHERE `user_id` = :user_id AND `menu_id` = :menu_id");
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':menu_id', $menu_id, PDO::PARAM_INT);
        $stmt->execute();

        return ["status" => "success", "message" => ucfirst($permission_type) . " permission updated"];

    } catch (Exception $e) {
        return ["status" => "error", "message" => $e->getMessage()];
    } finally {
        $stmt = null;
    }
}


             public function getUserPermissions($user_id, $menu_link) {

        $query = "SELECT A.can_backdate, A.can_edit, A.can_delete FROM user_permissions A
        JOIN menus B ON (A.menu_id = B.id )
                  WHERE A.user_id = ? AND B.menu_link = ?";
        $stmt = $this->connect()->prepare($query);
        $stmt->execute([$user_id, $menu_link]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


            
}
