<?php
require_once __DIR__ . '/vendor/autoload.php';

use Mpdf\Mpdf;

error_reporting(0);
session_start();

if (!isset($_SESSION['admin_access_name'])) {
    header('Location: login.php');
    exit();
}

include "includes/autoloader.inc.php";

$Printby = $_SESSION['admin_access_name'];

$data = new Sales();
$invoiceId = $_GET['invoice'];
$invoice_info = $data->InvoiceDetails($invoiceId);
$invoice_items = $data->InvoiceItems($invoiceId);

// Extract data
$discount_percentage = (float)($invoice_info['discount'] ?? 0.00);
$adjustment          = (float)($invoice_info['adjustment'] ?? 0.00);   // ← NEW
$PaidNow             = (float)($invoice_info['PaidNow'] ?? 0.00);

$invoice_date   = DateTime::createFromFormat('Y-m-d', $invoice_info['invoice_date'])->format('d-m-Y');
$invoiceNumber  = $invoice_info['invoice_no'];
$customerName   = $invoice_info['customer_name'];
$customerPhone  = $invoice_info['customer_phone'];
$customerAddress= $invoice_info['customer_address'] ?? 'N/A';
$file_name      = $invoiceNumber . '_' . $customerName;

// Customer Previous Due
$CustomerDue = new CustomerDueReport();
$ActualDue   = $CustomerDue->CustomerDueTillThisInvoice($invoice_info['customer_id'], $invoice_info['time'] ?? time());

$qty = 0;
$invoiceTotal = 0;
foreach ($invoice_items as $item) {
    $qty += (float)$item['Totalquantity'];
    $invoiceTotal += (float)$item['Totalquantity'] * (float)$item['price'];
}

// mPDF Setup
$mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'orientation' => 'P',
    'margin_top' => 43,
    'margin_bottom' => 4,
]);

// Header & Footer (unchanged)
$mpdf->SetHTMLHeader('
<div style="text-align: center;">
    <img src="invoiceHeader.png" alt="Header Image">
    <br><h5 style="font-weight:100">Bengal Centre: 6th Floor 28 Topkhana Road, Dhaka-1000, Bangladesh.<br> 
Mobile: +8801820423888, Phone: +88029513196 | Email: timeplusmediline@gmail.com
<br><b>Hotline: 01897914343, 01339340058</b></h5></div>
');

$printDate = date("d-m-Y h:i:s A");
$mpdf->SetHTMLFooter("
<table width='100%' style='font-size: 11;'>
    <tr><td width='33%' align='left'></td><td width='34%' align='left'></td><td width='33%' align='left'>Admin</td></tr>
    <tr><td width='33%' align='left'>______________</td><td width='34%' align='left'>____________</td><td width='33%' align='left'>____________</td></tr>
    <tr><td>Received by</td><td>Delivered by</td><td>Sales By</td></tr>
    <tr><td colspan='3'><hr></td></tr>
    <tr>
        <td>Printed on: $printDate</td>
        <td align='left'>Page {PAGENO} of {nbpg}</td>
        <td align='left'>Print by: $Printby</td>
    </tr>
</table>");

$html = "
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; }
    .invoice-box { width: 100%; border-collapse: collapse; }
    .invoice-box th, .invoice-box td { border: 1px solid black; padding: 6px; text-align: left; }
    .invoice-box th { background-color: #f2f2f2; }
    .text-right { text-align: right; }
</style>

<table class='invoice-box'>
    <tr><td><strong>Invoice</strong></td><td colspan='3'>$invoiceNumber</td><td class='text-right'><strong>Date</strong></td><td class='text-right'>$invoice_date</td></tr>
    <tr><td><strong>Customer</strong></td><td colspan='3'>$customerName</td><td class='text-right'><strong>Phone</strong></td><td class='text-right'>$customerPhone</td></tr>
    <tr><td><strong>Address</strong></td><td colspan='5'>$customerAddress</td></tr>
</table><br>

<table class='invoice-box'>
    <tr>
        <th>Ref</th><th>Description</th><th>Origin</th><th>Size</th><th>Quantity</th><th>Price</th><th>Total</th>
    </tr>";

$itemCount = 0;
$totalItems = count($invoice_items);
foreach ($invoice_items as $index => $item) {
    $itemCount++;
    $TotalPrice = (float)$item['Totalquantity'] * (float)$item['price'];

    $html .= "<tr>
        <td>{$item['code']}</td>
        <td>{$item['product_description']}</td>
        <td>{$item['product_orgin']}</td>
        <td>{$item['pack_size']}</td>
        <td>{$item['Totalquantity']}</td>
        <td>{$item['price']}</td>
        <td>$TotalPrice</td>
    </tr>";

    if ($itemCount % 22 == 0 && $index < ($totalItems - 1)) {
        $html .= "</table><pagebreak /><table class='invoice-box'><tr><th>Ref</th><th>Description</th><th>Origin</th><th>Size</th><th>Quantity</th><th>Price</th><th>Total</th></tr>";
    }
}

// Calculations
$discount_amount = $invoiceTotal * ($discount_percentage / 100);
$invoicePrice    = $invoiceTotal - $discount_amount - $adjustment;
$due             = $invoicePrice - $PaidNow;

$PreviousDue = (float)($ActualDue['total_due'] ?? 0);
$PreviousDue = max(0, $PreviousDue);
$runningDue  = $PreviousDue + $due;

$invoiceInWords = $data->convertToBangladeshiWords($invoicePrice);

// Summary
$html .= "
<tr>
    <td colspan='4' class='text-right'><strong>Total</strong></td>
    <td><strong>$qty</strong></td><td></td><td><strong>" . number_format($invoiceTotal, 2) . "</strong></td>
</tr>
<tr>
    <td colspan='5'><strong>In Words:</strong> $invoiceInWords Tk. Only</td>
    <td class='text-right'><strong>Sub Total :</strong></td>
    <td class='text-right'>" . number_format($invoiceTotal, 2) . "</td>
</tr>
<tr>
    <td colspan='4'><strong>Previous Due :</strong> " . number_format($PreviousDue, 2) . "</td>
    <td colspan='2' class='text-right'><strong>Discount ({$discount_percentage}%):</strong></td>
    <td class='text-right'>" . number_format($discount_amount, 2) . "</td>
</tr>
<tr>
    <td colspan='4'></td>
    <td colspan='2' class='text-right'><strong>Adjustment / Extra Discount :</strong></td>
    <td class='text-right'>" . number_format($adjustment, 2) . "</td>
</tr>
<tr>
    <td colspan='4'><strong>Current Due :</strong> " . number_format($due, 2) . "</td>
    <td colspan='2' class='text-right'><strong>Invoice Price :</strong></td>
    <td class='text-right'>" . number_format($invoicePrice, 2) . "</td>
</tr>
<tr>
    <td colspan='4'><strong>Total Due :</strong> " . number_format($runningDue, 2) . "</td>
    <td colspan='2' class='text-right'><strong>Paid :</strong></td>
    <td class='text-right'>" . number_format($PaidNow, 2) . "</td>
</tr>
<tr>
    <td colspan='4'></td>
    <td colspan='2' class='text-right'><strong>Due :</strong></td>
    <td class='text-right'>" . number_format($due, 2) . "</td>
</tr>
</table>";

$mpdf->WriteHTML($html);
$mpdf->Output($file_name . '.pdf', 'I');