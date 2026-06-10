<?php
// Include necessary files
include_once 'includes/config.inc.php';
include_once 'includes/autoloader.inc.php';

$List = new User();
$related_id = $_GET['related_id'] ?? ''; // Prevent undefined index error

foreach ($List->AllMenu() as $menu) { ?>
    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title"><?php echo htmlspecialchars($menu['menu_name']); 

// Condition-based checkbox visibility
$showPermission = true; // Always true for all menus

$specialMenus = ['Sales', 'Purchase', 'Receive & Pay'];

$showBackdate = in_array($menu['menu_name'], $specialMenus);
$showEdit = in_array($menu['menu_name'], array_merge($specialMenus, ['Settings', 'Report']));
$showDelete = in_array($menu['menu_name'], array_merge($specialMenus, ['Settings', 'Report']));
// Always show Delete

        ?></h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Menu Name</th>
                        <th>Permission</th>
                          <?php if ($showBackdate) { ?> <th>Backdate</th> <?php }else{ ?> <th></th><?php } ?>
                         <?php if ($showEdit) { ?> <th>Edit</th> <?php }else{ ?> <th></th><?php } ?>
                         <?php if ($showDelete) { ?> <th>Delete</th> <?php }else{ ?> <th></th><?php } ?>

                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $subMenus = $List->MenuByParent($menu['id']);
                    
                    // Handle both parent and submenu cases
                    $menusToProcess = empty($subMenus) ? [$menu] : $subMenus;
                    
                    foreach ($menusToProcess as $subMenu) { 
                        $isChecked = !empty($List->getUserSingleMenus($related_id, $subMenu['id'])) ? 'checked' : '';
                        $permissions = $List->getUserPermissions($related_id, $subMenu['menu_link']);
                        




                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($subMenu['menu_name']); ?></td>
                            <td>
                                <?php if ($showPermission) { ?>
                                    <div class="icheck-primary d-inline ml-2">
                                        <input type="checkbox" name="menu_permission[]" 
                                               id="MAINMENUtodoCheck<?php echo $subMenu['id']; ?>" 
                                               value="<?php echo $subMenu['id']; ?>" 
                                               <?php echo $isChecked; ?>
                                               onclick="PermissionCheckUncheck('<?php echo $related_id; ?>', '<?php echo $subMenu['id']; ?>', 'MAINMENU')">
                                        <label for="MAINMENUtodoCheck<?php echo $subMenu['id']; ?>"></label>
                                    </div>
                                <?php } ?>
                            </td>
                            
                            <?php if ($isChecked) { ?>
                                <td>
                                    <?php if ($showBackdate) { ?>
                                        <input type="checkbox" id="backdatetodoCheck<?php echo $subMenu['id']; ?>" 
                                               <?php echo ($permissions['can_backdate'] == 1) ? 'checked' : ''; ?> 
                                               onclick="PermissionCheckUncheck('<?php echo $related_id; ?>', '<?php echo $subMenu['id']; ?>', 'backdate')">
                                        <label for="backdatetodoCheck<?php echo $subMenu['id']; ?>"> </label>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if ($showEdit) { ?>
                                        <input type="checkbox" id="edittodoCheck<?php echo $subMenu['id']; ?>" 
                                               <?php echo ($permissions['can_edit'] == 1) ? 'checked' : ''; ?> 
                                               onclick="PermissionCheckUncheck('<?php echo $related_id; ?>', '<?php echo $subMenu['id']; ?>', 'edit')">
                                        <label for="edittodoCheck<?php echo $subMenu['id']; ?>"> </label>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if ($showDelete) { ?>
                                        <input type="checkbox" id="deletetodoCheck<?php echo $subMenu['id']; ?>" 
                                               <?php echo ($permissions['can_delete'] == 1) ? 'checked' : ''; ?> 
                                               onclick="PermissionCheckUncheck('<?php echo $related_id; ?>', '<?php echo $subMenu['id']; ?>', 'delete')">
                                        <label for="deletetodoCheck<?php echo $subMenu['id']; ?>"> </label>
                                    <?php } ?>
                                </td>
                            <?php } else { ?>
                                <td></td><td></td><td></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>
