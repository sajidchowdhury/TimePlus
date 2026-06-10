<?php
if (isset($_GET['transaction_by'])) {
    $transaction_by = $_GET['transaction_by']; // Fix incorrect variable name

    // Load necessary files
    include "autoloader.inc.php";




    $options = '<select name="transaction_by_id" id="transaction_by_id" required class="form-control select2" style="width: 100%;">';

     if($transaction_by == 'Cash' ){
    $options .= '<option value="Cash">Cash</option>';
     }else{
    // Create a new object

    $action = new BankSetup();
    $result = $action->BankList();
    foreach ($result as $row) {

    $options .= '<option value="'.$row['id'].'">'.$row['bank_name'].' ::: ' . $row['account_no'].'</option>';
           
            }



     }
    // Generate HTML for select options

   
    $options .= '</select>';

    echo $options;  // Output the HTML
    exit();
}






?>
