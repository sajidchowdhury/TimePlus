<?php



class Dbh {

    protected function connect() {

        try {

            //inventory

           $username = 'root';  // Your database username

           $password = '';      // Your database password



           //$username = 'root'; 

            //$password = ''; 

            $dbh = new PDO('mysql:host=localhost;dbname=osudlagb_TimePlus', $username, $password);

            // Set PDO error mode to exception

            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $dbh;

        } catch (PDOException $e) {

            // Print error message and terminate script

            die("Error! - " . $e->getMessage());

        }

    }





    static function checkAdmin($ID) {

        $dbh = new self();

        $pdo = $dbh->connect();

        

        $lc_fetch = $pdo->prepare("SELECT `id` FROM `admin` WHERE `user_type` = 'Admin' and id = ?");

        $lc_fetch->execute([$ID]);

        if ($lc_fetch->rowCount() > 0) {

            return true ; 

        }else{

            return false ; 

        }





    }

    static function CreateCode($table) {

        // Establish database connection

        $dbh = new self();

        $pdo = $dbh->connect();



        // Prepare and execute query to fetch the latest code

        $lc_fetch = $pdo->prepare("SELECT `code` FROM `{$table}` WHERE `status` = 'Done' ORDER BY `id` DESC LIMIT 1");

        $lc_fetch->execute();



        // If there are existing codes, generate a new one

        if ($lc_fetch->rowCount() > 0) {

            $fetch_list = $lc_fetch->fetch(PDO::FETCH_ASSOC);

            $number = intval($fetch_list['code']);

            $number++;

            $code = str_pad($number, 4, "0", STR_PAD_LEFT); // Pad the number with leading zeros

        } else {

            // If no existing codes, start from 0001

            $code = '0001';

        }



        // Generate invoice number

        $invoice_no = date("d-m-Y") . '-' . $code;



        // Return generated code and invoice number

        return array(

            'code' => $code,

            'invoice_no' => $invoice_no

        );

    }



    static function InsertCode($table) {

        // Establish database connection

        $dbh = new self();

        $pdo = $dbh->connect();



        // Generate code and invoice number

        $create_code = $dbh->CreateCode($table);

        $code = $create_code['code'];

        $status = 'Done';

        $created_date = date("Y-m-d H:i:s"); // Use 24-hour format for MySQL DATETIME

        if (!isset($_SESSION)) {

            session_start();

        }

        $poster = isset($_SESSION['admin_access_token']) ? $_SESSION['admin_access_token'] : null;



        // Prepare and execute query to insert code into table

        $stmt = $pdo->prepare("INSERT INTO `{$table}` (`code`, `status`, `created_date`, `poster`) VALUES (:code, :status, :created_date, :poster)");

        $stmt->bindParam(':code', $code);

        $stmt->bindParam(':status', $status);

        $stmt->bindParam(':created_date', $created_date);

        $stmt->bindParam(':poster', $poster);

        $stmt->execute();



        // Get the last inserted ID

        $last_insert_id = $pdo->lastInsertId();



        // Return the last inserted ID and the code

        return array(

            'last_id' => $last_insert_id,

            'code' => $code

        );

    }

}

