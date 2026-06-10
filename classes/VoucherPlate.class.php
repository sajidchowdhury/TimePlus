<?php 

class VoucherPlate {

    private $id; 

    public function __construct($id = 'New') {
        $this->id = $id;
    }

    public function SetupForm() { 


        $List = new LedgerSetup();


      if ($this->id == 'New') {


            $ledger_id = $account_id = '';
            $amount  = 0.00;
            $remarks = 'N/A';
            $transaction_by = 'Cash';
            $transaction_by_id = 'Cash';
            $transaction_type = '';
            $details = '<option value="Cash">Cash</option>';
            $transaction_date = $this->getCurrentDate();

        } else {


            $info = new Transaction();
            $data = $info->InvoiceDetails($this->id);

            $ledger_id = $data['ledger_id'];
            $account_id = $data['account_id'];
            $transaction_by = $data['transaction_by'];
            
            if($transaction_by == 'Bank' ){

            $BankOption = new BankSetup();
            $details = $BankOption->BankDetails($data['transaction_by_id']);

            }else{
            $details = '<option value="Cash">Cash</option>';
            }
            $transaction_type = $data['transaction_type'];

            $amount = $data['amount'];
            $remarks = $data['description'];
            $transaction_date = $data['transaction_date'];
        }




        $csrf_token = $_SESSION['csrf_token'] ?? ''; // Prevent undefined index error
        
        $content = '<div class="row">
        <div class="col-md-12" style="margin-bottom: 0px!important;"> 

        <form id="myForm" method="post">
            <input type="hidden" name="csrf_token" value="'.$csrf_token.'">
            <input type="hidden" name="related_id" id="related_id" value="'.$this->id.'">
         <input type="hidden" name="prev_mdate" id="prev_mdate" value="'.$transaction_date.'">

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Entry Table</h3>
                        </div>

                        <div class="card-body">
                            <div class="row">
                               <div class="col-md-6">
    <div class="form-group">
        <label for="ledger_id">Ledger Name</label>
        <select name="ledger_id" id="ledger_id" required class="form-control select2" style="width: 100%;" onchange="LedgerWiseAc(this.value);">
            <option value="">Select One</option>';
            
            foreach ($List->ListLedgerData() as $row) { 
                $content .= '<option ' ; 
                if($ledger_id == $row['id']) {  $content .= ' selected="selected" ' ; }else{}
                $content .= 'value="'.$row['id'].'">'.$row['ledger_name'].'</option>';
            }
        
        $content .= '</select>
    </div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <label for="account_id">Account List</label>
        <div id="load_account_head">
            <select name="account_id" id="account_id" required class="form-control select2" style="width: 100%;">
                <option value="">Select One</option>';

                 foreach ($List->ListAccountData() as $row) { 
                $content .= '<option ' ; 
                if($account_id == $row['id']) {  $content .= ' selected="selected" ' ; }else{}
                $content .= 'value="'.$row['id'].'">'.$row['account_name'].'</option>';
            }


            $content .= '</select>
        </div>
    </div>
</div> 


<div class="col-md-6">
    <div class="form-group">
        <label for="transaction_by">Transaction By</label>
        <select name="transaction_by" id="transaction_by" required class="form-control select2" style="width: 100%;" onchange="TransactionBYDetails(this.value);">
            <option ' ; if($transaction_by == 'Cash' ) { $content .= ' selected="selected" ' ; }else{} $content .= ' value="Cash">Cash</option>
            <option ' ; if($transaction_by == 'Bank' ) { $content .= ' selected="selected" ' ; }else{} $content .= ' value="Bank">Bank</option>
            </select>
    </div>
</div>
            <div class="col-md-6">
            <div class="form-group">
                <label for="transaction_by_id">Details</label>
                <div id="load_transaction_by">
                    <select name="transaction_by_id" id="transaction_by_id" required class="form-control select2" style="width: 100%;">
                        '.$details .'
                    </select>
                </div>
            </div>
            </div> 

                              <div class="col-md-6">
    <div class="form-group">
        <label for="transaction_type">Type</label>
        <select name="transaction_type" id="transaction_type" required class="form-control select2" style="width: 100%;" >
                    <option value="">Select One</option>
            <option ' ; if($transaction_type == 'credit' ) { $content .= ' selected="selected" ' ; }else{} $content .= ' value="credit">Income</option>
            <option ' ; if($transaction_type == 'debit' ) { $content .= ' selected="selected" ' ; }else{} $content .= ' value="debit">Expense</option>

            </select>
    </div>
</div>

   <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount">Amount</label>
<input type="number" class="form-control" name="amount" id="amount" value="'.$amount.'" autocomplete="off">
                                    </div>
                                </div>



                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="remarks">Remarks</label>
                                        <input type="text" class="form-control" name="remarks" id="remarks" value="'.$remarks.'" autocomplete="off">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mdate">Date</label>
                                        <input required type="date" class="form-control" name="mdate" id="mdate" value="'.$transaction_date.'" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <input type="submit" name="kt_submit_button" id="kt_submit_button" class="btn btn-primary" value="Submit">
                        </div>
                    </div>
                </div>
            </div>
        </form>
        </div>
        </div>';

        print $content;
    }

     /**
     * Helper function to check selected value in dropdowns
     */
    private function isSelected($value, $expected) {
        return ($value == $expected) ? 'selected' : '';
    }

    /**
     * Helper function to get the current date
     */
    private function getCurrentDate() {
        return date("Y-m-d");
    }


}
