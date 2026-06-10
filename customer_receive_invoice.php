<?php
require_once __DIR__ . '/vendor/autoload.php';

use Mpdf\Mpdf;

error_reporting(1);
session_start();

// Redirect if not logged in
if (!isset($_SESSION['admin_access_name'])) {
    header('Location: login.php');
    exit();
}

include "includes/autoloader.inc.php";


$data = new Transaction();
$invoiceId = $_GET['invoice'];


// --- Decode the code param ---
$previous_due = 0;
$recent_due   = 0;

if (isset($_GET['code'])) {
    $decoded = base64_decode($_GET['code']);
    $json    = json_decode($decoded, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
        $previous_due = isset($json['previous_due']) ? (float)$json['previous_due'] : 0;
        $recent_due   = isset($json['recent_due']) ? (float)$json['recent_due'] : 0;
    }
}



$invoice_info = $data->InvoiceDetails($invoiceId);

$PaidNow = $invoice_info['amount'] ?? 0.00;

$invoice_date = DateTime::createFromFormat('Y-m-d', $invoice_info['transaction_date'])->format('d-m-Y');


$invoiceNumber = $invoice_info['invoice_no'];


$data2 = new AddCustomer();
$list2 = $data2->SingleData($invoice_info['account_id']);

$customerName = $list2['customer_name'];
$customerPhone = $list2['customer_phone'];
$customerAddress = $list2['customer_address'] ?? 'N/A';
$file_name = $invoiceNumber . '_' . $customerName;


// Get customer due


$invoicePrice = $invoice_info['amount'] ;
$invoiceInWords = $data->convertToBangladeshiWords(number_format($invoice_info['amount'], 0));

$PreviousDueFormatted = number_format($previous_due, 2, '.', '');

$runningDueFormatted = number_format($recent_due, 2, '.', '');





if($invoice_info['transaction_by'] == 'Cash' ){
$TransactionBy = "<span class='underline'>Cash</span>";
}else{

    $data3 = new BankSetup();
    $list3 = $data3->SingleBankData($invoice_info['transaction_by_id']);

    $TransactionBy = "<span class='underline'>$list3[bank_name]</span> A/C: <span class='underline'>$list3[account_no]</span>";

}




// Create mPDF
$mpdf = new Mpdf([
     'mode' => 'utf-8',
    'format' => 'A4',
    'orientation' => 'P', // 'P' for Portrait, 'L' for Landscape
    'margin_top' => 43,
    'margin_bottom' => 4,
]);




// Header
$mpdf->SetHTMLHeader('
<div style="text-align: center;">
    <img src="invoiceHeader.png" alt="Header Image">
    <br><h5 style="font-weight:100">Bengal Centre: 6th Floor 28 Topkhana Road, Dhaka-1000, Bangladesh.<br> 
Mobile: +8801820423888, Phone: +88029513196 | Email: timeplusmediline@gmail.com
<br><b>Hotline: 01897914343, 01339340058</b></h5></div>
');

// Footer
date_default_timezone_set('Asia/Dhaka');
$printDate = date("d-m-Y h:i:s A");



$html = "
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #000; }
    .receipt {
        width: 100%;
        padding: 10px 15px;
    }
    .receipt-header {
        border-bottom: 2px solid #444;
        padding-bottom: 5px;
        margin-bottom: 15px;
        text-align: center;
    }
    .title {
        font-size: 16pt;
        font-weight: bold;
        color: #006400;
        margin: 0;
    }
    .info, .summary {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
        font-size: 11pt;
    }
    .info td, .summary td {
        padding: 6px 8px;
        vertical-align: top;
    }
    .info td:first-child {
        font-weight: bold;
        width: 25%;
    }
    .underline {
        border-bottom: 1px dashed #555;
        display: inline-block;
        min-width: 120px;
        padding: 0 2px;
    }
    .amount-box {
        border: 1px solid #000;
        padding: 8px 20px;
        font-weight: bold;
        font-size: 13pt;
        display: inline-block;
        margin: 15px 0;
        background: #f9f9f9;
    }
    .amount-box2 {
        border: 1px solid #000;
        padding: 8px 20px;
        font-size: 13pt;
        display: inline-block;
        margin: 15px 0;
        background: #f9f9f9;
    }
    .footer {
        margin-top: 35px;
        font-size: 11pt;
        width: 100%;
    }
    .footer td {
        padding-top: 25px;
        text-align: center;
    }
    .copy-label {
        text-align:right;
        font-size:10pt;
        font-style:italic;
        color:#444;
        margin-bottom:5px;
    }
    .divider {
        border-top: 1px dotted #444;
        margin: 20px 0;
    }
</style>

<div class='receipt'>
    <div class='copy-label'>Customer Copy</div>
    <div class='receipt-header'>
        <div class='title'>MONEY RECEIPT</div>
    </div>

    <table class='info amount-box2' >
        <tr>
            <td>Receipt Code</td>
            <td><span class='underline'>$invoiceNumber</span></td>
            <td>Date</td>
            <td><span class='underline'>$invoice_date</span></td>
        </tr>
        <tr>
            <td>Received From</td>
            <td colspan='3'><span class='underline'>$customerName</span></td>
        </tr>
        <tr>
            <td>Contact</td>
            <td><span class='underline'>$customerPhone</span></td>
            <td>Address</td>
            <td><span class='underline'>$customerAddress</span></td>
        </tr>
        <tr>
            <td>Transaction Method</td>
            <td colspan='3'>$TransactionBy</td>
        </tr>
    </table>

    <table class='summary amount-box'>
        <tr>
            <td class='amount-box'>Previous Due</td>
            <td class='amount-box'>$PreviousDueFormatted</td>
        </tr>
        <tr>
            <td class='amount-box'>Amount Received</td>
            <td class='amount-box'>$PaidNow</td>
        </tr>
        <tr>
            <td class='amount-box'>Current Due</td>
            <td class='amount-box'>$runningDueFormatted</td>
        </tr>
    </table>

    <table class='footer'>
        <tr>
            <td>_______________________<br>Received by</td>
            <td>Printed on: $printDate</td>
        </tr>
    </table>
</div>

<div class='divider'></div>

<div class='receipt'>
    <div class='copy-label'>Office Copy</div>
    <div class='receipt-header'>
        <div class='title'>MONEY RECEIPT</div>
    </div>

    <table class='info amount-box2' >
        <tr>
            <td>Receipt Code</td>
            <td><span class='underline'>$invoiceNumber</span></td>
            <td>Date</td>
            <td><span class='underline'>$invoice_date</span></td>
        </tr>
        <tr>
            <td>Received From</td>
            <td colspan='3'><span class='underline'>$customerName</span></td>
        </tr>
        <tr>
            <td>Contact</td>
            <td><span class='underline'>$customerPhone</span></td>
            <td>Address</td>
            <td><span class='underline'>$customerAddress</span></td>
        </tr>
        <tr>
            <td>Transaction Method</td>
            <td colspan='3'>$TransactionBy</td>
        </tr>
    </table>

    <table class='summary amount-box'>
        <tr>
            <td class='amount-box'>Previous Due</td>
            <td class='amount-box'>$PreviousDueFormatted</td>
        </tr>
        <tr>
            <td class='amount-box'>Amount Received</td>
            <td class='amount-box'>$PaidNow</td>
        </tr>
        <tr>
            <td class='amount-box'>Current Due</td>
            <td class='amount-box'>$runningDueFormatted</td>
        </tr>
    </table>

    <table class='footer'>
        <tr>
            <td>_______________________<br>Received by</td>
            <td>Printed on: $printDate</td>
        </tr>
    </table>
</div>
";




// Output PDF
$mpdf->WriteHTML($html);
$mpdf->Output($file_name . '.pdf', 'I');
