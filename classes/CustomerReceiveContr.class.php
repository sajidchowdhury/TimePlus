<?php 
class CustomerReceiveContr extends Transaction {

    use SharedFunctionalityTrait;

    private $customer_id; 
    private $discount; 
    private $receive; 
    private $remarks; 
    private $mdate; 
    private $related_id; 
    private $transaction_by; 
    private $transaction_by_id; 
    private $prev_mdate; 

    public function __construct($customer_id, $discount, $receive, $remarks, $transaction_by, $transaction_by_id, $mdate, $prev_mdate,$related_id) {
        $this->customer_id = $customer_id;
        $this->discount = $discount;
        $this->receive = $receive;
        $this->remarks = $remarks;
        $this->mdate = $mdate;
        $this->transaction_by = $transaction_by;
        $this->transaction_by_id = $transaction_by_id;
        $this->related_id = $related_id;        
        $this->prev_mdate = $prev_mdate;

    }

    public function Action() {



        $time = time() ; 

        // Validate inputs
        if (
            !$this->clean($this->customer_id) ||
            !$this->clean($this->remarks) ||
            !$this->clean($this->related_id)
        ) {
            return ["status" => "error", "message" => "Invalid input values"];
        }

        // Ensure discount and receive are valid numbers and greater than or equal to 0
        if ($this->discount < 0 || !is_numeric($this->discount)) {
            return ["status" => "error", "message" => "Invalid discount amount"];
        }

        if ($this->receive < 0 || !is_numeric($this->receive)) {
            return ["status" => "error", "message" => "Invalid receive amount"];
        }

        $responses = [];

        if ($this->related_id == 'New') {


            if ($this->mdate !== date('Y-m-d') && $this->checkEditPermission(['backdate'], 'customer_receive.php') !== true) {

            return ["status" => "error", "message" => 'Do not have permission to change date'];

            exit(); // Stop further execution
            } 


       // Process received payment transaction
            if ($this->receive > 0) {
                $remarks = ($this->remarks == 'N/A' || $this->remarks == '') ? 'Payment received from customer' : $this->remarks;



                $receiveResponse = $this->insertTransaction(
                    'credit',                  
                    $this->receive,            
                    1,                         // Ledger ID for customer payment
                    $this->customer_id,       
                    'customer', 
                    $this->transaction_by,
                    $this->transaction_by_id,               
                    $remarks, 
                    $this->mdate,
                    NULL,
                    $time
                    
                );

                if ($receiveResponse['status'] !== 'success') {
                    return $receiveResponse;  // Return early if there is an error with the receive
                }

                $responses[] = $receiveResponse;
            }




            // Process discount transaction
            if ($this->discount > 0) {
                $remarks = 'Discount' ;

                $discountResponse = $this->insertTransaction(
                    'debit',                   
                    $this->discount,          
                    2,                         // Ledger ID for discount
                    $this->customer_id,        
                    'customer',
                    $this->transaction_by,
                    $this->transaction_by_id,               
                    $remarks, 
                    $this->mdate,
                    NULL,$time

                );

                if ($discountResponse['status'] !== 'success') {
                    return $discountResponse;  // Return early if there is an error with the discount
                }

                $responses[] = $discountResponse;
            }

     


        } else {

            if ($this->checkEditPermission(['edit'], 'customer_receive.php') !== true) {

            return ["status" => "error", "message" => 'Do not have permission to Edit'];
            exit(); // Stop further execution
            } 


    // Checking permission for backdate editing
    if ($this->mdate != $this->prev_mdate && $this->checkEditPermission(['backdate'], 'customer_receive.php') !== true) {
        
        return ["status" => "error", "message" => 'Do not have permission to change date. Refresh for invoice date'];
        exit(); // Stop further execution
    } 



            // Determine transaction type
            $transaction_type = ($this->receive > 0) ? 'credit' : 'debit'; 
            $amount = ($this->receive > 0) ? $this->receive : $this->discount; 
            $ledger_id = ($this->receive > 0) ? 1 : 2; 
            $remarks = $this->remarks;

            $updateResponse = $this->updateTransaction(
                $transaction_type,                  
                $amount,            
                $ledger_id,                         
                $this->customer_id,       
                'customer', 
                $this->transaction_by,
                $this->transaction_by_id,                 
                $remarks, 
                $this->mdate,
                $this->related_id
            );

            if ($updateResponse['status'] !== 'success') {
                return $updateResponse;  // Return early if there is an error
            }

            $responses[] = $updateResponse;
        }

        // Final response handling
        return (!empty($responses))
            ? ["status" => "success", "message" => "Transactions processed successfully", "details" => $responses]
            : ["status" => "error", "message" => "No transaction was processed"];
    }
}
