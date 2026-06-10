<?php 
class LogContr extends LogReport {

    use SharedFunctionalityTrait;

    private $report_type; 
    private $date1; 
    private $date2; 
    private $related_id; 

    public function __construct($report_type, $related_id = NULL , $date1, $date2) {
        $this->report_type = $report_type;
        $this->date1 = $date1;
        $this->date2 = $date2;
        $this->related_id = $related_id;

    }

    public function Report() {



            if ($this->report_type === 'All') {  // Fixed typo from "Summery" to "Summary"
            return $this->AllUser($this->date1,$this->date2);
            } 


            if ($this->report_type === 'User-Wise') {  // Fixed typo from "Summery" to "Summary"
            return $this->SingleUser($this->related_id,$this->date1,$this->date2);
            } 


     return json_encode(["status" => "error", "message" => "Invalid report type."]);
    }
}
?>
