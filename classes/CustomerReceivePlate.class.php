<?php

class CustomerReceivePlate {
    private $id;

    public function __construct($id = 'New') {
        $this->id = $id;
    }

    public function SetupForm() {

        $List = new AddCustomer();


        $csrf_token = $_SESSION['csrf_token'] ?? ''; // Prevent undefined index error

        if ($this->id == 'New') {


            $customer_id = '';
            $discount = $receive = 0.00;
            $remarks = 'N/A';
            $transaction_by = 'Cash';
            $details = '<option value="Cash">Cash</option>';
            $transaction_date = $this->getCurrentDate();
            $transaction_type = 'debit';

        } else {


            $info = new Transaction();
            $data = $info->InvoiceDetails($this->id);
            $customer_id = $data['account_id'];
            $discount = ($data['transaction_type'] == 'debit') ? $data['amount'] : 0.00;
            $receive = ($data['transaction_type'] == 'credit') ? $data['amount'] : 0.00;
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
                                        <label for="customer_id">Customer Name</label>
                                        <select name="customer_id" id="customer_id" required class="form-control select2" style="width: 100%;">
                                            <option value="">Select One</option>
        HTML;

        foreach ($List->ListData() as $row) {
            $selected = ($customer_id == $row['id']) ? 'selected' : '';
            $content .= "<option value='{$row['id']}' {$selected}>{$row['customer_phone']} - {$row['customer_name']}</option>";
        }

        $content .= <<<HTML
                                        </select>
                                    </div>
                                </div>
        HTML;

// Display Discount and Receive Amount Fields
if ($this->id == 'New') {
    $content .= <<<HTML
    <div class="col-md-6">
        <div class="form-group">
            <label for="discount">Discount</label>
            <input required type="number" step="0.01" value="{$discount}" class="form-control" name="discount" id="discount" autocomplete="off">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="receive">Receive Amount</label>
            <input required type="number" step="0.01" value="{$receive}" class="form-control" name="receive" id="receive" autocomplete="off">
        </div>
    </div>
    HTML;
} else {
    if ($transaction_type == 'debit') {
        $content .= <<<HTML
        <div class="col-md-6">
            <div class="form-group">
                <label for="discount">Discount</label>
                <input required type="number" step="0.01" value="{$discount}" class="form-control" name="discount" id="discount" autocomplete="off">
            </div>
        </div>
        HTML;
    }else{

        $content .= <<<HTML
    <input required type="hidden"  value="0.00" name="discount" id="discount" > 
    HTML;
    }

    if ($transaction_type == 'credit') {
        $content .= <<<HTML
        <div class="col-md-6">
            <div class="form-group">
                <label for="receive">Receive Amount</label>
                <input required type="number" step="0.01" value="{$receive}" class="form-control" name="receive" id="receive" autocomplete="off">
            </div>
        </div>
        HTML;
    }else{
        $content .= <<<HTML
    <input required type="hidden"  value="0.00" name="receive" id="receive" > 
    HTML;

    }
}


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
