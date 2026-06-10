<?php
include_once "autoloader.inc.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $report_type = $_POST['report_type'] ?? '';
    $date_from = $_POST['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? '';
    $relatedid = $_POST['relatedid'] ?? '';
    $report_name = $_POST['report_name'] ?? '';


    if ($report_type === '') {
        echo "<div class='alert alert-danger'>Select Report Type</div>";
        exit;
    }


    if ($report_name == 'Product Movement Report') {

        $info = new ProductMovementContr($report_type,$relatedid, $date_from, $date_to); 
     
    }
    
    
    if ($report_name == 'Customer Due Report') {

        $info = new CustomerDueReportContr($report_type,$relatedid, $date_from, $date_to); 
     
    }


       if ($report_name == 'Supplier Due Report') {

        $info = new SupplierDueReportContr($report_type,$relatedid, $date_from, $date_to); 
    }


    if ($report_name == 'Voucher Wise Report') {

    $info = new VoucherReportContr($report_type,$relatedid, $date_from, $date_to); 
    
    }


    if ($report_name == 'Purchase Report') {

    $info = new PurchaseReportContr($report_type, $date_from, $date_to); 
    
    }


    if ($report_name == 'Sales Report') {

    $info = new SalesReportContr($relatedid,$report_type, $date_from, $date_to); 
    
    }

    if ($report_name == 'Sales Return Report') {

    $info = new SalesReturnReportContr($relatedid,$report_type, $date_from, $date_to); 
    
    }


    if ($report_name == 'Stock Report') {

    $info = new StockContr($report_type, $date_from, $date_to); 
    
    }

     if ($report_name == 'Cashbook Report') {

    $info = new CashbookContr($report_type, $date_from); 
    
    }


     if ($report_name == 'Log Report') {

    $info = new LogContr($report_type, $relatedid , $date_from, $date_to); 
    
    }


     if ($report_name == 'Sales Return') {

    $info = new SalesReturnContr($report_type, $relatedid , $date_from, $date_to); 
    
    }
    
    









         $results = $info->Report(); 

        echo $results ;
        exit();


        $reportData = "<div class='alert alert-danger'>Invalid report type</div>";
    
    echo $reportData;
} else {
    echo "<div class='alert alert-danger'>Invalid request method</div>";
}
?>
