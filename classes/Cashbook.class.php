<?php 
class Cashbook extends Dbh {





 protected function CashBookReport($date1 ) {
    $conn = $this->connect(); // Get DB connection

    // Convert dates to Y-m-d

    $getDue = new CustomerDueReport();

   // $date1 = DateTime::createFromFormat('d/m/Y', $date1)->format('Y-m-d');

  //  $date2 = DateTime::createFromFormat('d/m/Y', $date1)->format('Y-m-d');
$date2 = $date1 ; 
    $content = '<div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Cash Book Report :: ' . date('d-m-Y', strtotime($date1)) . ' to ' . date('d-m-Y', strtotime($date2)) . '</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">';

    // ==============================
    // Table 1: Only Donation Income
    // ==============================

                               
    $query1 = "
    SELECT 
        at.customer_id,at.time,
        at.invoice_date,at.total_amount,at.discount,
        CONCAT(C.customer_name) AS notes
    FROM 
        sales_invoices at
    JOIN 
        setup_customer C ON at.customer_id = C.id
    WHERE 
        at.invoice_date BETWEEN :date1 AND :date2
    ORDER BY 
        at.invoice_date ASC;
";


    $stmt1 = $conn->prepare($query1);
    $stmt1->bindValue(":date1", $date1);
    $stmt1->bindValue(":date2", $date2);
    $stmt1->execute();
    $invoices = $stmt1->fetchAll(PDO::FETCH_ASSOC); 

    $total_invoice = 0;

    $content .= "<div class='col-md-6'><div class='table-responsive'>
                    <table class='table table-bordered'>
                        <thead>
                            <tr>
                                <th colspan='3'>Invoice Created</th>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody> ";

    foreach ($invoices as $row) {

            
            $due = $getDue->CustomerDueTillDate($row['customer_id'], $row['invoice_date'], $row['time']) ; 




            $total_amount = $row['total_amount'];
            $discount = $row['discount'];
            $actual_discount = ($discount > 0) ? ($total_amount * ($discount / 100)) : 0.00;
            $invoicePrice = $total_amount - $actual_discount;
            $previousDue = $due['total_due'] - $invoicePrice ; 
            $color = ($previousDue>500000) ? 'red' : '' ; 

        $content .= "<tr>
                        <td style='color:{$color}'>{$row['invoice_date']}</td>
                        <td style='color:{$color}'>{$row['notes']} <br><b >Previous Due: {$previousDue}</b></td>
                        <td style='color:{$color}'>" . number_format($invoicePrice, 2) . "</td>
                    </tr>";

        $total_invoice += $invoicePrice;

    }

    $content .= "<tr style='font-weight: bold; background: #f2f2f2;'>
                    <td colspan='2' style='text-align: right;'>Total :</td>
                    <td>" . number_format($total_invoice, 2) . "</td>
                 </tr>";



 $query1 = "
    SELECT 
        at.transaction_date,at.amount,
        IF(at.transaction_by = 'Bank' , CONCAT( C.account_name, ' > ' , B.bank_name , ':', B.account_no ) ,  CONCAT( C.account_name, ' > Cash'  ))  AS notes
    FROM 
        account_transaction at
    JOIN 
        setup_account C ON at.account_id = C.id
    LEFT JOIN setup_bank B ON (at.transaction_by_id = B.id)    
    WHERE 
        at.entity_type = 'accounts' and (at.transaction_date BETWEEN :date1 AND :date2)
    ORDER BY 
        at.transaction_date ASC;
";


    $stmt1 = $conn->prepare($query1);
    $stmt1->bindValue(":date1", $date1);
    $stmt1->bindValue(":date2", $date2);
    $stmt1->execute();
    $expenses = $stmt1->fetchAll(PDO::FETCH_ASSOC); 

    $total_expense = 0;

    $content .= "<div class='col-md-6'><div class='table-responsive'>
                    <table class='table table-bordered'>
                        <thead>
                            <tr>
                                <th colspan='3'>Expense</th>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody> ";

    foreach ($expenses as $row) {
       
            $total_amount = $row['amount'];


        $content .= "<tr>
                        <td>{$row['transaction_date']}</td>
                        <td>{$row['notes']}</td>
                        <td>" . number_format($total_amount, 2) . "</td>
                    </tr>";

        $total_expense += $total_amount;

    }

    $content .= "<tr style='font-weight: bold; background: #f2f2f2;'>
                    <td colspan='2' style='text-align: right;'>Total :</td>
                    <td>" . number_format($total_expense, 2) . "</td>
                 </tr>";



    $content .= "</tbody></table></div></div>";

    // ==============================
    // Table 2: All Transactions
    // ==============================
    $query2 = "SELECT A.amount,A.transaction_date,A.description,
    IF(A.transaction_by = 'Bank' , CONCAT(A.description,  ' >> ' , B.bank_name , ':', B.account_no ) ,  CONCAT(A.description, ' >> ', 'Cash'  ))  AS Notes

 FROM `account_transaction` A 
LEFT JOIN setup_bank B ON (A.transaction_by_id = B.id)    

where A.entity_type = 'customer' and A.description NOT IN ('Discount','Customer Purchase') AND A.transaction_date BETWEEN :date1 AND :date2;
    ";

    $stmt2 = $conn->prepare($query2);
    $stmt2->bindValue(":date1", $date1);
    $stmt2->bindValue(":date2", $date2);
    $stmt2->execute();
    $collectios = $stmt2->fetchAll(PDO::FETCH_ASSOC); 

    $total_collection = 0;

    $content .= "<div class='col-md-6'><div class='table-responsive'>
                    <table class='table table-bordered'>
                        <thead>
                            <tr>
                                <th colspan='3'>Collection</th>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>";

    foreach ($collectios as $row) {
       


        $content .= "<tr>
                        <td>{$row['transaction_date']}</td>
                        <td>{$row['Notes']}</td>
                        <td>" . number_format($row['amount'], 2) . "</td>
                    </tr>";

        $total_collection += $row['amount'];


    }

    $content .= "<tr style='font-weight: bold; background: #f2f2f2;'>
                    <td colspan='2' style='text-align: right;'>Total Collection:</td>
                    <td>" . number_format($total_collection, 2) . "</td>
                 </tr>";

    $content .= "</tbody></table></div></div>";


            



    // Close all divs
    $content .= "</div></div></div></div></div>";

    return $content;
}


  protected function CashBookReport2($date1) {
    $conn = $this->connect();
    $content = '<div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Cash Book Report :: ' . date('d-m-Y', strtotime($date1)) . '</h3>
            </div><div class="card-body">
            <div class="row">';

    // Fetch all transactions for this date
    $query = "
        SELECT 
            at.transaction_date,
            at.description,
            at.transaction_type,
            IF(at.) at.amount,
            at.entity_type,
            at.transaction_by,
            at.transaction_by_id,
            COALESCE(c.customer_name, s.supplier_name, a.account_name) AS name,
            COALESCE(c.customer_phone, s.supplier_phone, '') AS phone,
            CASE 
                WHEN at.transaction_by = 'Bank' THEN CONCAT(b.bank_name, ' - ', b.account_no) 
                ELSE 'Cash' 
            END AS transaction_by_details
        FROM 
            account_transaction at
        LEFT JOIN setup_customer c ON at.entity_type = 'Customer' AND at.account_id = c.id
        LEFT JOIN setup_suppliers s ON at.entity_type = 'Supplier' AND at.account_id = s.id
        LEFT JOIN setup_account a ON at.entity_type = 'Accounts' AND at.account_id = a.id
        LEFT JOIN setup_bank b ON at.transaction_by = 'Bank' AND at.transaction_by_id = b.id
        WHERE at.transaction_date = :date1
        ORDER BY FIELD(at.entity_type, 'Customer', 'Supplier', 'Accounts'), at.transaction_by
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":date1", $date1, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 

    // Split transactions into debit and credit
    $debits = [];
    $credits = [];
    $totals = [
        'debit_cash' => 0, 'debit_bank' => 0,
        'credit_cash' => 0, 'credit_bank' => 0
    ];

    foreach ($result as $row) {
        $group = strtolower($row['transaction_type']) === 'debit' ? 'debits' : 'credits';
        ${$group}[] = $row;

        if (strtolower($row['transaction_type']) === 'debit') {
            if ($row['transaction_by'] === 'Bank') $totals['debit_bank'] += $row['amount'];
            else $totals['debit_cash'] += $row['amount'];
        } else {
            if ($row['transaction_by'] === 'Bank') $totals['credit_bank'] += $row['amount'];
            else $totals['credit_cash'] += $row['amount'];
        }
    }

    // Helper to generate table HTML
    $renderTable = function($title, $transactions) {
        $table = "<table class='table table-bordered table-sm'><thead>
                    <tr><th>Type</th><th>Name</th><th>Phone</th><th>Details</th><th>Description</th><th>Amount</th></tr>
                   </thead><tbody>";
        foreach ($transactions as $row) {
            $table .= "<tr>
                        <td>{$row['entity_type']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['phone']}</td>
                        <td>{$row['transaction_by_details']}</td>
                        <td>{$row['description']}</td>
                        <td>" . number_format($row['amount'], 2) . "</td>
                    </tr>";
        }
        $table .= "</tbody></table>";
        return $table;
    };

    // Debit Table
    $content .= "<div class='col-sm-6'>
                    <h4 class='text-center text-success'>All Debits</h4>
                    {$renderTable('Debits', $debits)}
                    <table class='table table-bordered'>
                        <tr><th>Total Debit in Bank</th><td>" . number_format($totals['debit_bank'], 2) . "</td></tr>
                        <tr><th>Total Debit in Cash</th><td>" . number_format($totals['debit_cash'], 2) . "</td></tr>
                        <tr><th>Actual Total Debit</th><td>" . number_format($totals['debit_bank'] + $totals['debit_cash'], 2) . "</td></tr>
                    </table>
                 </div>";

    // Credit Table
    $content .= "<div class='col-sm-6'>
                    <h4 class='text-center text-danger'>All Credits</h4>
                    {$renderTable('Credits', $credits)}
                    <table class='table table-bordered'>
                        <tr><th>Total Credit in Bank</th><td>" . number_format($totals['credit_bank'], 2) . "</td></tr>
                        <tr><th>Total Credit in Cash</th><td>" . number_format($totals['credit_cash'], 2) . "</td></tr>
                        <tr><th>Actual Total Credit</th><td>" . number_format($totals['credit_bank'] + $totals['credit_cash'], 2) . "</td></tr>
                    </table>
                 </div>";

    $content .= "</div>"; // row

    // Summary Section
    $closing = ($totals['debit_bank'] + $totals['debit_cash']) - ($totals['credit_bank'] + $totals['credit_cash']);
    $content .= "<div class='row mt-4'>
                    <div class='col-12'>
                        <h4 class='text-center'>Closing Summary</h4>
                        <table class='table table-bordered  table-sm'>
                            <tr><th>Closing Cash (Debit - Credit)</th><td>" . number_format($closing, 2) . "</td></tr>
                            <tr><th>Bank Balance</th><td>" . number_format($totals['debit_bank'] - $totals['credit_bank'], 2) . "</td></tr>
                            <tr><th>Cash Balance</th><td>" . number_format($totals['debit_cash'] - $totals['credit_cash'], 2) . "</td></tr>
                        </table>
                    </div>
                 </div>";

    $content .= "</div></div>";
    return $content;
}


protected function CashBookSummery($date1) {
    $conn = $this->connect(); // Get DB connection



    $content = '<div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Summery Report :: ' . date('d-m-Y', strtotime($date1)) . '</h3>
            </div>
            <div class="card-body">
            <div class="row">';

    // Query all transactions for the date
    $query = "
        SELECT 
            at.transaction_date,
            at.invoice_no,
            at.description,
            at.entity_type,
            at.transaction_type,
            at.transaction_by,
            at.amount,
            at.transaction_by_id,
            at.account_id,

            -- Customer/Supplier/Account names
            c.customer_name,
            c.customer_phone AS cphone,
            s.supplier_name,
            s.supplier_phone AS sphone,
            ac.account_name,
            b.bank_name,
            b.account_no
        FROM account_transaction at
        LEFT JOIN setup_customer c ON at.entity_type = 'Customer' AND at.account_id = c.id
        LEFT JOIN setup_suppliers s ON at.entity_type = 'Supplier' AND at.account_id = s.id
        LEFT JOIN setup_account ac ON at.entity_type = 'accounts' AND at.account_id = ac.id
        LEFT JOIN setup_bank b ON at.transaction_by = 'Bank' AND at.transaction_by_id = b.id
        WHERE at.transaction_date = :date1
        ORDER BY at.id ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":date1", $date1, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize totals
    $types = ['Customer', 'Supplier', 'Accounts'];
    $sources = ['Bank', 'Cash'];
    $directions = ['Debit', 'Credit'];

    $totals = [];
    foreach ($types as $type) {
        foreach ($directions as $dir) {
            foreach ($sources as $src) {
                $totals["{$type}_{$dir}_{$src}"] = 0;
            }
        }
    }

    $total_debit = 0;
    $total_credit = 0;
    $debit_rows = '';
    $credit_rows = '';

    foreach ($rows as $r) {
         $entity = ucfirst($r['entity_type']);
        $trans_by = $r['transaction_by'];
        $type = ucfirst($r['transaction_type']);
        $amount = (float) $r['amount'];

        // Build name
        $name = '';
        if ($entity == 'Customer') {
            $name = $r['customer_name'] . ' (' . $r['cphone'] . ')';
        } elseif ($entity == 'Supplier') {
            $name = $r['supplier_name'] . ' (' . $r['sphone'] . ')';
        } else {
            $name = $r['account_name'];
        }

        $method = $trans_by === 'Bank' ? "{$r['bank_name']} ({$r['account_no']})" : 'Cash';

        // Add to totals
        $totals["{$entity}_{$type}_{$trans_by}"] += $amount;
        if ($type === 'Debit') {
            $total_debit += $amount;
            $debit_rows .= "<tr><td>{$r['invoice_no']}</td><td>{$entity}</td><td>{$name}</td><td>{$method}</td><td>{$r['description']}</td><td>" . number_format($amount, 2) . "</td></tr>";
        } else {
            $total_credit += $amount;
            $credit_rows .= "<tr><td>{$r['invoice_no']}</td><td>{$entity}</td><td>{$name}</td><td>{$method}</td><td>{$r['description']}</td><td>" . number_format($amount, 2) . "</td></tr>";
        }
    }

    $closing = $total_debit - $total_credit;

    // Build Summary Table
    $printGroup = function($label, $prefix) use ($totals) {
        $debit_bank = $totals["{$prefix}_Debit_Bank"];
        $debit_cash = $totals["{$prefix}_Debit_Cash"];
        $credit_bank = $totals["{$prefix}_Credit_Bank"];
        $credit_cash = $totals["{$prefix}_Credit_Cash"];

        $total_debit = $debit_bank + $debit_cash;
        $total_credit = $credit_bank + $credit_cash;

        return "
            <tr class='bg-light'><td colspan='3'><b>{$label}</b></td></tr>
            <tr><td>Bank</td><td>" . number_format($debit_bank, 2) . "</td><td>" . number_format($credit_bank, 2) . "</td></tr>
            <tr><td>Cash</td><td>" . number_format($debit_cash, 2) . "</td><td>" . number_format($credit_cash, 2) . "</td></tr>
            <tr><td>Total</td><td><b>" . number_format($total_debit, 2) . "</b></td><td><b>" . number_format($total_credit, 2) . "</b></td></tr>
        ";
    };

    $content .= '<div class="col-sm-12">
        <table class="table table-bordered table-sm">
            <thead><tr class="bg-secondary text-white"><th>Type</th><th>Debit</th><th>Credit</th></tr></thead><tbody>';
    $content .= $printGroup('Customer', 'Customer');
    $content .= $printGroup('Supplier', 'Supplier');
    $content .= $printGroup('Accounts', 'Accounts');
    $content .= '</tbody></table></div>';

    

    $content .= '</div>'; // End of .row

    // Toggle button
    $content .= '<div class="mt-3"><button class="btn btn-info" onclick="document.getElementById(\'rawTrans\').classList.toggle(\'d-none\')">Show/Hide Transactions</button></div>';

    // Raw transactions
    $content .= '<div class="d-none mt-3" id="rawTrans"><div class="row">';
    $content .= '<div class="col-sm-6"><h5>Debit Transactions</h5><table class="table table-bordered table-sm"><thead><tr><th>Invoice</th><th>Type</th><th>Name</th><th>Method</th><th>Description</th><th>Amount</th></tr></thead><tbody>';
    $content .= $debit_rows;
    $content .= '</tbody></table></div>';

    $content .= '<div class="col-sm-6"><h5>Credit Transactions</h5><table class="table table-bordered table-sm"><thead><tr><th>Invoice</th><th>Type</th><th>Name</th><th>Method</th><th>Description</th><th>Amount</th></tr></thead><tbody>';
    $content .= $credit_rows;
    $content .= '</tbody></table></div>';

    $content .= '</div></div>'; // End raw row

    $content .= '</div></div>'; // End of card-body and card

    return $content;
}



}
