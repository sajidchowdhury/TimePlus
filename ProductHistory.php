<?php
// Include necessary files
include_once 'includes/config.inc.php';
include_once 'includes/autoloader.inc.php';

$info = new AddItem();
$item_details = $info->SingleDataByCode($_GET['related_id']) ; 

if(!empty($item_details['code'])){
$action = new StockContr('Single-Product-Stock',$item_details['id']);
$stock = $action->Report(); 
}else{
$stock = 0 ;
}



 ?>
<table id="example1" class="table table-bordered table-striped">

         <tr>
            <th colspan="6" style="text-align: center;">Product Details</th>

        </tr>
        <tr>
            <th>Code</th>
            <th>Product Group</th>
            <th>Product Description</th>
            <th>Pack Size</th>
            <th>Sales Price</th>
            <th>Stock</th>

        </tr>

            
            <tr>
                <td style="text-align:left;"><?php echo htmlspecialchars($item_details['code']); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($item_details['product_group']); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($item_details['product_description']); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($item_details['pack_size']); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($item_details['price']); ?>
                                <td style="text-align:left;"><?php echo htmlspecialchars($stock); ?>

            </tr>   


  
</table>
