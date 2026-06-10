<?php 

class SalesContr extends Sales {



    use SharedFunctionalityTrait;



private $invoice;
    private $customer_id;
    private $cart_data;
    private $user_id;
    private $related_id; 
    private $prev_invoice_date; 
    private $invoice_date; 
    private $discount; 
    private $adjustment;      // ← New
    private $sales_person; 
    private $receive_now; 
    private $transaction_by_id;



    public function __construct(
        $related_id, 
        $invoice = null, 
        $customer_id = null, 
        $receive_now = 0.00, 
        $transaction_by_id = null, 
        $sales_person = null, 
        $prev_invoice_date = null, 
        $invoice_date = null, 
        $discount = 0.00, 
        $adjustment = 0.00,        // ← New
        $cart_data = [], 
        $user_id = null
        ) {

        $this->invoice           = $invoice;
        $this->customer_id       = $customer_id;
        $this->cart_data         = $cart_data;
        $this->user_id           = $user_id;
        $this->related_id        = $related_id;
        $this->prev_invoice_date = $prev_invoice_date;
        $this->invoice_date      = $invoice_date;
        $this->sales_person      = $sales_person;
        $this->discount          = $discount;
        $this->adjustment        = $adjustment;           // ← New
        $this->receive_now       = $receive_now;
        $this->transaction_by_id = $transaction_by_id;

    }



   
    public function Action() {

        date_default_timezone_set('Asia/Dhaka');

        // New Invoice
        if ($this->related_id === 'New') {         

            if ($this->invoice_date !== date('Y-m-d') && $this->checkEditPermission(['backdate'], 'sales.php') !== true) {
                return ["status" => "error", "message" => 'Do not have permission to change date'];
            } 

            return $this->CreateData(
                $this->invoice, 
                $this->customer_id,
                $this->receive_now,  
                $this->transaction_by_id , 
                $this->sales_person, 
                $this->invoice_date, 
                $this->discount, 
                $this->adjustment,           // ← Passed correctly
                $this->cart_data, 
                $this->user_id
            );
        } 

        // Edit Invoice - Permission checks
        if ($this->checkEditPermission(['edit'], 'sales.php') !== true) {
            return ["status" => "error", "message" => 'Do not have permission to Edit'];
        } 

        if ($this->invoice_date != $this->prev_invoice_date && $this->checkEditPermission(['backdate'], 'sales.php') !== true) {
            return ["status" => "error", "message" => 'Do not have permission to change date. Refresh for invoice date'];
        } 

        return $this->UpdateData(
            $this->invoice, 
            $this->customer_id, 
            $this->receive_now,  
            $this->transaction_by_id , 
            $this->sales_person , 
            $this->invoice_date, 
            $this->discount, 
            $this->adjustment,               // ← Passed correctly
            $this->cart_data, 
            $this->user_id, 
            $this->related_id
        );
    }
    


        public function DeleteAction() {
            // Checking permission for backdate editing
            if ($this->checkEditPermission(['edit'], 'sales.php') !== true) {
            return ["status" => "error", "message" => 'Do not have permission to Edit'];
            exit(); // Stop further execution
            } 



            if ($this->checkEditPermission(['delete'],'sales.php') !== true) {
            return ["status" => "error", "message" => 'Do not have permission to delete'];
            } 
            return $this->DeleteItem($this->related_id);
    }


          public function DeleteFullInvoice() {

            if ($this->checkEditPermission(['delete'],'sales_report.php') !== true) {
            return ["status" => "error", "message" => 'Do not have permission to delete'];
            } 
            return $this->DeleteInvoice($this->related_id);

    }

}

