<?php
// Include the config.php file
include_once 'includes/config.inc.php';

// Include Configuration File
include_once "includes/autoloader.inc.php";

$List = new LedgerSetup();
?>
              <table id="example1" class="table table-bordered table-striped">
              <thead>
        <tr>
            <th>SL</th>
            <th>Ledger Name</th>
            <th>Account Name</th>

            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php   
        $sl = 1; 
        foreach ($List->ListAccountData() as $row) { // Assuming ListData() fetches data from setup_customer table
        ?>                     
            <tr>
                <input type="hidden" id="customerId<?php echo $row['id']; ?>" value="<?php echo $row['id']; ?>">
                <td style="text-align:center;"><?php echo $sl++; ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($row['ledger_name']); ?></td>
                <td style="text-align:left;"><?php echo htmlspecialchars($row['account_name']); ?></td>

                <td style="text-align:center;">  
                    <a href="account_setup.php?id=<?php echo $row['id']; ?>">
                        <i class="fa fa-edit" style="color:green;"></i>
                    </a> 
                    
                </td>
            </tr>   
        <?php } ?>
    </tbody>
</table>
