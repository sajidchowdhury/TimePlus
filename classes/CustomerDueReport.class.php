<?php 
class CustomerDueReport extends Dbh {

        use SharedFunctionalityTrait;


   protected function DueSummery($date) {
    $conn = $this->connect(); // Get DB connection


    $ReportName = "Customer Due Report :: till " . date('d-m-Y', strtotime($date))  ; 



    $content = '<div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">'.$ReportName.'</h3>
        </div>
        <div class="card-body">
        <div class="table-responsive">';

    $query = "
        SELECT
            at.account_id AS customer_id,
            c.customer_name,
            c.customer_phone,
            SUM(CASE WHEN at.transaction_type = 'debit' AND at.description <> 'Discount' THEN at.amount ELSE 0 END) AS total_debit,
            SUM(CASE WHEN at.transaction_type = 'credit' THEN at.amount ELSE 0 END) AS total_credit,
            SUM(CASE WHEN at.transaction_type = 'debit' AND at.description = 'Discount' THEN at.amount ELSE 0 END) AS total_discount
        FROM account_transaction at
        JOIN setup_customer c ON at.account_id = c.id
        WHERE 
            at.entity_type = 'customer'
            AND at.transaction_date <= :date
        GROUP BY 
            at.account_id, c.customer_name, c.customer_phone
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":date", $date, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 

    $total_due = 0;

    if (count($result) > 0) {
        $content .= "<table class='table table-bordered' id='example'>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Total Debit</th>
                    <th>Total Credit</th>
                    <th>Total Discount</th>
                    <th>Due</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($result as $row) {
            $debit = (float) $row['total_debit'];
            $credit = (float) $row['total_credit'];
            $discount = (float) $row['total_discount'];
            $due = $debit - $credit - $discount;

            $total_due += $due;

            $content .= "<tr>
                <td>{$row['customer_name']}</td>
                <td>{$row['customer_phone']}</td>
                <td>" . number_format($debit, 2) . "</td>
                <td>" . number_format($credit, 2) . "</td>
                <td>" . number_format($discount, 2) . "</td>
                <td>" . number_format($due, 2) . "</td>
            </tr>";
        }

        $content .= "</tbody>
            <tfoot>
                <tr>
                    <th >Total Due:</th><th ></th><th ></th><th ></th><th ></th>
                    <th>" . number_format($total_due, 2) . "</th>
                </tr>
            </tfoot>
        </table></div></div>";
    } else {
        $content .= "<p>No records found for the selected date range.</p>";
    }

    $content .= $this->LoadExportScript($ReportName) ; 
   
    return $content;
}



protected function DateWiseCollection($date1,$date2) {
    $conn = $this->connect(); // Get DB connection


$date1 = DateTime::createFromFormat('d/m/Y', $date1)->format('Y-m-d');
$date2 = DateTime::createFromFormat('d/m/Y', $date2)->format('Y-m-d');




    $ReportName = "Collection Report :: From " . date('d-m-Y', strtotime($date1)) . " to " .  date('d-m-Y', strtotime($date2)); 



    $content = '<div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">'.$ReportName.'</h3>
        </div>
        <div class="card-body">
        <div class="table-responsive">';


    $query = "
        SELECT
            IF(at.transaction_by = 'Bank' , B.bank_name, 'Cash') as Details,
            at.description,at.amount,
            c.customer_name,
            c.customer_phone
        FROM account_transaction at
        JOIN setup_customer c ON at.account_id = c.id
        LEFT JOIN setup_bank B ON at.transaction_by_id = B.id

        WHERE 
            at.description NOT IN  ('Product Return') AND
            at.entity_type = 'customer' AND at.transaction_type = 'credit'  AND at.amount > 0 
            AND at.transaction_date BETWEEN  :date1 AND  :date2
       
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":date1", $date1, PDO::PARAM_STR);    $stmt->bindValue(":date2", $date2, PDO::PARAM_STR);

    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 

    $total_debit = 0; $total_credit = 0;

    if (count($result) > 0) {
        $content .= "<table class='table table-bordered' id='example'>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Transaction By</th>
                                        <th>Description</th>

                    <th>Credit</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($result as $row) {
            $credit = (float) $row['amount'];

       $total_credit += $credit;
            $content .= "<tr>
                <td>{$row['customer_name']}<br>Phone: {$row['customer_phone']}</td>
                <td>{$row['Details']}</td>                
                <td>{$row['description']}</td>

                <td>" . number_format($credit, 2) . "</td>
            </tr>";
        }

        $content .= "</tbody>
            <tfoot>
                <tr>
                    <th ></th><th >Total Due:</th><th ></th>
                    <th>" . number_format($total_credit, 2) . "</th>
                </tr>
            </tfoot>
        </table></div></div>";
    } else {
        $content .= "<p>No records found for the selected date range.</p>";
    }

    $content .= $this->LoadExportScript($ReportName) ; 
   
    return $content;
}


protected function CustomerWiseDue($customer_id, $date1, $date2) {
    $conn = $this->connect(); // DB connection

    $List = new AddCustomer();
    $data = $List->SingleData($customer_id);

    $ReportName = $data['customer_name'] . " Due Report :: From $date1 to $date2 ";

    // Card wrapper
    $content = '<div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">'.$ReportName.'</h3>
            </div><div class="card-body"><div class="table-responsive">';

    // Convert to Y-m-d
    $date1 = DateTime::createFromFormat('d/m/Y', $date1)->format('Y-m-d');
    $date2 = DateTime::createFromFormat('d/m/Y', $date2)->format('Y-m-d');

    // Get opening/closing balance before date1
    $closing_due = $this->getClosingDue($customer_id, $date1);

    // Query
    $query = "
        SELECT 
            att.time,
            att.related_id,    
            att.id,
            att.description,
            att.ledger_id,            
            att.transaction_date,
            CASE 
                WHEN att.description <> 'Discount' THEN CONCAT('Invoice No: ',si.invoice_no)
                ELSE ''
            END AS InvoiceNo,
            COALESCE((CASE WHEN att.transaction_type = 'debit'  AND description <> 'Discount' THEN att.amount ELSE 0 END), 0) AS total_debit,
            COALESCE((CASE WHEN att.transaction_type = 'credit' THEN att.amount ELSE 0 END), 0) AS total_credit,
            COALESCE((CASE WHEN att.transaction_type = 'debit' AND description = 'Discount' THEN att.amount ELSE 0 END), 0) AS total_discount,
            CASE 
                WHEN att.transaction_by = 'Bank' THEN CONCAT(b.bank_name, ' - ', b.account_no) 
                ELSE 'Cash' 
            END AS Details
        FROM account_transaction att
        LEFT JOIN setup_bank b 
            ON att.transaction_by = 'Bank' AND att.transaction_by_id = b.id
        LEFT JOIN sales_invoices si 
            ON att.related_id = si.id
        WHERE 
            att.entity_type = 'customer'
            AND att.account_id = :customer_id
            AND att.transaction_date BETWEEN :date1 AND :date2
        ORDER BY att.transaction_date, att.time ASC;
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":customer_id", $customer_id, PDO::PARAM_INT);
    $stmt->bindValue(":date1", $date1, PDO::PARAM_STR);
    $stmt->bindValue(":date2", $date2, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // trackers
    $previous_due = (float) $closing_due;
    $total_due    = (float) $closing_due;

    // Table start
    $content .= "<table class='table table-bordered' id='example'>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Details</th>
                    <th>Total Debit</th>
                    <th>Total Credit</th>
                    <th>Discount</th>
                    <th>Due</th>
                    <th class='no-export'>Action</th>
                </tr>
            </thead>
            <tbody>";

    // Opening Balance row
    $content .= "<tr>
            <td><strong>Opening Balance</strong></td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td><strong>" . number_format($closing_due,2,'.','') . "</strong></td>
            <td>-</td>
          </tr>";

    $content .= "<input type='hidden' id='PageName' name='PageName' value='customerDue_report.php'>";

    foreach ($result as $row) {
        $total_debit    = (float) $row['total_debit'];
        $total_credit   = (float) $row['total_credit'];
        $total_discount = (float) $row['total_discount'];

        $text = ($row['description'] == 'Product Return') ? 'Return ' : '';

        // calculate change in due
        $due_amount = $total_debit - $total_credit - $total_discount;
        $recent_due = $previous_due + $due_amount;

        // encode both dues
        $encoded = base64_encode(json_encode([
            "previous_due" => $previous_due,
            "recent_due"   => $recent_due
        ]));

        // formatted date
        $formatted_date = (!empty($row['transaction_date'])) 
            ? date("d-m-Y", strtotime($row['transaction_date'])) 
            : 'N/A';

        // build row
        $content .= "<tr id='row-{$row['id']}'>
                        <td>{$formatted_date}</td>
                        <td>{$row['description']} :: {$row['Details']} <br>{$text} {$row['InvoiceNo']}</td>
                        <td>" . number_format($total_debit, 2, '.', '') . "</td>
                        <td>" . number_format($total_credit, 2, '.', '') . "</td>
                        <td>" . number_format($total_discount, 2, '.', '') . "</td>
                        <td>" . number_format($recent_due, 2, '.', '') . "</td>
                        <td class='no-export'>
                            <input type='hidden' name='due_data[{$row['id']}]' value='{$encoded}'>";

        // action buttons
        if ($row['ledger_id'] == 3) { // Client Purchase
            $content .= '<div class="btn-group">
                <a href="sales.php?id='.$row['related_id'].'" target="_blank" class="btn btn-info">
                    <i class="fas fa-pencil-alt"></i>
                </a>
                <a href="invoice.php?invoice='.$row['related_id'].'" target="_blank" class="btn btn-warning">
                    <i class="far fa-file-alt"></i>
                </a>
            </div>';
        } elseif ($row['ledger_id'] == 6) { // Sales Return
            $content .= '<div class="btn-group">
                <a href="sales_return_invoice.php?invoice='.$row['related_id'].'" target="_blank" class="btn btn-warning">
                    <i class="far fa-file-alt"></i>
                </a>
            </div>';
        } else {
            $content .= '<div class="btn-group">
                <a href="customer_receive.php?id='.$row['id'].'" target="_blank" class="btn btn-info">
                    <i class="fas fa-pencil-alt"></i>
                </a>
                <button type="button" class="btn btn-danger delete-btn" data-item_id="'.$row['id'].'">
                    <i class="far fa-trash-alt"></i>
                </button>
                <a href="customer_receive_invoice.php?code='.$encoded.'&invoice='.$row['id'].'" target="_blank" class="btn btn-warning">
                    <i class="far fa-file-alt"></i>
                </a>
            </div>';
        }

        $content .= "</td></tr>";

        // update trackers
        $previous_due = $recent_due;
        $total_due    = $recent_due;
    }

    // footer
    $content .= "</tbody>
            <tfoot>
                <tr>
                    <th></th><th></th><th></th><th></th>
                    <th>Total</th>
                    <th>" . number_format($total_due, 2, '.', '') . "</th>
                    <td class='no-export'>-</td>
                </tr>
            </tfoot>
        </table></div></div>";

    $content .= $this->LoadExportScript($ReportName,'false');

    return $content;
}


public function CustomerDueTillDate($customer_id, $date, $time) {

   $datetime = $date . ' ' . $time; 

    $conn = $this->connect(); // DB connection

    $query = "
        SELECT
            SUM(CASE WHEN att.transaction_type = 'debit' AND att.description <> 'Discount' THEN att.amount ELSE 0 END) AS total_debit,
            SUM(CASE WHEN att.transaction_type = 'credit' THEN att.amount ELSE 0 END) AS total_credit,
            SUM(CASE WHEN att.transaction_type = 'debit' AND att.description = 'Discount' THEN att.amount ELSE 0 END) AS total_discount
        FROM account_transaction att
        WHERE 
            att.entity_type = 'customer'
            AND att.account_id = :customer_id

           AND CONCAT(att.transaction_date, ' ', att.time) <= :datetime
       
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":customer_id", $customer_id, PDO::PARAM_INT);
    $stmt->bindValue(":datetime", $datetime, PDO::PARAM_STR);  // Format: '2025-05-10 13:32:51'
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $debit = (float) $row['total_debit'];
    $credit = (float) $row['total_credit'];
    $discount = (float) $row['total_discount'];

    $due = $debit - $credit - $discount;

    return [
        'total_debit' => $debit,
        'total_credit' => $credit,
        'total_discount' => $discount,
        'total_due' => $due
    ];
}

public function CustomerDueTillThisInvoice($customer_id,$time) {

    $conn = $this->connect(); // DB connection

    $query = "
        SELECT
    SUM(CASE WHEN att.transaction_type = 'debit' AND att.description <> 'Discount' THEN att.amount ELSE 0 END) AS total_debit,
    SUM(CASE WHEN att.transaction_type = 'credit' THEN att.amount ELSE 0 END) AS total_credit,
    SUM(CASE WHEN att.transaction_type = 'debit' AND att.description = 'Discount' THEN att.amount ELSE 0 END) AS total_discount
FROM account_transaction att
WHERE 
    att.entity_type = 'customer'
    AND att.account_id = :customer_id
    AND att.time < :time

    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":customer_id", $customer_id, PDO::PARAM_INT);
    $stmt->bindValue(":time", $time, PDO::PARAM_STR);

    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $debit = (float) $row['total_debit'];
    $credit = (float) $row['total_credit'];
    $discount = (float) $row['total_discount'];

    $due = $debit - $credit - $discount;

    return [
        'total_debit' => $debit,
        'total_credit' => $credit,
        'total_discount' => $discount,
        'total_due' => $due
    ];
}



// Function to get closing due (one day before $date1)
public function getClosingDue($customer_id, $date1) {


       $conn = $this->connect(); // DB connection

    $query = "
        SELECT
            SUM(CASE WHEN att.transaction_type = 'debit' AND att.description <> 'Discount' THEN att.amount ELSE 0 END) AS total_debit,
            SUM(CASE WHEN att.transaction_type = 'credit' THEN att.amount ELSE 0 END) AS total_credit,
            SUM(CASE WHEN att.transaction_type = 'debit' AND att.description = 'Discount' THEN att.amount ELSE 0 END) AS total_discount
        FROM account_transaction att
        WHERE 
            att.entity_type = 'customer'
            AND att.account_id = :customer_id
            AND ( att.transaction_date < :date )
            
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":customer_id", $customer_id, PDO::PARAM_INT);
    $stmt->bindValue(":date", $date1, PDO::PARAM_STR);  // Format: 2025-05-05
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $debit = (float) $row['total_debit'];
    $credit = (float) $row['total_credit'];
    $discount = (float) $row['total_discount'];

    $due = $debit - $credit - $discount;

    return number_format( $due, 2, '.', '');


   
}



}
