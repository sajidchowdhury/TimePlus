<?php 
class SupplierDueReport extends Dbh {



   protected function DueSummery($date) {
    $conn = $this->connect(); // Get DB connection

    $content = '<div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Supplier Due Summary :: Till '.date('d-m-Y', strtotime($date)).'</h3>
            </div><div class="card-body"><div class="table-responsive">';

    $query = "
        SELECT         
            at.account_id AS supplier_id,
            c.supplier_name, 
            c.supplier_phone,
            SUM(CASE WHEN at.transaction_type = 'debit' THEN at.amount ELSE 0 END) AS total_debit,
            SUM(CASE WHEN at.transaction_type = 'credit' THEN at.amount ELSE 0 END) AS total_credit
        FROM 
            account_transaction at
        JOIN 
            setup_suppliers c ON at.account_id = c.id
        WHERE 
            at.entity_type = 'supplier'
            AND at.transaction_date <= :date  
        GROUP BY 
            at.account_id, c.supplier_name, c.supplier_phone
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":date", $date, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 

    $total_due = 0; // Initialize total due variable

    if (count($result) > 0) {
        $content .= "<table class='table table-bordered' id='example1'>
                <thead>
                    <tr>
                        <th>Suppplier Name</th>
                        <th>Phone</th>
                        <th>Total Debit</th>
                        <th>Total Credit</th>
                        <th>Due</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($result as $row) {
            $total_debit = (!empty($row['total_debit']) && is_numeric($row['total_debit'])) ? $row['total_debit'] : 0;
            $total_credit = (!empty($row['total_credit']) && is_numeric($row['total_credit'])) ? $row['total_credit'] : 0;
            $due_amount = $total_debit - $total_credit;

            $total_due += $due_amount; // Add to total due sum

            $content .= "<tr>
                    <td>{$row['supplier_name']}</td>
                    <td>{$row['supplier_phone']}</td>
                    <td>" . number_format($total_debit, 2, '.', '') . "</td>
                    <td>" . number_format($total_credit,  2, '.', '') . "</td>
                    <td>" . number_format($due_amount,  2, '.', '') . "</td>
                  </tr>";
        }

        // Add tfoot section for total due
        $content .= "</tbody>
                <tfoot>
                    <tr>
                        <th colspan='4'>Total</th>
                        <th>" . number_format($total_due,  2, '.', '') . "</th>
                    </tr>
                </tfoot>
            </table></div></div>";
    } else {
        $content .= "<p>No records found for the selected date range.</p>";
    }

    return $content;
}

protected function SupplierWiseDue($supplier_id, $date1, $date2) {
    $conn = $this->connect(); // Get DB connection



    // Get Closing Balance (One Day Before $date1)

    $content = '<div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Supplier Due Report :: '  .$date1 . ' to ' . $date2 . '</h3>
            </div><div class="card-body"><div class="table-responsive">';

$date1 = DateTime::createFromFormat('d/m/Y', $date1)->format('Y-m-d');
$date2 = DateTime::createFromFormat('d/m/Y', $date2)->format('Y-m-d');


        $closing_due = $this->getClosingDue($supplier_id, $date1);

    $query = "
        SELECT
    at.related_id,    
    at.id,
    at.description,
    at.ledger_id,              
    at.transaction_date,
    at.invoice_no,
    COALESCE((CASE WHEN at.transaction_type = 'debit' THEN at.amount ELSE 0 END), 0) AS total_debit,
    COALESCE((CASE WHEN at.transaction_type = 'credit' THEN at.amount ELSE 0 END), 0) AS total_credit,
    CASE 
        WHEN at.transaction_by = 'Bank' THEN CONCAT(b.bank_name, ' - ', b.account_no) 
        ELSE 'Cash' 
    END AS Details
FROM 
    account_transaction at
LEFT JOIN 
    setup_bank b ON at.transaction_by = 'Bank' AND at.transaction_by_id = b.id
WHERE 
    at.entity_type = 'supplier'
    AND at.account_id = :supplier_id
    AND at.transaction_date BETWEEN :date1 AND :date2
ORDER BY 
    at.transaction_date ASC;

    ";


    $stmt = $conn->prepare($query);
    $stmt->bindValue(":supplier_id", $supplier_id, PDO::PARAM_INT);
    $stmt->bindValue(":date1", $date1, PDO::PARAM_STR);
    $stmt->bindValue(":date2", $date2, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);



    $total_due = (float) $closing_due; // Convert to float for proper calculation

    $content .= "<table class='table table-bordered' id='example1'>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Details</th>
                        <th>Total Debit</th>
                        <th>Total Credit</th>
                        <th>Due</th>
                                                <th>Action</th>

                    </tr>
                </thead>
                <tbody>";

    // First row for opening balance
    $content .= "<tr>
                <td><strong>Opening Balance</strong></td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td><strong>" . number_format($closing_due,2, '.', '') ."</strong></td>
                      <td>-</td>
              </tr>";
$content .= "<input type='text' style='display:none' id='PageName' name='PageName' value='supplierDue_report.php'>";

    foreach ($result as $row) {
        $total_debit = (float) $row['total_debit'];
        $total_credit = (float) $row['total_credit'];
        $due_amount = $total_debit - $total_credit;

        $total_due += $due_amount; // Running total

        // Convert transaction_date to d-m-Y format
        $formatted_date = (!empty($row['transaction_date'])) ? date("d-m-Y", strtotime($row['transaction_date'])) : 'N/A';

        $content .= "<tr id='row-{$row['id']}'>
                    <td>{$formatted_date}</td>
                    <td>{$row['Details']}</td>
                    <td>" . number_format( $total_debit  , 2, '.', '') . "</td>
                    <td>" . number_format($total_credit, 2, '.', '') . "</td>
                    <td>" . number_format($total_due , 2, '.', '') . "</td>";



if( $row['ledger_id'] == 4){

                    $content .= '<td><div class="btn-group">
                    <a href="purchase.php?id=' . $row['related_id'] . ' " target="_blank" class="btn btn-info">
                    <i class="fas fa-pencil-alt"></i>
                    </a>
                    </div></td>';

}else{

       $content .= '<td><div class="btn-group">
                    <a href="supplier_payment.php?id=' . $row['id'] . ' " target="_blank" class="btn btn-info">
                    <i class="fas fa-pencil-alt"></i>
                    </a>

                    <button type="button" class="btn btn-danger delete-btn" data-item_id="' . $row['id'] . ' ">
                    <i class="far fa-trash-alt"></i>
                    </button>
                    </div></td>';


}



                $content .= "  </tr>";
    }

    // Add tfoot section for total due
    $content .= "</tbody>
                <tfoot>
                    <tr>
                        <th colspan='4'>Total</th>
                        <th>" . number_format( $total_due, 2, '.', ''). "</th>
                              <td>-</td>
                    </tr>
                </tfoot>
            </table></div></div>";

    return $content;
}
// Function to get closing due (one day before $date1)
protected function getClosingDue($supplier_id, $date1) {
    $conn = $this->connect(); // Get DB connection

    // Ensure date format is correct
    $date1 = date("Y-m-d", strtotime($date1));

    $query = "
        SELECT 
            COALESCE(SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END), 0) AS total_debit,
            COALESCE(SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END), 0) AS total_credit
        FROM account_transaction
        WHERE entity_type = 'supplier' 
            AND account_id = :supplier_id 
            AND transaction_date < :date1
            group by account_id
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":supplier_id", $supplier_id, PDO::PARAM_INT);
    $stmt->bindValue(":date1", $date1, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no result found, set default values
    $total_debit = 0;
    $total_credit = 0;

    if ($result) {
        $total_debit = (float) $result['total_debit'];
        $total_credit = (float) $result['total_credit'];
    }

    $closing_due = $total_debit - $total_credit;
    return number_format( $closing_due, 2, '.', '');


}



}
