<?php 
class SupplierPaymentContr extends Transaction {

    use SharedFunctionalityTrait;

    private $supplier_id; 
    private $pay_now; 
    private $remarks; 
    private $mdate; 
    private $related_id; 
            private $transaction_by; 
    private $transaction_by_id; 
    private $prev_mdate; 


    public function __construct($supplier_id, $pay_now, $remarks, $mdate, $prev_mdate , $transaction_by , $transaction_by_id, $related_id) {

        $this->supplier_id = $supplier_id;
        $this->pay_now = $pay_now;
        $this->remarks = $remarks;
        $this->mdate = $mdate;
                $this->transaction_by = $transaction_by;
        $this->transaction_by_id = $transaction_by_id;

        $this->related_id = $related_id;
                $this->prev_mdate = $prev_mdate;

    }


       public function Action() {
    try {
        // Validate inputs
        if (
            !$this->clean($this->supplier_id) ||
            !$this->clean($this->remarks) ||
            !$this->clean($this->related_id)
        ) {
            return ["status" => "error", "message" => "Invalid input values"];
        }

        // Ensure 'pay_now' is a valid number and greater than 0
        if ($this->pay_now < 0 || !is_numeric($this->pay_now)) {
            return ["status" => "error", "message" => "Invalid pay now amount"];
        }

        $responses = [];

        // If it's a new transaction
        if ($this->related_id == 'New') {


         if ($this->mdate !== date('Y-m-d') && $this->checkEditPermission(['backdate'], 'supplier_payment.php') !== true) {

            return ["status" => "error", "message" => 'Do not have permission to change date'];

            exit(); // Stop further execution
            } 





            if ($this->pay_now > 0) {
                $remarks = ($this->remarks == 'N/A' || $this->remarks == '') ? 'Supplier payment' : $this->remarks;

                $paymentResponse = $this->insertTransaction(
                    'debit',                   // Transaction type (debit for payment)
                    $this->pay_now,            // Amount
                    2,                         // Ledger ID for supplier payment
                    $this->supplier_id,        // Supplier ID
                    'supplier',                // Entity type
                    $this->transaction_by,     // Transaction by
                    $this->transaction_by_id,  // Transaction by ID
                    $remarks,                  // Remarks
                    $this->mdate,              // Transaction date
                    NULL                       // No related ID for a new transaction
                );

                // If transaction fails, return error immediately
                if ($paymentResponse['status'] !== 'success') {
                    return $paymentResponse;
                }

                $responses[] = $paymentResponse;
            }
        } else {



            if ($this->checkEditPermission(['edit'], 'supplier_payment.php') !== true) {

            return ["status" => "error", "message" => 'Do not have permission to Edit'];
            exit(); // Stop further execution
            } 


    // Checking permission for backdate editing
    if ($this->mdate != $this->prev_mdate && $this->checkEditPermission(['backdate'], 'supplier_payment.php') !== true) {
        
        return ["status" => "error", "message" => 'Do not have permission to change date. Refresh for invoice date'];
        exit(); // Stop further execution
    } 

    
            // If updating an existing transaction
            $transaction_type = 'debit'; // Supplier payments are always debits
            $remarks = $this->remarks;

            $updateResponse = $this->updateTransaction(
                $transaction_type,
                $this->pay_now,
                2,                          // Ledger ID for supplier payment
                $this->supplier_id,
                'supplier',
                $this->transaction_by,
                $this->transaction_by_id,
                $remarks,
                $this->mdate,
                $this->related_id
            );

            // If update fails, return error immediately
            if ($updateResponse['status'] !== 'success') {
                return $updateResponse;
            }

            $responses[] = $updateResponse;
        }

        // Ensure valid JSON response
        $finalResponse = !empty($responses) 
            ? ["status" => "success", "message" => "Transaction processed successfully", "details" => $responses]
            : ["status" => "error", "message" => "No transaction was processed"];

        // Send JSON response
        header('Content-Type: application/json');
        echo json_encode($finalResponse);
        exit;

    } catch (Exception $e) {
        // Catch unexpected errors
        header('Content-Type: application/json');
        echo json_encode(["status" => "error", "message" => "Unexpected error: " . $e->getMessage()]);
        exit;
    }
}





}
