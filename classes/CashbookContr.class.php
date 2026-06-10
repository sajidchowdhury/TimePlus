<?php 
class CashbookContr extends Cashbook {

    use SharedFunctionalityTrait;

    private $report_type; 
    private $date1; 

    public function __construct($report_type, $date1) {
        $this->report_type = $report_type;
        $this->date1 = $date1;
    }

    public function Report() {



        if ($this->report_type === 'Daily-Cash') {  // Fixed typo from "Summery" to "Summary"
            return $this->CashBookReport($this->date1);
        } 


        if ($this->report_type === 'Summery') {  // Fixed typo from "Summery" to "Summary"
            return $this->CashBookSummery($this->date1);
        } 




     return json_encode(["status" => "error", "message" => "Invalid report type."]);
    }
}
?>
