<?php 
class PurchaseReport extends Dbh {

     protected function PurchaseSummary($date1, $date2) {




        $conn = $this->connect(); // Get DB connection

    $content = '<div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Purchase Report :: From '.$date1. ' To '.$date2.'</h3>
            </div><div class="card-body"><div class="table-responsive">';



        $date1 = DateTime::createFromFormat('d/m/Y', $date1)->format('Y-m-d');
$date2 = DateTime::createFromFormat('d/m/Y', $date2)->format('Y-m-d');





        $query = "
            SELECT pi.id,C.supplier_name,pi.invoice_no, pi.supplier_id, DATE_FORMAT(pi.invoice_date, '%d/%m/%Y') AS invoice_date, 
                   SUM(pii.quantity * pii.price) AS total_amount,
                   COUNT(DISTINCT pii.product_id) AS total_items
            FROM purchase_invoices pi
            JOIN purchase_invoices_items pii ON pi.id = pii.invoice_id
            JOIN setup_suppliers C ON pi.supplier_id = C.id

            WHERE pi.invoice_date BETWEEN :date1 AND :date2
            GROUP BY pi.invoice_no, pi.supplier_id, pi.invoice_date
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
                            <th>Supplier</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Total Items</th>
                           <th class='no-export'>Action</th>

                        </tr>
                    </thead>
                    <tbody>";

            foreach ($result as $row) {
                $formatted_amount = number_format($row['total_amount'], 2); // Format amount
                $content .= "<tr id=row-{$row['id']}>  

                <td>{$row['invoice_no']}</td>
                <td>{$row['supplier_name']}</td>
                <td>{$row['invoice_date']}</td>
                <td>{$formatted_amount}</td>
                <td>{$row['total_items']}</td>" ; 

                  $content .= '<td class="no-export"><div class="btn-group">
    <a href="purchase.php?id=' . $row['id'] . ' " target="_blank" class="btn btn-info">
        <i class="fas fa-pencil-alt"></i>
    </a>


   
</div></td>';

// <button type="button" class="btn btn-danger delete-btn" data-item_id="' . $row['id'] . ' "><i class="far fa-trash-alt"></i></button>
            
             $content .= "</tr>";

            }
        $content .= "</tbody></table></div></div>";
        } else {
            $content .= "<p>No records found for the selected date range.</p>";
        }

        return $content;
    }


     protected function InvoiceWise($date1, $date2) {

    $conn = $this->connect(); // Get DB connection

    $content = '<div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Purchase Report :: From '.$date1. ' To '.$date2.'</h3>
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
            C.supplier_name AS supplier_name,
            E.employee_name AS emp_name
            
        FROM purchase_invoices pi
        JOIN setup_suppliers C ON pi.supplier_id = C.id
        JOIN admin E ON pi.poster = E.id
        WHERE pi.invoice_date BETWEEN :date1 AND :date2
        ORDER BY pi.invoice_date DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":date1", $date1, PDO::PARAM_STR);
    $stmt->bindValue(":date2", $date2, PDO::PARAM_STR);
    $stmt->execute();
    $invoice_info = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all invoice data

    if (count($invoice_info) > 0) {
        $content .= "<table class='table table-bordered' id='example'>
                <thead>
                    <tr>
                        <th>Invoice No.</th> 
                        <th>Date</th> 
                        <th>Supplier Name</th> 
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

        // Loop through each invoice
        foreach ($invoice_info as $invoice) {
            $report = '';

            // Get product details for the current invoice
            $query_items = "
               SELECT 
                    pii.product_id, 
                    p.product_description AS product_name, 
                    pii.quantity, 
                    pii.price
                FROM purchase_invoices_items pii
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
                <td>' . $invoice['supplier_name'] . '</td>
                <td>' . $invoice['emp_name'] . '</td>
                <td></td>
                <td>0</td>
                <td>0</td>
                <td></td>
                <td></td>
                <td></td>
                <td><a href="purchase_invoice_copy.php?purches_type=fg_local_purches&code=' . $invoice['invoice_no'] . '" target="_BLINK"><span class="fa fa-file-text"></span></a></td>
            </tr>';

            // Loop through each product in the invoice
            foreach ($fetch_list2 as $fetch2) {
                $total_price = $fetch2['price'] * $fetch2['quantity'];
                $total_qty += $fetch2['quantity'];

                $report .= '<tr>
                    <td colspan="4"></td>
                    <td>' . $fetch2['product_name'] . '</td>
                    <td colspan="2"></td>
                    <td style="text-align: right;">' . $fetch2['price'] . '</td>
                    <td style="text-align: center;">' . $fetch2['quantity'] . '</td>
                    <td style="text-align: right;">' . number_format($total_price, 2) . '</td>
                    <td></td>
                </tr>';
            }

            // Add the total summary for the invoice
            $report .= '<tr style="font-weight: bold;">
                <td colspan="8"></td>
                <td style="text-align: center;">Total Quantity<br>' . $total_qty . '</td>
                <td style="text-align: right;">
                    Total: ' . number_format($invoice['total_amount'], 2) . '<br>
                    Total Paid: ' . number_format(0, 2) . '<br>
                    Total Due: ' . number_format(0, 2) . '
                </td>
                <td class="no-export"></td>
            </tr>';

            // Append report to content
            $content .= $report;
        }

        $content .= "</tbody></table></div></div>";
    } else {
        $content .= "<p>No records found for the selected date range.</p>";
    }

    return $content;
}


}
