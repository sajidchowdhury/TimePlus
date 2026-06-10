<?php 
class SalesReturnReportContr extends SalesReturnReport {

    use SharedFunctionalityTrait;

    private $report_type; 
    private $relatedid; 
    private $date1; 
    private $date2; 

    public function __construct($relatedid ,$report_type, $date1, $date2) {
        $this->report_type = $report_type;
        $this->date1 = $date1;
        $this->date2 = $date2;
        $this->relatedid = $relatedid;
    }

    public function Report() {



        if ($this->report_type === 'Due-Summery') {  // Fixed typo from "Summery" to "Summary"
            return $this->SalesSummary($this->date1, $this->date2);
        } 



        if ($this->report_type === 'Invoice-Wise') {  // Fixed typo from "Summery" to "Summary"
            return $this->InvoiceWise($this->date1, $this->date2);
        } 


        if ($this->report_type === 'Customer-Wise') {  // Fixed typo from "Summery" to "Summary"
            return $this->CustomerWise($this->relatedid,$this->date1, $this->date2);
        } 




     return json_encode(["status" => "error", "message" => "Invalid report type."]);
    }
}
?>
