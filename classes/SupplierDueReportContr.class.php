<?php 
class SupplierDueReportContr extends SupplierDueReport {

    use SharedFunctionalityTrait;

    private $report_type; 
    private $date1; 
    private $date2; 
    private $supplier_id; 

    public function __construct($report_type,$supplier_id, $date1, $date2) {
        $this->report_type = $report_type;
        $this->supplier_id = $supplier_id;
        $this->date1 = $date1;
        $this->date2 = $date2;
    }

    public function Report() {



        if ($this->report_type === 'Due-Summery') {  // Fixed typo from "Summery" to "Summary"
            return $this->DueSummery($this->date1);
        } 



        if ($this->report_type === 'Supplier-Wise') {  // Fixed typo from "Summery" to "Summary"
            return $this->SupplierWiseDue($this->supplier_id,$this->date1, $this->date2);
        } 


     return json_encode(["status" => "error", "message" => "Invalid report type."]);
    }
}
?>
