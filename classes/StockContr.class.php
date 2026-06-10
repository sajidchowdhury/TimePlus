<?php 
class StockContr extends Stock {


    private $report_type; 
    private $related_id; 
    private $date1; 
    private $date2; 

    public function __construct($report_type, $related_id = null , $date1 = null, $date2 = null) {
        $this->report_type = $report_type;
        $this->related_id = $related_id ?? 'All';
        $this->date1 = $date1 ?? date('Y-m-d'); // Set default date if null
        $this->date2 = $date2 ?? date('Y-m-d'); // Set default date if null
    }
    

    public function Report() {


if ($this->report_type === 'Stock-By-Expiry') {
    return $this->SingleProductStockByExpiry($this->related_id);
}




        if ($this->report_type === 'Single-Product-Stock') {  // Fixed typo from "Summery" to "Summary"
        return $this->SingleProductStock($this->related_id);
        } 

        if ($this->report_type === 'Summery') {  // Fixed typo from "Summery" to "Summary"
            return $this->TodayStockSummery();
            } 

     return json_encode(["status" => "error", "message" => "Invalid report type."]);
    }
}
?>
