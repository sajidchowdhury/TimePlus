<?php
if ($_POST['action'] == 'delete_invoice') {
    // Ensure POST data is sanitized and validated before usage
    $related_id = isset($_POST['related_id']) ? $_POST['related_id'] : null;
    $page_name = isset($_POST['PageName']) ? $_POST['PageName'] : null;

    if (!$related_id || !$page_name) {
        echo json_encode(["status" => "error", "message" => "Invalid request."]);
        exit();
    }


    include "autoloader.inc.php"; // Include the autoloader for classes



    $action = new TransactionContr($related_id, $page_name);
    $result = $action->DeleteFullInvoice(); 

    // Return the result status
    echo json_encode($result);
    exit();


}
?>
