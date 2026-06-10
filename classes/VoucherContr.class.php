<?php 
class VoucherContr extends Transaction {

    use SharedFunctionalityTrait;

    private $ledger_id; 
    private $account_id; 
    private $transaction_by; 
    private $transaction_by_id; 
    private $transaction_type; 
    private $amount; 
    private $remarks;  
    private $mdate;
    private $related_id; 
    private $prev_mdate; 

    public function __construct($ledger_id, $account_id, $transaction_by, $transaction_by_id, $transaction_type, $amount, $remarks, $mdate, $prev_mdate , $related_id) {
        $this->ledger_id = filter_var($ledger_id, FILTER_VALIDATE_INT);
        $this->account_id = filter_var($account_id, FILTER_VALIDATE_INT);
        $this->transaction_by = htmlspecialchars($transaction_by);
        $this->transaction_by_id = $transaction_by_id;
        $this->transaction_type = ($transaction_type === 'credit' || $transaction_type === 'debit') ? $transaction_type : null;
        $this->amount = filter_var($amount, FILTER_VALIDATE_FLOAT);
        $this->remarks = htmlspecialchars($remarks);
        $this->mdate = htmlspecialchars($mdate);
                $this->prev_mdate = htmlspecialchars($prev_mdate);

        $this->related_id = ($related_id === 'New') ? 'New' : filter_var($related_id, FILTER_VALIDATE_INT);
    }

    public function Action() {
        // Validate required fields
        if (
            !$this->ledger_id || !$this->account_id || !$this->transaction_by ||
            !$this->transaction_by_id || !$this->transaction_type || !$this->amount ||
            !$this->remarks || !$this->mdate || !$this->related_id
        ) {
            return ["status" => "error", "message" => "Invalid input values"];
        }

        if ($this->related_id === 'New') {


            if ($this->mdate !== date('Y-m-d') && $this->checkEditPermission(['backdate'], 'voucher_wise.php') !== true) {

            return ["status" => "error", "message" => 'Do not have permission to change date'];

            exit(); // Stop further execution
            } 



            // Insert New Voucher
            $result = $this->insertTransaction(
                $this->transaction_type, $this->amount, $this->ledger_id, $this->account_id,
                'accounts', $this->transaction_by, $this->transaction_by_id,
                $this->remarks, $this->mdate, NULL
            );
            return $result ? ["status" => "success", "message" => "Transaction recorded"] 
                           : ["status" => "error", "message" => "Failed to insert transaction"];
        } else {


            if ($this->checkEditPermission(['edit'], 'voucher_wise.php') !== true) {

            return ["status" => "error", "message" => 'Do not have permission to Edit'];
            exit(); // Stop further execution
            } 


    // Checking permission for backdate editing
    if ($this->mdate != $this->prev_mdate && $this->checkEditPermission(['backdate'], 'voucher_wise.php') !== true) {
        
        return ["status" => "error", "message" => 'Do not have permission to change date. Refresh for invoice date'];
        exit(); // Stop further execution
    } 

    

            
            // Update Existing Voucher
            $result = $this->updateTransaction(
                $this->transaction_type,                  
                $this->amount,            
                $this->ledger_id,  
                 $this->account_id     ,                  
                'accounts',       
                $this->transaction_by, 
                $this->transaction_by_id,
                $this->remarks, 
                $this->mdate,
                $this->related_id
            );
            return $result ? ["status" => "success", "message" => "Transaction updated"] 
                           : ["status" => "error", "message" => "Failed to update transaction"];
        }
    }
}
