<?php
if (isset($_GET['ledger_id'])) {
    $ledger_id = $_GET['ledger_id']; // Fix incorrect variable name

    // Load necessary files
    include "autoloader.inc.php";

    // Create a new object
    $action = new LedgerSetup();
    $result = $action->LedgerWiseAccountData($ledger_id);

    // Generate HTML for select options
    $options = '<select name="account_id" id="account_id" required class="form-control select2" style="width: 100%;">';
    $options .= '<option value="">Select One</option>';

    foreach ($result as $row) {
        $options .= '<option value="'.$row['id'].'">'.$row['account_name'].'</option>';
    }

    $options .= '</select>';

    echo $options;  // Output the HTML
    exit();
}
?>
