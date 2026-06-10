<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read and decode the JSON request body
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    // Validate structure and required keys
    if (
        isset($data['items'], $data['sales_id'], $data['customer_id'], $data['invoice_date']) &&
        is_array($data['items']) && !empty($data['items'])
    ) {
        include "autoloader.inc.php"; // Include your autoloader

        // Extract values
        $items = $data['items'];
        $sales_id = $data['sales_id'];
        $customer_id = $data['customer_id'];
        $invoice_date = $data['invoice_date'];

        try {
            // Call your controller
            $return = new SalesReturnContr('Sales Return', $items, '', '', $customer_id, $invoice_date, $sales_id);
            $response = $return->Action();

            // Output the result as JSON
            echo json_encode($response);
        } catch (Exception $e) {
            // Handle any errors gracefully
            echo json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing or invalid data.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}
