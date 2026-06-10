<?php
// Include necessary files
include_once 'includes/config.inc.php';
include_once "includes/autoloader.inc.php";

$List = new AddItem();
?>
<table id="example1" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>SL</th>
            <th>Code</th>
            <th>Product Group</th>
                        <th>Orgin</th>
            <th>Product Description</th>
            <th>Pack Size</th>
                        <th>Sales Price</th>

            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php   
        $sl = 1; 
        foreach ($List->ListData() as $row) {  
        ?>                     
            <tr>
                <input type="hidden" id="productId<?php echo $row['id']; ?>" value="<?php echo $row['id']; ?>">
                <td style="text-align:center;"><?php echo $sl++; ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($row['code']); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($row['product_group']); ?></td>
                                <td style="text-align:left;"><?php echo htmlspecialchars($row['product_orgin']); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($row['product_description']); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($row['pack_size']); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($row['price']); ?>
                    
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modal-xl"
    id="exampleModalCenter" 
    onclick="openModal('PriceHistory', 'productId<?php echo $row['id']; ?>', 'Price History');">
    Price History
</button>
                </td>

                <td style="text-align:center;">  
                    <a href="add_item.php?id=<?php echo $row['id']; ?>">
                        <i class="fa fa-edit" style="color:green;"></i>
                    </a> 
                </td>
            </tr>   
        <?php } ?>
    </tbody>
</table>
