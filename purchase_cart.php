<?php
// Include the config.php file
include_once 'includes/config.inc.php';

// Include Configuration File
include_once "includes/autoloader.inc.php";
// Start the session only if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in with an active session
if (!isset($_SESSION['admin_access_token'])) {
    // Redirect to login page or show an error message
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['admin_access_token']; // Get the unique user ID from session

$purchase = new Purchase();
$itemHelper = new AddItem();


$cart_data = [];
$invoice_id = $_GET['id'] ?? 'New';


if ($invoice_id !== 'New') {
    // Clear old session cart for this invoice load
    $_SESSION['purchase_edit_cart'][$user_id]= [];

    // Load data from DB
    $cart_data = $purchase->InvoiceItems($invoice_id);

    // Store loaded items in session with status for tracking
    foreach ($cart_data as $item) {
        $composite_key = $item['product_id'] . '___' . $item['expiry_date'];
        $_SESSION['purchase_edit_cart'][$user_id][$composite_key] = array_merge($item, ['status' => 'EXISTING','item_id' => $item['id']]);
    }

    // Use session again for displaying
    $cart_data = $_SESSION['purchase_edit_cart'][$user_id];

} else {

    // Load from session if it's a new invoice
    if (!isset($_SESSION['cart'][$user_id])) {
        $_SESSION['cart'][$user_id] = [];
    }
    $cart_data = $_SESSION['cart'][$user_id];
}

// Display the cart
?>
<div id="load_cart">
    <table id="example1" class="table table-bordered table-striped">
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

                                  <?php if($invoice_id == 'New'){ ?>

<button class="delete-item" data-item_id="<?= $rowId ?>" onclick="return confirm('Are you sure you want to remove this item?');">
                                <i class="fa fa-trash" style="color:red;"></i>
                            </button>

                                  <?php } ?>
                            
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
                <td colspan="4" style="text-align:right; font-weight:bold;">Total:</td>
                <td style="text-align:center;"><?php echo number_format($total_qty, 2); ?></td>
                <td></td>
                <td style="text-align:center;"></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>
