<?php
// Include the config.php file
include_once 'includes/config.inc.php';

// Include Configuration File
include_once "includes/autoloader.inc.php";

$List = new User();
?>
<table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>Name</th>
                  <th>E-mail</th>
                  <th>Phone</th>
                  <th>User Type</th>

                  <th>Block</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php   

                $sl = 1; 
                  foreach($List->ListData()  AS $row) {

                    
                ?>                     
                        
                    <tr>
<input type="hidden" id="userId<?php echo $row['id']; ?>" value="<?php print $row['id'];?>">
                        <td style="text-align:center;"><?php echo $sl++; ?></td>
                        <td style="text-align:left;"><?php echo $row['employee_name']; ?></td>
                        <td style="text-align:left;"><?php echo $row['email']; ?></td>

                        <td style="text-align:left;"><?php echo $row['employee_number']; ?></td>
                        <td style="text-align:left;"><?php echo $row['user_type']; ?></td>

                        <td style="text-align:left;"><?php echo $row['block_user']; ?></td>

                        <td style="text-align:center;">  
                        <a href="user_create.php?id=<?php echo $row['id']; ?>"> <i class="fa fa-edit" style="color:green;"></i> </a> 
                      

                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modal-xl"
    id="exampleModalCenter" 
    onclick="openModal('menuPermission', 'userId<?php echo $row['id']; ?>', 'Menu Permission');">
    Menu
</button>

                        </td>
                    </tr>   

                <?php  } // while ?>
                </tbody>
               
              </table>