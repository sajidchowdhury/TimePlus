<?php

// Include configuration and autoloaders
include_once 'includes/config.inc.php';
include_once 'includes/autoloader.inc.php';

// Start session if not started
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Ensure user is logged in
if (!isset($_SESSION['admin_access_token'])) {header('Location: login.php');exit();}

$user_id = $_SESSION['admin_access_token'];
$sales = new Sales();
$itemHelper = new AddItem();

$cart_data = [];
$invoice_id = $_GET['id'] ?? 'New';



// Initialize discount defaults

$discount_percentage = 0.00;
$actual_discount = 0.00;



if ($invoice_id !== 'New') {

    // Clear old session cart for this invoice load
    $_SESSION['sales_edit_cart'][$user_id]= [];

    // Load data from DB
    $cart_data = $sales->InvoiceItems($invoice_id);
    $invoiceDetails = $sales->InvoiceDetails($invoice_id);
    $discount_percentage = !empty($invoiceDetails['discount']) ? $invoiceDetails['discount'] : 18.00; // Default 18%
    $actual_discount = ($discount_percentage > 0) ? $invoiceDetails['total_amount'] * ($discount_percentage / 100) : 0.00;
    $adjustment = !empty($invoiceDetails['adjustment']) ? $invoiceDetails['adjustment'] : 0.00; // New field
    $receive_now  = !empty($invoiceDetails['PaidNow']) ? $invoiceDetails['PaidNow'] : 0.00;


    $invoicePrice = $total - $actual_discount - $adjustment;
    $invoice_Due = $invoicePrice - $receive_now;


    // Store loaded items in session with status for tracking

    foreach ($cart_data as $item) {

        $composite_key = $item['product_id'] . '___' . $item['expiry_date'];

        $_SESSION['sales_edit_cart'][$user_id][$composite_key] = array_merge($item, ['status' => 'EXISTING','item_id' => $item['id']]);

    }



    // Use session again for displaying

    $cart_data = $_SESSION['sales_edit_cart'][$user_id];



    } else {

    // Load from session if it's a new invoice

    if (!isset($_SESSION['sales_cart'][$user_id])) {
        $_SESSION['sales_cart'][$user_id] = [];
    }

    $cart_data = $_SESSION['sales_cart'][$user_id];
    $receive_now  = 0 ;

}






?>



<!-- Display Cart -->

<div id="load_cart">
    <table id="example1" class="table table-bordered table-striped table-condensed">
        <thead>
            <tr>
                <th>SL</th>
                <th>Code</th>
                <th>Description</th>
                <th>Expiry Date</th>
                <th>Quantity</th>
                <th>Rate</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>

            <?php
            $sl = 1;
            $total = 0;
            $count = 0;
            $total_qty = 0;

            if (!empty($cart_data)) {

                foreach ($cart_data as $key => $item) {

                    $product_id = $item['product_id'];

                    $expiry_date = $item['expiry_date'];

                    $qty = (int)$item['quantity'];

                    $rate = (float)$item['price'];

                    $total_price = $qty * $rate;



                    $product = $itemHelper->SingleData($product_id);

                    $rowId = (isset($_GET['id']) && $_GET['id'] !== "New") ?  $item['id'] : $key ; 



                    $total += $total_price;

                    $total_qty += $qty;

                    $count++;

            ?>

                    <tr>

                        <td class="text-center"><?= $sl++; ?></td>

                        <td><?= htmlspecialchars($product["code"]) ?></td>

                        <td><?= htmlspecialchars($product["product_description"] . ' ' . $product["pack_size"]) ?></td>

                        <td><?= htmlspecialchars($expiry_date) ?></td>

                        <td class="text-center"><?= $qty ?></td>

                        <td class="text-center"><?= number_format($rate, 2) ?></td>

                        <td><?= number_format($total_price, 2) ?></td>

                        <td class="text-center">

                            <button class="delete-item" data-item_id="<?= $rowId ?>" onclick="return confirm('Are you sure you want to remove this item?');">

                                <i class="fa fa-trash" style="color:red;"></i>

                            </button>

                        </td>

                    </tr>

            <?php

                }

            } else {

                echo "<tr><td colspan='8' class='text-center'>No items found</td></tr>";

            }



            ?>

            <input type="hidden" name="countItem" id="countItem" value="<?=  $count; ?>">



            <tr>
                <td colspan="6" class="text-right fw-bold">Sub Total</td>
                <td><input type="number" readonly class="form-control" id="subtotal" name="subtotal" value="<?= number_format((float)$total, 2, '.', '') ?>"></td>
                <td></td>
            </tr>

            <!-- Customer Discount -->
            <tr>
                <td colspan="6" class="text-right fw-bold">Discount (%)</td>
                <td>
                    <input type="number" step="any" class="form-control" name="discount_percentage" 
                           id="discount_percentage" value="<?= $discount_percentage ?>" 
                           onkeyup="Calculate()">
                </td>
                <td></td>
            </tr>
            <tr>
                <td colspan="6" class="text-right fw-bold">Discount Amount</td>
                <td>
                    <input type="number" readonly step="any" class="form-control" 
                           id="actual_discount" value="<?= number_format($actual_discount, 2, '.', '') ?>">
                </td>
                <td></td>
            </tr>

<!-- Adjustment Row -->
<tr style="background:#f8f9fa;">
    <td colspan="6" class="text-right fw-bold text-danger">Adjustment / Extra Discount</td>
    <td>
        <input type="number" step="any" class="form-control text-danger" 
               id="adjustment" name="adjustment" value="<?= number_format($adjustment, 2, '.', '') ?>" 
               onkeyup="Calculate()">
    </td>
    <td class="text-center">
        <button type="button" class="btn btn-sm btn-danger" onclick="makeDueZero()">
            <i class="fa fa-magic"></i> Make Due 0
        </button>
    </td>
</tr>

<!-- Rest of the table (Invoice Price, Receive, Due) -->
<tr>
    <td colspan="6" class="text-right fw-bold">Invoice Price</td>
    <td><input type="number" readonly class="form-control font-weight-bold" 
               id="invoicePrice" name="invoicePrice" value="<?= number_format($invoicePrice, 2, '.', '') ?>"></td>
    <td></td>
</tr>

            <!-- Receive & Due -->
            <tr>
                <td colspan="6" class="text-right fw-bold">Receive By</td>
                <td>
                    <!-- existing bank select -->
                    <select name="transaction_by_id" id="transaction_by_id" class="form-control select2">
                        <option value="0">Cash</option>
                        <?php 
                        $List = new BankSetup();
                        foreach ($List->BankList() as $row) { ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['bank_name']); ?> :: <?php echo htmlspecialchars($row['account_no']); ?></option>
                        <?php } ?>  
                    </select>
                </td>
                <td></td>
            </tr>

            <tr>
                <td colspan="6" class="text-right fw-bold">Receive</td>
                <td><input type="number" onkeyup="Calculate()" class="form-control" 
                           id="receive_now" name="receive_now" value="<?= number_format((float)$receive_now, 2, '.', '') ?>"></td>
                <td></td>
            </tr>

            <tr style="background:#e8f5e9;">
                <td colspan="6" class="text-right fw-bold">Due</td>
                <td><input type="number" readonly class="form-control font-weight-bold" 
                           id="invoice_Due" name="invoice_Due" value=""></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>

