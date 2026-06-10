<?php
// Include the config.php file
include_once 'includes/config.inc.php';

// Include Configuration File
include_once "includes/autoloader.inc.php";

$List = new BankSetup();
?>
              <table id="example1" class="table table-bordered table-striped">
              <thead>
        <tr>
            <th>SL</th>
            <th>Bank Name</th>
            <th>Account Name</th>

            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php   
        $sl = 1; 
        foreach ($List->BankList() as $row) { // Assuming ListData() fetches data from setup_customer table
        ?>                     
            <tr>
                <input type="hidden" id="customerId<?php echo $row['id']; ?>" value="<?php echo $row['id']; ?>">
                <td style="text-align:center;"><?php echo $sl++; ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($row['bank_name']); ?></td>
                                <td style="text-align:left;"><?php echo htmlspecialchars($row['account_no']); ?></td>

                <td style="text-align:center;">  
                    <a href="bank_setup.php?id=<?php echo $row['id']; ?>">
                        <i class="fa fa-edit" style="color:green;"></i>
                    </a> 
                    
                </td>
            </tr>   
        <?php } ?>
    </tbody>
</table>
