<?php
// Include the config.php file
include_once 'includes/config.inc.php';

// Include Configuration File
include_once "includes/autoloader.inc.php";

$List = new AddCustomer();
?>
              <table id="example1" class="table table-bordered table-striped">
              <thead>
        <tr>
            <th>SL</th>
            <th>Customer Name</th>
            <th>Customer Phone</th>
            <th>Address</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php   
        $sl = 1; 
        foreach ($List->ListData() as $row) { // Assuming ListData() fetches data from setup_customer table
        ?>                     
            <tr>
                <input type="hidden" id="customerId<?php echo $row['id']; ?>" value="<?php echo $row['id']; ?>">
                <td style="text-align:center;"><?php echo $sl++; ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($row['customer_phone']); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($row['address']); ?></td>
                <td style="text-align:center;">  
                    <a href="add_customer.php?id=<?php echo $row['id']; ?>">
                        <i class="fa fa-edit" style="color:green;"></i>
                    </a> 
                    
                </td>
            </tr>   
        <?php } ?>
    </tbody>
</table>
