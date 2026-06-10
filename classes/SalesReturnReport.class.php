<?php 
class SalesReturnReport extends Dbh {

        use SharedFunctionalityTrait;



    protected function SalesSummary($date1, $date2) {
    $conn = $this->connect(); // Get DB connection



    $ReportName = "Sales Return Report Summery:: From $date1 $date2 " ; 
    $content = '<div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">'.$ReportName.'</h3>
            </div><div class="card-body"><div class="table-responsive">';

    $date1 = DateTime::createFromFormat('d/m/Y', $date1)->format('Y-m-d');
    $date2 = DateTime::createFromFormat('d/m/Y', $date2)->format('Y-m-d');

    $query = "
        SELECT pi.id, pi.total_amount, C.customer_name, pi.invoice_no, pi.customer_id, 
        DATE_FORMAT(pi.invoice_date, '%d/%m/%Y') AS invoice_date
        FROM sales_return pi
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
        $content .= "<input type='hidden' id='delete_type' value='Invoice' > <table class='table table-bordered' id='example'>
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Return Amount</th>
                        <th class='no-export'>Action</th>
                    </tr>
                </thead>
                <tbody>";

        $total_amount_sum = 0;

        foreach ($result as $row) {
            // Calculations
            $total_amount = $row['total_amount'];

            $content .= "<tr id=row-{$row['id']}>
                        <td>{$row['invoice_no']}</td>
                        <td>{$row['customer_name']}</td>
                        <td>{$row['invoice_date']}</td>
                        <td>{$total_amount}</td>
                        <td class='no-export'>
                            <div class='btn-group'>
                             <a href='sales_return_invoice.php?invoice={$row['id']}' target='_blank' class='btn btn-warning'>
                                    <i class='far fa-file-alt'></i>
                                </a>
                                
                                  <button type='button' class='btn btn-danger delete-btn' onclick='DeleteInvoice(".$row['id'].")'>
                                    <i class='far fa-trash-alt'></i>
                                </button>
                            </div>
                        </td>
                    </tr>";

            $total_amount_sum += $total_amount;
        }

        $content .= "</tbody>
            <tfoot>
                <tr>
                    <td colspan='3'><strong>Total</strong></td>
                    <td><strong>{$total_amount_sum}</strong></td> <td></td>
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

    $conn = $this->connect(); // Get DB connection


    $ReportName = "Invoice Wise Sales Return Report:: From $date1 to $date2 " ; 



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
    C.customer_name AS customer_name

FROM sales_return pi
JOIN setup_customer C ON pi.customer_id = C.id
WHERE pi.invoice_date BETWEEN :date1 AND :date2
ORDER BY pi.invoice_date DESC

    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":date1", $date1, PDO::PARAM_STR);
    $stmt->bindValue(":date2", $date2, PDO::PARAM_STR);
    $stmt->execute();
    $invoice_info = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all invoice data

    if (count($invoice_info) > 0) {
        $content .= "<table class='table table-bordered' id='example'><input type='hidden' id='delete_type' value='Invoice_Item' > 
                <thead>
                    <tr>
                        <th>Invoice No.</th> 
                        <th>Date</th> 
                        <th>Customer Name</th> 
                        <th>Product Name</th> 
                        <th>Quantity</th> 
                                                <th>Price</th> 
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
                    pii.product_id, pii.id,
                    CONCAT(p.product_description,' ' , p.pack_size) AS product_name, 

                    pii.quantity, 
                    pii.price
                FROM sales_return_items pii
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
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="no-export"></td>
            </tr>';

            // Loop through each product in the invoice
            foreach ($fetch_list2 as $fetch2) {
                $total_price = $fetch2['price'] * $fetch2['quantity'];
                

                $report .= '<tr>
                    <td ></td><td ></td><td ></td>
                    <td>' . $fetch2['product_name'] . '</td>
                    
                    <td style="text-align: center;">' . $fetch2['quantity'] . '</td>                    
                    <td style="text-align: right;">' . $fetch2['price'] . '</td>

                    <td style="text-align: right;">' . number_format($total_price, 2) . '</td>
                    <td class="no-export">';

$report .=  "<div class='btn-group'>
                             
                                <button type='button' class='btn btn-danger delete-btn' onclick='DeleteInvoice(".$fetch2['id'].")'>
                                    <i class='far fa-trash-alt'></i>
                                </button>
                            </div>";


                   $report .= ' </td>
                </tr>';

            $total_qty += $fetch2['quantity'];


            }

            // Add the total summary for the invoice
            $report .= '<tr style="font-weight: bold;">
                <td ></td><td ></td><td ></td><td style="text-align:right;">Total</td>
                <td style="text-align: center;">' . $total_qty . '</td><td ></td>
                <td style="text-align: right;">' . number_format($invoice['total_amount'], 2) . '<br>
                </td>
                <td class="no-export"></td>
            </tr>';

            // Append report to content
            $content .= $report;


$total_grand_qty += $total_qty;
$total_grand_amount += $invoice['total_amount'];

        }

        $content .= "</tbody>";

        $content .= "
<tfoot>
    <tr style='font-weight:bold; background:#f0f0f0;'>
        <td colspan='4' style='text-align:right;'>Grand Totals</td>
        <td style='text-align:center;'>" . $total_grand_qty . "</td><td ></td>
        <td style='text-align:right;'>
            " . number_format($total_grand_amount, 2) . "<br>
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


protected function CustomerWise($customer_id,$date1, $date2) {



    $conn = $this->connect(); // Get DB connection


    $ReportName = "Customer Wise Sales Return Report:: From $date1 to $date2 " ; 



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
    C.customer_name AS customer_name

FROM sales_return pi
JOIN setup_customer C ON pi.customer_id = C.id
WHERE (pi.invoice_date BETWEEN :date1 AND :date2) AND pi.customer_id = :customer_id
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
                        <th>Product Name</th> 
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
                    pii.product_id, pii.id,
                    CONCAT(p.product_description,' ' , p.pack_size) AS product_name, 

                    pii.quantity, 
                    pii.price
                FROM sales_return_items pii
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
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="no-export"><a href="sales_invoice_copy.php?purches_type=fg_local_purches&code=' . $invoice['invoice_no'] . '" target="_BLINK"><span class="fa fa-file-text"></span></a></td>
            </tr>';

            // Loop through each product in the invoice
            foreach ($fetch_list2 as $fetch2) {
                $total_price = $fetch2['price'] * $fetch2['quantity'];
                

                $report .= '<tr>
                    <td ></td><td ></td><td ></td>
                    <td>' . $fetch2['product_name'] . '</td>
                    
                    <td style="text-align: right;">' . $fetch2['price'] . '</td>
                    <td style="text-align: center;">' . $fetch2['quantity'] . '</td>
                    <td style="text-align: right;">' . number_format($total_price, 2) . '</td>
                    <td class="no-export">';

$report .=  "<div class='btn-group'>
                              
                                <button type='button' class='btn btn-danger delete-btn' data-item_id='{$fetch2['id']}'>
                                    <i class='far fa-trash-alt'></i>
                                </button>
                            </div>";


                   $report .= ' </td>
                </tr>';

            $total_qty += $fetch2['quantity'];


            }

            // Add the total summary for the invoice
            $report .= '<tr style="font-weight: bold;">
                <td ></td><td ></td><td ></td><td ></td><td >Total</td>
                <td style="text-align: center;">' . $total_qty . '</td>
                <td style="text-align: right;">' . number_format($invoice['total_amount'], 2) . '<br>
                </td>
                <td class="no-export"></td>
            </tr>';

            // Append report to content
            $content .= $report;


$total_grand_qty += $total_qty;
$total_grand_amount += $invoice['total_amount'];

        }

        $content .= "</tbody>";

        $content .= "
<tfoot>
    <tr style='font-weight:bold; background:#f0f0f0;'>
        <td colspan='4' style='text-align:right;'>Grand Totals</td>
        <td style='text-align:center;'>" . $total_grand_qty . "</td>
        <td style='text-align:right;'>
            " . number_format($total_grand_amount, 2) . "<br>
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
