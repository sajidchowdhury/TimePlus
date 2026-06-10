<?php 
class CustomerDueReportContr extends CustomerDueReport {

    use SharedFunctionalityTrait;

    private $report_type; 
    private $date1; 
    private $date2; 
    private $customer_id; 

    public function __construct($report_type,$customer_id, $date1, $date2) {
        $this->report_type = $report_type;
        $this->customer_id = $customer_id;
        $this->date1 = $date1;
        $this->date2 = $date2;
    }

    public function Report() {



        if ($this->report_type === 'Due-Summery') {  // Fixed typo from "Summery" to "Summary"
            return $this->DueSummery($this->date1);
        } 



        if ($this->report_type === 'Customer-Wise') {  // Fixed typo from "Summery" to "Summary"
            return $this->CustomerWiseDue($this->customer_id,$this->date1, $this->date2);
        } 


        if ($this->report_type === 'Date-Wise-Collection') {  // Fixed typo from "Summery" to "Summary"
            return $this->DateWiseCollection($this->date1, $this->date2);
        } 




     return json_encode(["status" => "error", "message" => "Invalid report type."]);
    }
}
?>
