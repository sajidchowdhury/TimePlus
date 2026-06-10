<?php
// Include necessary files
include_once 'includes/config.inc.php';
include_once 'includes/autoloader.inc.php';

$List = new AddItem();
$related_id = $_GET['related_id'] ?? ''; // Prevent undefined index error
 ?>
<table id="example1" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>SL</th>
            <th>Date</th>
            <th>Price</th>
            <th>Changed By</th>
        </tr>
    </thead>
    <tbody>
        <?php   
        $sl = 1; 
        foreach ($List->PriceHistory($related_id) as $row) {  
        ?>                     
            <tr>
                <input type="hidden" id="productId<?php echo $row['id']; ?>" value="<?php echo $row['id']; ?>">
                <td style="text-align:center;"><?php echo $sl++; ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($row['changed_date']); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($row['price']); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($row['employee_name']); ?></td>

                
            </tr>   
        <?php } ?>
    </tbody>
</table>
