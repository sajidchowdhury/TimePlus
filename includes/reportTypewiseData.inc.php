<?php
include_once "autoloader.inc.php";

if (isset($_POST['report_type'])) {
    $report_type = json_decode($_POST['report_type'], true); 

    if ($report_type === null) {
        echo json_encode(["error" => "Invalid JSON format"]);
        exit;
    }

    $type = $report_type['type'] ?? '';
    $customData = $report_type['customData'] ?? '';

    $date_content = '';
    $content = '';

    // Generate date fields
    if ($customData == 'Need-Two-Date') {

        $date_content = '<div class="form-group">
                            <label>Select Date</label>
                            <input type="text" class="form-control" name="reservation" id="reservation" value="' . date("Y-m-d") . '">
                        </div>';


    } elseif ($customData == 'Need-Single-Date') {


        $date_content = '<div class="form-group">
                            <label>Select Date</label>
                            <input type="date" class="form-control" name="sdate" id="sdate" value="' . date("Y-m-d") . '">
                        </div>';
    }else{

         $date_content = '<div style="display:none" class="form-group">
                            <label>Select Date</label>
                            <input type="date" class="form-control" name="sdate" id="sdate" value="' . date("Y-m-d") . '">
                        </div>';
    }



    // Generate additional input fields based on report type
    if ($type == 'Customer-Wise') {
        $info = new AddCustomer();
        $content = '<input type="hidden" id="related_id" value="customer_id" ><div class="form-group">
                        <label>Customer Name</label>
                        <select class="form-control select2" id="customer_id" name="customer_id">
                            <option value="">Select One</option>';
        
        foreach ($info->ListData() as $fetch) {
            $content .= '<option value="'.$fetch['id'].'">'.$fetch['customer_name'].'</option>';
        }

        $content .= '</select>
                    </div>';

                        echo json_encode([
        "date_content" => $date_content,
        "content" => $content
    ]);
    exit;


    }


        if ($type == 'Supplier-Wise') {

        $info = new AddSupplier();
        $content = '<input type="hidden" id="related_id" value="supplier_id" > <div class="form-group">
                        <label>Supplier Name</label>
                        <select class="form-control select2" id="supplier_id" name="supplier_id">
                            <option value="">Select One</option>';
        
        foreach ($info->ListData() as $fetch) {
            $content .= '<option value="'.$fetch['id'].'">'.$fetch['supplier_name'].'</option>';
        }

        $content .= '</select>
                    </div>';

                        echo json_encode([
        "date_content" => $date_content,
        "content" => $content
    ]);
    exit;


    }


        if ($type == 'Due-Summery' || $type == 'Invoice-Wise' || $type == 'Summery'|| $type == 'Daily-Cash'|| $type == 'All' || $type == 'Date-Wise-Collection' ) {

        $info = new AddSupplier();
        $content = '<input type="hidden" id="related_id" value="All" >
        <input type="hidden" id="All" value="All" >';

                        echo json_encode([
        "date_content" => $date_content,
        "content" => $content
    ]);
    exit;


    }


        if ($type == 'Invoice-Wise-Search') {

        $info = new AddSupplier();
        $content = '<input type="hidden" id="related_id" value="invoice_no" >
    <label for="sales_rate">Invoice No</label>
    <input required   type="text" class="form-control" name="invoice_no" id="invoice_no" value="" />
';

                        echo json_encode([
        "date_content" => $date_content,
        "content" => $content
    ]);
    exit;


    }






  if ($type == 'Ledger-Wise') {

        $info = new LedgerSetup();
        $content = '<input type="hidden" id="related_id" value="ledger_id" > <div class="form-group">
                        <label>Ledger</label>
                        <select class="form-control select2" id="ledger_id" name="ledger_id">
                            <option value="">Select One</option>';
        
        foreach ($info->ListLedgerData() as $fetch) {
            $content .= '<option value="'.$fetch['id'].'">'.$fetch['ledger_name'].'</option>';
        }

        $content .= '</select>
                    </div>';

                        echo json_encode([
        "date_content" => $date_content,
        "content" => $content
    ]);
    exit;


    }

      if ($type == 'User-Wise') {

        $info = new User();
        $content = '<input type="hidden" id="related_id" value="user_id" > <div class="form-group">
                        <label>User Name</label>
                        <select class="form-control select2" id="user_id" name="user_id">
                            <option value="">Select One</option>';
        
        foreach ($info->ListData() as $fetch) {
            $content .= '<option value="'.$fetch['id'].'">'.$fetch['employee_name'].'</option>';
        }

        $content .= '</select>
                    </div>';

                        echo json_encode([
        "date_content" => $date_content,
        "content" => $content
    ]);
    exit;


    }

if ($type == 'Product-Wise') {

        $info = new AddItem();
        $content = '<input type="hidden" id="related_id" value="product_id" > <div class="form-group">
                        <label>Product Name</label>
                        <select class="form-control select2" id="product_id" name="product_id">
                            <option value="">Select One</option>';
        
        foreach ($info->ListData() as $fetch) {
            $content .= '<option value="'.$fetch['id'].'">'.$fetch['code'].' ' . $fetch['product_description'] . '</option>';
        }

        $content .= '</select>
                    </div>';

                        echo json_encode([
        "date_content" => $date_content,
        "content" => $content
    ]);
    exit;


    }

  if ($type == 'Account-Wise') {

        $info = new LedgerSetup();
        $content = '<input type="hidden" id="related_id" value="account_id" > <div class="form-group">
                        <label>Account</label>
                        <select class="form-control select2" id="account_id" name="account_id">
                            <option value="">Select One</option>';
        
        foreach ($info->ListAccountData() as $fetch) {
            $content .= '<option value="'.$fetch['id'].'">'.$fetch['account_name'].'</option>';
        }

        $content .= '</select>
                    </div>';

                        echo json_encode([
        "date_content" => $date_content,
        "content" => $content
    ]);
    exit;


    }






    // Send JSON response

} else {
    echo json_encode(["error" => "No data received"]);
}
?>
