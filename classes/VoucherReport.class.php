<?php 
class VoucherReport extends Dbh {



   protected function LedgerWise($relatedid, $date1, $date2) {
    $conn = $this->connect(); // Get DB connection






    $content = '<div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Voucher wise report :: from '.date('d-m-Y', strtotime($date1)).' to ' . date('d-m-Y', strtotime($date2)) . '</h3>
            </div><div class="card-body"><div class="table-responsive">';


$date1 = DateTime::createFromFormat('d/m/Y', $date1)->format('Y-m-d');
$date2 = DateTime::createFromFormat('d/m/Y', $date2)->format('Y-m-d');



    $query = "
        SELECT
    at.id,    
    at.description,        
    C.account_name,         
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
JOIN setup_account C ON (at.account_id = C.id)    
WHERE 
    at.entity_type = 'accounts'
    AND at.ledger_id = :relatedid
    AND at.transaction_date BETWEEN :date1 AND :date2
ORDER BY 
    at.transaction_date ASC;
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":relatedid", $relatedid, PDO::PARAM_STR);
    $stmt->bindValue(":date1", $date1, PDO::PARAM_STR);
    $stmt->bindValue(":date2", $date2, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 

    $total_due = 0; // Initialize total due variable

    if (count($result) > 0) {
        $content .= "<table class='table table-bordered' id='example1'>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Account</th>
                        <th>Details</th>
                        <th>Notes</th>
                        <th> Debit</th>
                        <th> Credit</th>
                                                <th> Action</th>

                    </tr>
                </thead>
                <tbody>";
$content .= "<input type='text' style='display:none' id='PageName' name='PageName' value='voucher_report.php'>";

        foreach ($result as $row) {
           

            $content .= "<tr id='row-{$row['id']}'>
                    <td>{$row['transaction_date']}</td>
                    <td>{$row['account_name']}</td>
                    <td>{$row['Details']}</td>
                    <td>{$row['description']}</td>
                    <td>" . number_format($row['total_debit'],  2, '.', '') . "</td>
                    <td>" . number_format($row['total_credit'],  2, '.', '') . "</td>";



                $content .= '<td><div class="btn-group">
                <a href="voucher_wise.php?id=' . $row['id'] . ' " target="_blank" class="btn btn-info">
                <i class="fas fa-pencil-alt"></i>
                </a>
                 <button type="button" class="btn btn-danger delete-btn" data-item_id="' . $row['id'] . ' ">
                    <i class="far fa-trash-alt"></i>
                    </button>

                </div></td>';




                 $content .= " </tr>";
        }

        // Add tfoot section for total due
        $content .= "</tbody>
               
            </table></div></div>";
    } else {
        $content .= "<p>No records found for the selected date range.</p>";
    }

    return $content;
}



 protected function AccountWise($relatedid, $date1, $date2) {
    $conn = $this->connect(); // Get DB connection






    $content = '<div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Voucher wise report :: from '.date('d-m-Y', strtotime($date1)).' to ' . date('d-m-Y', strtotime($date2)) . '</h3>
            </div><div class="card-body"><div class="table-responsive">';


$date1 = DateTime::createFromFormat('d/m/Y', $date1)->format('Y-m-d');
$date2 = DateTime::createFromFormat('d/m/Y', $date2)->format('Y-m-d');



    $query = "
        SELECT
    at.id,    
    at.description,        
    C.ledger_name,         
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
JOIN setup_ledger C ON (at.ledger_id = C.id)    
WHERE 
    at.entity_type = 'accounts'
    AND at.account_id = :relatedid
    AND at.transaction_date BETWEEN :date1 AND :date2
ORDER BY 
    at.transaction_date ASC;
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":relatedid", $relatedid, PDO::PARAM_STR);
    $stmt->bindValue(":date1", $date1, PDO::PARAM_STR);
    $stmt->bindValue(":date2", $date2, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 

    $total_due = 0; // Initialize total due variable

    if (count($result) > 0) {
        $content .= "<table class='table table-bordered' id='example1'>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Ledger</th>
                        <th>Details</th>
                        <th>Notes</th>
                        <th> Debit</th>
                        <th> Credit</th>
                                                                        <th> Action</th>

                    </tr>
                </thead>
                <tbody>";
$content .= "<input type='hidden' id='PageName' value='voucher_report.php'>";

        foreach ($result as $row) {
           

            $content .= "<tr id='row-{$row['id']}'>
                    <td>{$row['transaction_date']}</td>
                    <td>{$row['ledger_name']}</td>
                    <td>{$row['Details']}</td>
                    <td>{$row['description']}</td>
                    <td>" . number_format($row['total_debit'],  2, '.', '') . "</td>
                    <td>" . number_format($row['total_credit'],  2, '.', '') . "</td>
                    <td><a href='voucher_wise.php?id={$row['id']}'>Edit</a>|| 
                     <input type='button' class='btn btn-danger delete-btn' data-item_id='{$row['id']}' value='X'>
</td>
                  </tr>";

                
        }

        // Add tfoot section for total due
        $content .= "</tbody>
               
            </table></div></div>";
    } else {
        $content .= "<p>No records found for the selected date range.</p>";
    }

    return $content;
}




}
