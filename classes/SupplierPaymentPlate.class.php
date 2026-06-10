<?php

class SupplierPaymentPlate {
    private $id;

    public function __construct($id = 'New') {
        $this->id = $id;
    }

    public function SetupForm() {



        $csrf_token = $_SESSION['csrf_token'] ?? ''; // Prevent undefined index error

        if ($this->id == 'New') {


            $supplier_id = '';
            $amount  = 0.00;
            $remarks = 'N/A';
            $transaction_by = 'Cash';
            $details = '<option value="Cash">Cash</option>';
            $transaction_date = $this->getCurrentDate();
            $transaction_type = 'debit';

        } else {


            $info = new Transaction();
            $data = $info->InvoiceDetails($this->id);
             $amount = $data['amount'];
            $remarks = $data['description'];
            $transaction_by = $data['transaction_by'];

            if($transaction_by == 'Bank' ){

            $BankOption = new BankSetup();
            $details = $BankOption->BankDetails($data['transaction_by_id']);


            }else{
            $details = '<option value="Cash">Cash</option>';
            }

            $transaction_date = $data['transaction_date'];
            $transaction_type = $data['transaction_type'];
        }

        // Start Form Content
        $content = <<<HTML
        <div class="row">
            <div class="col-md-12">
                <form id="myForm" method="post">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    <input type="hidden" name="related_id" id="related_id" value="{$this->id}">
                    <input type="hidden" name="action" id="action" value="save">
                                        <input type="hidden" name="prev_mdate" id="prev_mdate" value="{$transaction_date}">


                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Entry Table</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Customer Name -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="supplier_id">Supplier Name</label>
                                        <select name="supplier_id" id="supplier_id" required class="form-control select2" style="width: 100%;">
                                            <option value="1">Liner</option>
        HTML;

     

        $content .= <<<HTML
                                        </select>
                                    </div>
                                </div>
     

                         <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pay_now">Pay Now</label>
                                        <input required type="number" step="0.01" VALUE = "{$amount}"  class="form-control" name="pay_now" id="pay_now" autocomplete="off">
                                    </div>
                                </div>
   HTML;

        $content .= <<<HTML
                                <!-- Remarks -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="remarks">Remarks</label>
                                        <input type="text" class="form-control" name="remarks" id="remarks" value="{$remarks}" autocomplete="off">
                                    </div>
                                </div>

                                <!-- Transaction By -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="transaction_by">Transaction By</label>
                                        <select name="transaction_by" id="transaction_by" required class="form-control select2" style="width: 100%;" onchange="TransactionBYDetails(this.value);">
                                            <option value="Cash" {$this->isSelected($transaction_by, 'Cash')}>Cash</option>
                                            <option value="Bank" {$this->isSelected($transaction_by, 'Bank')}>Bank</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Transaction By ID -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="transaction_by_id">Details</label>
                                        <div id="load_transaction_by">
                                            <select name="transaction_by_id" id="transaction_by_id" required class="form-control select2" style="width: 100%;">
                                                {$details}
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Date -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mdate">Date</label>
                                        <input required type="date" class="form-control" name="mdate" id="mdate" value="{$transaction_date}" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <input type="submit" name="kt_submit_button" id="kt_submit_button" class="btn btn-primary" value="Submit">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        HTML;

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
