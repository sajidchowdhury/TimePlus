<?php
require_once __DIR__ . '/vendor/autoload.php';

use Mpdf\Mpdf;

error_reporting(0);
session_start();

// Redirect if not logged in
if (!isset($_SESSION['admin_access_name'])) {
    header('Location: login.php');
    exit();
}

include "includes/autoloader.inc.php";

$Printby = $_SESSION['admin_access_name'];

$data = new SalesReturn();
$invoiceId = $_GET['invoice'];
$invoice_info = $data->InvoiceDetails($invoiceId);

$invoice_items = $data->InvoiceItems($invoiceId);
// Extract and validate data
$discount_percentage =  0.00;
$sales_person = $invoice_info['salesPerson'] ?? '';
$PaidNow =  0.00;

$invoice_date = DateTime::createFromFormat('Y-m-d', $invoice_info['invoice_date'])->format('d-m-Y');
$invoiceNumber = $invoice_info['invoice_no'];
$customerName = $invoice_info['customer_name'];
$customerPhone = $invoice_info['customer_phone'];
$customerAddress = $invoice_info['customer_address'] ?? 'N/A';
$file_name = $invoiceNumber . '_' . $customerName;


// Get customer due
$CustomerDue = new CustomerDueReport();
$ActualDue = $CustomerDue->CustomerDueTillThisInvoice($invoice_info['customer_id'],$invoice_info['time']);

// Calculate total and quantity
$total = 0;
$qty = 0;
foreach ($invoice_items as $item) {
    $total += $item['total_price'];
    $qty += $item['Totalquantity'];
}

// Discount and due calculations
$discount = $total * ($discount_percentage / 100);
$invoicePrice = $total - $discount;
$due = $invoicePrice - $PaidNow;

$discountFormatted = number_format($discount, 2, '.', '');
$invoicePriceFormatted = number_format($invoicePrice, 2, '.', '');
$totalFormatted = number_format($total, 2, '.', '');
$paidFormatted = number_format($PaidNow, 2, '.', '');
$dueFormatted = number_format($due, 2, '.', '');

// Due calculations
$PreviousDue = $ActualDue['total_due'];
$PreviousDue = ($PreviousDue > 0) ? $PreviousDue : 0.00;
$runningDue = $PreviousDue + $due;

$PreviousDueFormatted = number_format($PreviousDue, 2, '.', '');
$runningDueFormatted = number_format($runningDue, 2, '.', '');


$invoiceInWords = $data->convertToBangladeshiWords($invoicePrice);

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

$mpdf->SetHTMLFooter("
<table width='100%' style='font-size: 11;'>
    <tr>
        <td width='33%' align='left' > </td>
        <td width='34%' align='left'  ></td>
        <td width='33%' align='left'  > Admin </td>
    </tr>
    <tr>
        <td width='33%' align='left'>______________</td>
        <td width='34%' align='left'></td>
        <td width='33%' align='left' > ____________</td>
    </tr>
    <tr>
        <td width='33%' align='left'>Received by</td>
        <td width='34%' align='left'></td>
        <td width='33%' align='left'> Sales By</td>
    </tr>
    <tr><td colspan='3'><hr></td></tr>
    <tr>
        <td width='33%'>Printed on: $printDate</td>
        <td width='34%' align='left'>Page {PAGENO} of {nbpg}</td>
        <td width='33%' align='left'>Print by: $Printby</td>
    </tr>
</table>
");

$html = "
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; }
    .invoice-box { width: 100%; border-collapse: collapse; }
    .invoice-box th, .invoice-box td { border: 1px solid black; padding: 6px; text-align: left; }
    .invoice-box th { background-color: #f2f2f2; }
</style>

<table class='invoice-box'>

        <tr>
        <td colspan='6' style='text-align:center;color:Red'> <strong>SALES RETURN INVOICE</strong></td>
    </tr>

    <tr>
        <td><strong>Invoice</strong></td>
        <td colspan='3'>$invoiceNumber</td>
        <td style='text-align:right;'><strong>Date</strong></td>
        <td style='text-align:right;'>$invoice_date</td>
    </tr>
    <tr>
        <td><strong>Customer</strong></td>
        <td colspan='3'>$customerName</td>
        <td style='text-align:right;'><strong>Phone</strong></td>
        <td style='text-align:right;'>$customerPhone</td>
    </tr>
    <tr>
        <td><strong>Address</strong></td>
        <td colspan='5'>$customerAddress</td>
</tr>

</table>
<br>
<table class='invoice-box'>
    <tr>
        <th>Ref</th>
        <th>Description</th>
        <th>Origin</th>
        <th>Size</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Total</th>
    </tr>";

$itemCount = 0;
$totalItems = count($invoice_items);

foreach ($invoice_items as $index => $item) {
    $itemCount++;

    $html .= "<tr>
        <td>{$item['code']}</td>
        <td>{$item['product_description']}</td>
        <td>{$item['product_orgin']}</td>
        <td>{$item['pack_size']}</td>
        <td>{$item['Totalquantity']}</td>
        <td>{$item['price']}</td>
        <td>{$item['total_price']}</td>
    </tr>";

    // Add page break after every 20 products unless it's the last product
    if ($itemCount % 22 == 0 && $index < ($totalItems - 1)) {
        $html .= "</table><pagebreak /><table class='invoice-box'>
        <tr>
            <th>Ref</th>
            <th>Description</th>
            <th>Origin</th>
            <th>Size</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Total</th>
        </tr>";
    }
}

// Append the summary (footer rows) only once, after all products
$html .= "<tr>
    <td colspan='4' style='text-align:right;border-bottom:1.5px solid black'><strong>Total</strong></td>
    <td><strong>$qty</strong></td>
    <td></td>
    <td><strong>$totalFormatted</strong></td>
</tr>

</table>";

// Output PDF
$mpdf->WriteHTML($html);
$mpdf->Output($file_name . '.pdf', 'I');
