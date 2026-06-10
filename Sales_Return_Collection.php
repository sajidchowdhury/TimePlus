<?php
// Include necessary files
include_once 'includes/config.inc.php';
include_once 'includes/autoloader.inc.php';

$data = new Sales();
$invoiceId = $_GET['related_id'];
$invoice_info = $data->InvoiceDetails($invoiceId);
$invoice_items = $data->InvoiceDetailsOnReturn($invoiceId);

$invoice_date = DateTime::createFromFormat('Y-m-d', $invoice_info['invoice_date'])->format('d-m-Y');
$invoiceNumber = $invoice_info['invoice_no'];
$invoiceDiscount = $invoice_info['discount'];

$customerName = $invoice_info['customer_name'];
$customerPhone = $invoice_info['customer_phone'];
$customerAddress = $invoice_info['customer_address'] ?? 'N/A';

// Start building HTML content
$content = "<div class='row'><div class='col-md-12'  ><div class='card card-primary' ><div class='card-header'><h3 class='card-title' >";
$content .= "Invoice Details<br>
<strong>Invoice Date:</strong> {$invoice_date} | <strong>Invoice No:</strong> {$invoiceNumber}<br>
<strong>Customer Name:</strong> {$customerName} | <strong>Phone:</strong> {$customerPhone}";
$content .= "</h3></div></div>";




$content .= '

<input type="hidden" id="customer_id" name="customer_id" value="'.$invoice_info['customer_id'].'">
<input type="hidden" id="invoice_date" name="invoice_date" value="'.$invoice_info['invoice_date'].'">
<input type="hidden" id="sales_id" name="sales_id" value="'.$invoice_info['id'].'">


';
$content .= "<table class='table table-bordered table-striped table-condensed' style='white-space:nowrap;'>
<tr>
    <th>Description</th>
    <th>Price (Discount ".$invoiceDiscount.") %</th>
    <th>Quantity</th>
    <th>Previous Return</th>
    <th>Return Now</th>
    <th>Reason</th>
</tr>";

foreach ($invoice_items as $index => $item) {
    $expiry_date = DateTime::createFromFormat('Y-m-d', $item['expiry_date'])->format('d-m-Y');
    $unit_price = $item['price'];
    $discounted_unit_price = $unit_price * (1 - ($invoiceDiscount / 100));

    $content .= "<tr>
        <td>
            {$item['productdescription']}<br>
            <b>Exp: {$expiry_date}</b><br>
            <small>Remaining: <span class='remaining-display badge bg-warning text-dark'>{$item['remaining_qty']}</span></small>
        </td>
        <td><b>After Discount: {$discounted_unit_price}</b><br>Actual Price: {$item['price']}</td>
        <td>{$item['quantity']}</td>
        <td><span class='previous-display'>{$item['returned_qty']}</span></td>
        <td>
            <input class='form-control return-now' type='number' min='0' value='0'
                data-product-id='{$item['product_id']}'
                data-product_name=\"{$item['productdescription']}\"
                data-remaining_qty='{$item['remaining_qty']}'
                data-expiry_date='{$item['expiry_date']}'
                data-previous='{$item['returned_qty']}'
                data-price='{$discounted_unit_price}'>
        </td>
        <td>
            <input class='form-control note' type='text' name='note[]' value='N/A'>
        </td>
    </tr>";
}


$content .= "<tr><td colspan='6' style='text-align:center'>
    <button type='button' id='submit-return' onclick='SalesReturn()' class='btn btn-danger'>Return Items</button>
</td></tr></table></div></div></div></div>";

// Append JS Script

print $content;
