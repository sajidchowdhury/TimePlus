<?php 
class PurchaseContr extends Purchase {

    use SharedFunctionalityTrait;

    private $invoice;
    private $supplier_id;
    private $cart_data;
    private $user_id;
    private $related_id; 
    private $prev_invoice_date; 
    private $invoice_date; 


    public function __construct($related_id, $invoice = null, $supplier_id = null, $prev_invoice_date = null, $invoice_date = null, $cart_data = [], $user_id = null) {



        $this->invoice = $invoice;
        $this->supplier_id = $supplier_id;
        $this->cart_data = $cart_data;
        $this->user_id = $user_id;
        $this->related_id = $related_id;
        $this->prev_invoice_date =  $prev_invoice_date;
        $this->invoice_date = $invoice_date;

    }

    public function Action() {
 



        if ($this->related_id === 'New') {


            if ($this->invoice_date !== date('Y-m-d') && $this->checkEditPermission(['backdate'], 'purchase.php') !== true) {

            return ["status" => "error", "message" => 'Do not have permission to change date'];

            exit(); // Stop further execution
            } 



            return $this->CreateData($this->invoice, $this->supplier_id, $this->invoice_date, $this->cart_data, $this->user_id);
        } 
        

 // Checking permission for backdate editing
    if ($this->checkEditPermission(['edit'], 'purchase.php') !== true) {
        
        return ["status" => "error", "message" => 'Do not have permission to Edit'];
        exit(); // Stop further execution
    } 


    // Checking permission for backdate editing
    if ($this->invoice_date != $this->prev_invoice_date && $this->checkEditPermission(['backdate'], 'purchase.php') !== true) {
        
        return ["status" => "error", "message" => 'Do not have permission to change date. Refresh for invoice date'];
        exit(); // Stop further execution
    } 

    
           return $this->UpdateData($this->invoice, $this->supplier_id, $this->invoice_date, $this->cart_data, $this->user_id, $this->related_id);


    }



           public function DeleteAction() {


            // Checking permission for backdate editing
            if ($this->checkEditPermission(['edit'], 'purchase.php') !== true) {

            return ["status" => "error", "message" => 'Do not have permission to Edit'];
            exit(); // Stop further execution
            } 
    

            if ($this->checkEditPermission(['delete'],'purchase.php') !== true) {
            
            return ["status" => "error", "message" => 'Do not have permission to delete'];

            } 
            return $this->DeleteItem($this->related_id);


    }


          public function DeleteFullInvoice() {

            if ($this->checkEditPermission(['delete'],'purchase_report.php') !== true) {
            
            return ["status" => "error", "message" => 'Do not have permission to delete'];

            } 
            return $this->DeleteInvoice($this->related_id);


    }



    
}
