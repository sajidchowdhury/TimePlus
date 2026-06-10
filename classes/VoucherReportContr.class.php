<?php 
class VoucherReportContr extends VoucherReport {

    use SharedFunctionalityTrait;

    private $report_type; 
    private $date1; 
    private $date2; 
    private $relatedid; 

    public function __construct($report_type,$relatedid, $date1, $date2) {
        $this->report_type = $report_type;
        $this->relatedid = $relatedid;
        $this->date1 = $date1;
        $this->date2 = $date2;
    }

    public function Report() {



        if ($this->report_type === 'Ledger-Wise') {  // Fixed typo from "Summery" to "Summary"
            return $this->LedgerWise($this->relatedid,$this->date1, $this->date2);
        } 



        if ($this->report_type === 'Account-Wise') {  // Fixed typo from "Summery" to "Summary"
            return $this->AccountWise($this->relatedid,$this->date1, $this->date2);
        } 


     return json_encode(["status" => "error", "message" => "Invalid report type."]);
    }
}
?>
