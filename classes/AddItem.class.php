<?php 

class AddItem extends Dbh {
 
    
    protected function CreateData($code, $product_group, $product_orgin , $product_description, $pack_size, $sales_price) {
    try {
        $db = $this->connect();

        // Check for duplicate product code
        $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM `setup_product` WHERE `code` = :code");
        $checkStmt->bindParam(':code', $code);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            return ["status" => "error", "message" => "Duplicate product code."];
        }

        // Insert new product
        $stmt = $db->prepare("INSERT INTO `setup_product` 
            (`code`, `product_group`, `product_orgin`, `product_description`, `pack_size`, `price`, `created_date`, `poster`) 
            VALUES (:code, :product_group, :product_orgin , :product_description, :pack_size, :sales_price, :created_date, :poster)");

        if (!isset($_SESSION)) {
            session_start();
        }

        if (!isset($_SESSION['admin_access_token'])) {
            throw new Exception("Unauthorized: Missing session token.");
        }

        $created_date = date("Y-m-d H:i:s"); 
        $poster = $_SESSION['admin_access_token'];

        // Bind parameters
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':product_group', $product_group);
        $stmt->bindParam(':product_orgin', $product_orgin);
        $stmt->bindParam(':product_description', $product_description);
        $stmt->bindParam(':pack_size', $pack_size);
        $stmt->bindParam(':sales_price', $sales_price);
        $stmt->bindParam(':created_date', $created_date);
        $stmt->bindParam(':poster', $poster);

        if (!$stmt->execute()) {
            throw new Exception("Database error: Failed to insert product.");
        }

        // Get last inserted product ID
        $lastInsertId = $db->lastInsertId();

        // Insert into product_price_history
        $historyStmt = $db->prepare("INSERT INTO product_price_history (product_id, price, changed_date, changed_by) 
                                     VALUES (:product_id, :price, :changed_date, :changed_by)");
        $historyStmt->bindParam(':product_id', $lastInsertId);
        $historyStmt->bindParam(':price', $sales_price);
        $historyStmt->bindParam(':changed_date', $created_date);
        $historyStmt->bindParam(':changed_by', $poster);
        $historyStmt->execute();

        return ["status" => "success", "message" => "Product created successfully."];

    } catch (Exception $e) {
        return ["status" => "error", "message" => $e->getMessage()];
    } finally {
        $stmt = null;
    }
}

    
    
   protected function UpdateData($code, $product_group, $product_orgin  , $product_description, $pack_size, $sales_price, $related_id) {
    try {
        $db = $this->connect();

        // Check if the new code already exists in another record
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM `setup_product` WHERE `code` = :code AND `id` != :related_id");
        $checkStmt->bindParam(':code', $code);
        $checkStmt->bindParam(':related_id', $related_id);
        $checkStmt->execute();
        $count = $checkStmt->fetchColumn();

        if ($count > 0) {
            return ["status" => "error", "message" => "Duplicate entry: This code already exists."];
        }

        // Get current date
        $update_date = date("Y-m-d H:i:s");
        $today = date("Y-m-d");

        if (!isset($_SESSION)) {
            session_start();
        }

        if (!isset($_SESSION['admin_access_token'])) {
            throw new Exception("Unauthorized: Missing session token.");
        }
        $poster = $_SESSION['admin_access_token'];

        // Check if the price has changed today
        $priceCheckStmt = $db->prepare("SELECT price FROM product_price_history 
                                        WHERE product_id = :related_id 
                                        AND DATE(changed_date) = :today 
                                        ORDER BY changed_date DESC 
                                        LIMIT 1");
        $priceCheckStmt->bindParam(':related_id', $related_id);
        $priceCheckStmt->bindParam(':today', $today);
        $priceCheckStmt->execute();
        $lastPrice = $priceCheckStmt->fetchColumn();

        // Always update product details (except price if unchanged)
        $updateStmt = $db->prepare("UPDATE `setup_product` 
            SET `code` = :code, `product_group` = :product_group, `product_orgin` = :product_orgin , `product_description` = :product_description, 
                `pack_size` = :pack_size, `update_date` = :update_date 
            WHERE `id` = :related_id");

        // Bind parameters
        $updateStmt->bindParam(':code', $code);
        $updateStmt->bindParam(':product_group', $product_group);
        $updateStmt->bindParam(':product_orgin', $product_orgin);
        $updateStmt->bindParam(':product_description', $product_description);
        $updateStmt->bindParam(':pack_size', $pack_size);
        $updateStmt->bindParam(':update_date', $update_date);
        $updateStmt->bindParam(':related_id', $related_id);

        if (!$updateStmt->execute()) {
            throw new Exception("Database error: Failed to update product.");
        }

        // If the price is different from today’s record, update it
        if ($lastPrice === false || $lastPrice != $sales_price) {
            // Update price in setup_product
            $priceUpdateStmt = $db->prepare("UPDATE `setup_product` SET `price` = :sales_price WHERE `id` = :related_id");
            $priceUpdateStmt->bindParam(':sales_price', $sales_price);
            $priceUpdateStmt->bindParam(':related_id', $related_id);
            $priceUpdateStmt->execute();

            // Insert new price into history
            $historyStmt = $db->prepare("INSERT INTO product_price_history (product_id, price, changed_date, changed_by) 
                                         VALUES (:product_id, :price, :changed_date, :changed_by)");
            $historyStmt->bindParam(':product_id', $related_id);
            $historyStmt->bindParam(':price', $sales_price);
            $historyStmt->bindParam(':changed_date', $update_date);
            $historyStmt->bindParam(':changed_by', $poster);
            $historyStmt->execute();
        }

        return ["status" => "success", "message" => "Product updated successfully."];

    } catch (Exception $e) {
        return ["status" => "error", "message" => $e->getMessage()];
    }
}


      public function PriceHistory($id) {

        $stmt = $this->connect()->prepare('SELECT C.employee_name,A.*,B.code,B.product_description FROM 
            product_price_history A  
            JOIN setup_product B ON (A.product_id = B.id) 
             JOIN admin C ON (A.changed_by = C.id) 
            WHERE A.product_id = :id ORDER BY A.changed_date DESC');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
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
          

        protected function DeleteFromJson($id) {
            $jsonFile = __DIR__ . '/../classes/json/item.json';
        
            // Read existing JSON file
            $jsonData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
        
            // Filter out the deleted item
            $jsonData = array_filter($jsonData, function ($product) use ($id) {
                return $product['id'] != $id;
            });
        
            // Save back to JSON file
            file_put_contents($jsonFile, json_encode(array_values($jsonData), JSON_PRETTY_PRINT));
        }

        
public function ListData() {
    $jsonFile = __DIR__ . '/../classes/json/item.json';
    
    // Clear file stat cache to ensure fresh data
    clearstatcache(true, $jsonFile);

    if (!file_exists($jsonFile)) {
        return [];
    }

    $jsonContent = file_get_contents($jsonFile);
    $jsonData = json_decode($jsonContent, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Log error or handle as needed
        return [];
    }

    return $jsonData['items'] ?? [];
}
    
        public function SingleData($id) {
            $jsonFile = __DIR__ . '/../classes/json/item.json';
        
            // Check if the file exists
            if (!file_exists($jsonFile)) {
                return null; // Return null if file doesn't exist
            }
        
            // Read JSON file and decode it
            $jsonData = json_decode(file_get_contents($jsonFile), true);
        
            // Ensure "items" key exists
            if (!isset($jsonData['items'])) {
                return null;
            }
        
            // Loop through items to find the matching ID
            foreach ($jsonData['items'] as $item) {
                if ($item['id'] == $id) {
                    return $item; // Return the matched item
                }
            }
        
            return null; // Return null if no match is found
        }

      public function SingleDataByCode($code) {
            $jsonFile = __DIR__ . '/../classes/json/item.json';
        
            // Check if the file exists
            if (!file_exists($jsonFile)) {
                return null; // Return null if file doesn't exist
            }
        
            // Read JSON file and decode it
            $jsonData = json_decode(file_get_contents($jsonFile), true);
        
            // Ensure "items" key exists
            if (!isset($jsonData['items'])) {
                return null;
            }
        
            // Loop through items to find the matching ID
            foreach ($jsonData['items'] as $item) {
                if ($item['code'] == $code) {
                    return $item; // Return the matched item
                }
            }
        
            return null; // Return null if no match is found
        }


        
     public  function XMLtemList() {
    $stmt = $this->connect()->prepare('SELECT * FROM setup_product ');
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $products = [];

    foreach($data AS $row) {
        $products[] = [
            "id" => $row["id"],
            "code" => $row["code"],
            "product_group" => $row["product_group"],
            "product_orgin" => $row["product_orgin"],
            "product_description" => $row["product_description"],
            "pack_size" => $row["pack_size"],
            "price" => $row["price"],
            "created_date" => $row["created_date"],
            "update_date" => $row["update_date"],
            "poster" => $row["poster"]
        ];
    }

    // Convert data to JSON and save to a file
    $json_data = json_encode(["items" => $products], JSON_PRETTY_PRINT);
    file_put_contents(__DIR__ . "/json/item.json", $json_data);

    // Close connection
}
        
    
    }

    