<?php 
class SalesReturnContr extends SalesReturn {

    use SharedFunctionalityTrait;

    private $report_type; 
    private $date1; 
    private $date2; 
    private $relatedid; 
    private $customer_id; 
    private $invoice_date; 
    private $sales_id; 

    public function __construct($report_type,$relatedid, $date1 = null , $date2 = null ,$customer_id = null  ,$invoice_date = null ,$sales_id = null ) {

        $this->report_type = $report_type;
        $this->relatedid = $relatedid;
        $this->date1 = $date1;
        $this->date2 = $date2;
        $this->customer_id = $customer_id;
        $this->invoice_date = $invoice_date;
        $this->sales_id = $sales_id;
    }


    public function Report() {

        if ($this->report_type === 'Invoice-Wise-Search') {  // Fixed typo from "Summery" to "Summary"
            return $this->SearchByInvoice($this->relatedid);
        } 


     if ($this->report_type === 'Customer-Wise') {  // Fixed typo from "Summery" to "Summary"
            return $this->SearchByCustomer($this->date1,$this->date2,$this->relatedid);
        } 



     return json_encode(["status" => "error", "message" => "Invalid report type."]);
    }




                public function Action() {


                return $this->CreateData($this->relatedid,$this->customer_id,$this->invoice_date,$this->sales_id);


                }




        public function DeleteAction() {



            if ($this->checkEditPermission(['delete'],'sales_return_report.php') !== true) {
            
            return ["status" => "error", "message" => 'Do not have permission to delete'];

            } 


            if($this->report_type == 'Invoice_Item' ){

            return $this->DeleteItem($this->relatedid);


            }

             if($this->report_type == 'Invoice' ){
            return $this->DeleteInvoice($this->relatedid);
            }





    }


}
?>
