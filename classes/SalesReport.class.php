<?php 
class SalesReport extends Dbh {

        use SharedFunctionalityTrait;



    protected function SalesSummary($date1, $date2) {
    $conn = $this->connect(); // Get DB connection



    $ReportName = "Sales Report Summery:: From $date1 $date2 " ; 
    $content = '<div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">'.$ReportName.'</h3>
            </div><div class="card-body"><div class="table-responsive">';

    $date1 = DateTime::createFromFormat('d/m/Y', $date1)->format('Y-m-d');
    $date2 = DateTime::createFromFormat('d/m/Y', $date2)->format('Y-m-d');

    $query = "
        SELECT pi.id, pi.total_amount, pi.discount, C.customer_name, pi.invoice_no, pi.customer_id, 
        DATE_FORMAT(pi.invoice_date, '%d/%m/%Y') AS invoice_date
        FROM sales_invoices pi
        JOIN setup_customer C ON pi.customer_id = C.id
        WHERE pi.invoice_date BETWEEN :date1 AND :date2
        GROUP BY pi.invoice_no, pi.customer_id, pi.invoice_date
        ORDER BY pi.invoice_date DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":date1", $date1, PDO::PARAM_STR);
    $stmt->bindValue(":date2", $date2, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all rows

    if (count($result) > 0) {
        $content .= "<table class='table table-bordered' id='example'>
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Sub Total</th>
                        <th>Discount%</th>
                        <th>Discount</th>
                        <th>Invoice Price</th>
                        <th class='no-export'>Action</th>
                    </tr>
                </thead>
                <tbody>";

        $total_amount_sum = 0;
        $total_discount_sum = 0;
        $total_invoice_sum = 0;

        foreach ($result as $row) {
            // Calculations
            $total_amount = $row['total_amount'];
            $discount = $row['discount'];
            $actual_discount = ($discount > 0) ? ($total_amount * ($discount / 100)) : 0.00;
            $invoicePrice = $total_amount - $actual_discount;

            $content .= "<tr id=row-{$row['id']}>
                        <td>{$row['invoice_no']}</td>
                        <td>{$row['customer_name']}</td>
                        <td>{$row['invoice_date']}</td>
                        <td>{$total_amount}</td>
                        <td>{$discount}</td>    
                        <td>{$actual_discount}</td>
                        <td>{$invoicePrice}</td>
                        <td class='no-export'>
                            <div class='btn-group'>
                                <a href='sales.php?id={$row['id']}' target='_blank' class='btn btn-info'>
                                    <i class='fas fa-pencil-alt'></i>
                                </a>
                                <a href='invoice.php?invoice={$row['id']}' target='_blank' class='btn btn-warning'>
                                    <i class='far fa-file-alt'></i>
                                </a>
                                <button type='button' class='btn btn-danger delete-btn' data-item_id='{$row['id']}'>
                                    <i class='far fa-trash-alt'></i>
                                </button>
                            </div>
                        </td>
                    </tr>";

            $total_amount_sum += $total_amount;
            $total_discount_sum += $actual_discount;
            $total_invoice_sum += $invoicePrice;
        }

        $content .= "</tbody>
            <tfoot>
                <tr>
                    <td colspan='3'><strong>Total</strong></td>
                    <td><strong>{$total_amount_sum}</strong></td>
                    <td></td>
                    <td><strong>{$total_discount_sum}</strong></td>
                    <td><strong>{$total_invoice_sum}</strong></td>
                    <td class='no-export'></td>
                </tr>
            </tfoot>
        </table></div></div>";
    } else {
        $content .= "<p>No records found for the selected date range.</p>";
    }

    // Add DataTables and Export functionality scripts
       $content .= $this->LoadExportScript($ReportName) ; 

    return $content;
}
protected function InvoiceWise($date1, $date2) {
    $conn = $this->connect(); // DB connection

    // Report title
    $ReportName = "Invoice Wise Sales Report :: From $date1 to $date2";

    // Convert date format (d/m/Y → Y-m-d)
    $date1 = DateTime::createFromFormat('d/m/Y', $date1)->format('Y-m-d');
    $date2 = DateTime::createFromFormat('d/m/Y', $date2)->format('Y-m-d');

    // === Decide previous date:
    // Typically "previous balance" = stock as of 1 day before date1 (opening balance).
    // If you want previous as 1 day before date2, change the next line to use $date2.
    $previous_date = date('Y-m-d', strtotime($date1 . ' -1 day'));
    // $previous_date = date('Y-m-d', strtotime($date2 . ' -1 day')); // <-- alternative

    // Single SQL: get opening (prev), sales-in-range, and closing (to date2) per product
    $query = "
    SELECT
      p.id AS product_id,
      p.product_description AS product_name,
      p.pack_size,

      -- previous balance (purchases <= previous_date) - (sales <= previous_date) + (returns <= previous_date)
      COALESCE(pur_prev.total_purchase,0)
      - COALESCE(sal_prev.total_sales,0)
      + COALESCE(ret_prev.total_return,0) AS previous_balance,

      -- sales between date1 and date2 (inclusive)
      COALESCE(sales_range.total_sales,0) AS sales_qty,

      -- current balance up to date2
      COALESCE(pur_to.total_purchase,0)
      - COALESCE(sal_to.total_sales,0)
      + COALESCE(ret_to.total_return,0) AS current_balance

    FROM setup_product p

    -- purchases up to previous_date
    LEFT JOIN (
      SELECT pii.product_id, SUM(pii.quantity) AS total_purchase
      FROM purchase_invoices_items pii
      JOIN purchase_invoices pi ON pi.id = pii.invoice_id
      WHERE pi.invoice_date <= :previous_date
      GROUP BY pii.product_id
    ) pur_prev ON pur_prev.product_id = p.id

    -- sales up to previous_date
    LEFT JOIN (
      SELECT sii.product_id, SUM(sii.quantity) AS total_sales
      FROM sales_invoices_items sii
      JOIN sales_invoices si ON si.id = sii.invoice_id
      WHERE si.invoice_date <= :previous_date
      GROUP BY sii.product_id
    ) sal_prev ON sal_prev.product_id = p.id

    -- returns up to previous_date
    LEFT JOIN (
      SELECT sri.product_id, SUM(sri.quantity) AS total_return
      FROM sales_return_items sri
      JOIN sales_return sr ON sr.id = sri.invoice_id
      WHERE sr.invoice_date <= :previous_date
      GROUP BY sri.product_id
    ) ret_prev ON ret_prev.product_id = p.id

    -- sales between date1 and date2 (range)
    LEFT JOIN (
      SELECT sii.product_id, SUM(sii.quantity) AS total_sales
      FROM sales_invoices_items sii
      JOIN sales_invoices si ON si.id = sii.invoice_id
      WHERE si.invoice_date BETWEEN :date1 AND :date2
      GROUP BY sii.product_id
    ) sales_range ON sales_range.product_id = p.id

    -- purchases up to date2
    LEFT JOIN (
      SELECT pii.product_id, SUM(pii.quantity) AS total_purchase
      FROM purchase_invoices_items pii
      JOIN purchase_invoices pi ON pi.id = pii.invoice_id
      WHERE pi.invoice_date <= :date2
      GROUP BY pii.product_id
    ) pur_to ON pur_to.product_id = p.id

    -- sales up to date2
    LEFT JOIN (
      SELECT sii.product_id, SUM(sii.quantity) AS total_sales
      FROM sales_invoices_items sii
      JOIN sales_invoices si ON si.id = sii.invoice_id
      WHERE si.invoice_date <= :date2
      GROUP BY sii.product_id
    ) sal_to ON sal_to.product_id = p.id

    -- returns up to date2
    LEFT JOIN (
      SELECT sri.product_id, SUM(sri.quantity) AS total_return
      FROM sales_return_items sri
      JOIN sales_return sr ON sr.id = sri.invoice_id
      WHERE sr.invoice_date <= :date2
      GROUP BY sri.product_id
    ) ret_to ON ret_to.product_id = p.id

    ORDER BY p.product_description ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(':previous_date', $previous_date, PDO::PARAM_STR);
    $stmt->bindValue(':date1', $date1, PDO::PARAM_STR);
    $stmt->bindValue(':date2', $date2, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build HTML output
    $content = '<div class="card card-primary">
        <div class="card-header"><h3 class="card-title">'.$ReportName.'</h3></div>
        <div class="card-body"><div class="table-responsive">';

    if (count($rows) > 0) {
        $content .= "<table class='table table-bordered' id='example'>
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Item Name</th>
                    <th>Pack Size</th>
                    <th>Sales <br> ($date1 - $date2)</th>
                    <th>Current Balance <br>(till $date2)</th>
                    <th>Previous Balance <br>(till $previous_date)</th>

                </tr>
            </thead>
            <tbody>";

        $sl = 1;
        foreach ($rows as $r) {
            $content .= '<tr>
                <td>'.$sl++.'</td>
                <td>'.htmlspecialchars($r['product_name']).'</td>
                <td>'.htmlspecialchars($r['pack_size']).'</td>
                <td style="text-align:right;">'.(int)$r['sales_qty'].'</td>
                <td style="text-align:right;">'.(int)$r['current_balance'].'</td>
                <td style="text-align:right;">'.(int)$r['previous_balance'].'</td>
            </tr>';
        }

        $content .= "</tbody>
            <tfoot>
                <tr style='font-weight:bold; background:#f0f0f0;'>
                    <td colspan='6' class='text-center'>End of Report</td>
                </tr>
            </tfoot>
        </table></div></div>";
    } else {
        $content .= "<p>No records found for the selected date range.</p>";
    }

    $content .= $this->LoadExportScript($ReportName, 'false');
    return $content;
}


protected function CustomerWise($customer_id,$date1, $date2) {

    $conn = $this->connect(); // Get DB connection


    $ReportName = "Customer Wise Sales Report:: From $date1 to $date2 " ; 



    $content = '<div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">'.$ReportName.'</h3>
            </div><div class="card-body"><div class="table-responsive">';


        $date1 = DateTime::createFromFormat('d/m/Y', $date1)->format('Y-m-d');
$date2 = DateTime::createFromFormat('d/m/Y', $date2)->format('Y-m-d');




    // Query to fetch invoice information and item details
    $query = "
       SELECT 
    pi.total_amount,
    pi.id,
    pi.invoice_no, 
    DATE_FORMAT(pi.invoice_date, '%d/%m/%Y') AS invoice_date, 
    C.customer_name AS customer_name,
    E.employee_name AS emp_name,
    IFNULL(at_discount.amount, 0) AS DiscountAmount,
    IFNULL(at_receive.amount, 0) AS ReceiveAmount
FROM sales_invoices pi
JOIN setup_customer C ON pi.customer_id = C.id
JOIN admin E ON pi.poster = E.id
LEFT JOIN account_transaction at_discount ON pi.discount_transaction_id = at_discount.id
LEFT JOIN account_transaction at_receive ON pi.receive_now_id = at_receive.id
WHERE (pi.invoice_date BETWEEN :date1 AND :date2 ) AND pi.customer_id = :customer_id
ORDER BY pi.invoice_date DESC

    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":date1", $date1, PDO::PARAM_STR);
    $stmt->bindValue(":date2", $date2, PDO::PARAM_STR);
    $stmt->bindValue(":customer_id", $customer_id, PDO::PARAM_STR);
    $stmt->execute();
    $invoice_info = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all invoice data

    if (count($invoice_info) > 0) {
        $content .= "<table class='table table-bordered' id='example'>
                <thead>
                    <tr>
                        <th>Invoice No.</th> 
                        <th>Date</th> 
                        <th>Customer Name</th> 
                        <th>Employee Name</th> 
                        <th>Product Name</th> 
                        <th>VAT</th> 
                        <th>Transport Cost</th> 
                        <th>Price</th> 
                        <th>Quantity</th> 
                        <th>Total</th> 
                        <th class='no-export'>Action</th>
                    </tr>
                </thead>
                <tbody>";

$total_grand_qty = 0;
$total_grand_amount = 0;
$total_grand_paid = 0;
$total_grand_due = 0;


        // Loop through each invoice
        foreach ($invoice_info as $invoice) {
            $report = '';

            // Get product details for the current invoice
            $query_items = "
               SELECT 
                    pii.product_id, 
                    CONCAT(p.product_description,' ' , p.pack_size) AS product_name, 

                    pii.quantity, 
                    pii.price
                FROM sales_invoices_items pii
                JOIN setup_product p ON pii.product_id = p.id
                WHERE pii.invoice_id = :invoice_id
            ";

            $stmt_items = $conn->prepare($query_items);
            $stmt_items->bindValue(":invoice_id", $invoice['id'], PDO::PARAM_STR);
            $stmt_items->execute();
            $fetch_list2 = $stmt_items->fetchAll(PDO::FETCH_ASSOC); // Fetch all product data for the invoice

            $total_qty = 0;
            $total_price = 0;

            // Add invoice details row
            $report .= '<tr>
                <td>' . $invoice['invoice_no'] . '</td>
                <td>' . $invoice['invoice_date'] . '</td>
                <td>' . $invoice['customer_name'] . '</td>
                <td>' . $invoice['emp_name'] . '</td>
                <td></td>
                <td>0</td>
                <td>0</td>
                <td></td>
                <td></td>
                <td></td>
                <td class="no-export"><a href="sales_invoice_copy.php?purches_type=fg_local_purches&code=' . $invoice['invoice_no'] . '" target="_BLINK"><span class="fa fa-file-text"></span></a></td>
            </tr>';

            // Loop through each product in the invoice
            foreach ($fetch_list2 as $fetch2) {
                $total_price = $fetch2['price'] * $fetch2['quantity'];
                

                $report .= '<tr>
                    <td ></td><td ></td><td ></td><td ></td>
                    <td>' . $fetch2['product_name'] . '</td>
                    <td ></td> <td ></td>
                    <td style="text-align: right;">' . $fetch2['price'] . '</td>
                    <td style="text-align: center;">' . $fetch2['quantity'] . '</td>
                    <td style="text-align: right;">' . number_format($total_price, 2) . '</td>
                    <td class="no-export"></td>
                </tr>';

            $total_qty += $fetch2['quantity'];


            }

            // Add the total summary for the invoice
            $report .= '<tr style="font-weight: bold;">
                <td ></td><td ></td><td ></td><td ></td><td ></td><td ></td><td ></td><td >Total</td>
                <td style="text-align: center;">' . $total_qty . '</td>
                <td style="text-align: right;">
                    Sub Total: ' . number_format($invoice['total_amount'], 2) . '<br>
                    Discount : ' . number_format($invoice['DiscountAmount'], 2) . '<br>
                    Invoice Price: ' . number_format($invoice['total_amount']-$invoice['DiscountAmount'], 2) . '<br>
                </td>
                <td class="no-export"></td>
            </tr>';

            // Append report to content
            $content .= $report;


$total_grand_qty += $total_qty;
$total_grand_amount += $invoice['total_amount'];
$total_grand_paid +=$invoice['DiscountAmount'];
$total_grand_due += $invoice['total_amount']-$invoice['DiscountAmount'];

        }

        $content .= "</tbody>";

        $content .= "
<tfoot>
    <tr style='font-weight:bold; background:#f0f0f0;'>
        <td colspan='8' style='text-align:right;'>Grand Totals</td>
        <td style='text-align:center;'>" . $total_grand_qty . "</td>
        <td style='text-align:right;'>
            " . number_format($total_grand_amount, 2) . "<br>
            " . number_format($total_grand_paid, 2) . "<br>
            " . number_format($total_grand_due, 2) . "
        </td>
        <td class='no-export'></td>
    </tr>
</tfoot>

</table></div></div>";
    } else {
        $content .= "<p>No records found for the selected date range.</p>";
    }

       $content .= $this->LoadExportScript($ReportName,'false') ; 


    return $content;
}


}
