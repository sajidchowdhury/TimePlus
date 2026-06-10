<?php
// Load configuration and autoloader  
require_once 'includes/config.inc.php';  
require_once 'includes/autoloader.inc.php';  

// Define required script files  
$scripts = [  
    'plugins/sweetalert2/sweetalert2.min.js',  
    'plugins/jquery-ui/jquery-ui.js',
    'js/modal.js' , 
    'js/CreateUser.js'  
];  

// Determine the operation type (New or Edit)  
$id = $_GET['id'] ?? 'New';  

// Initialize site structure and user form  
$siteStructure = new SiteStructure($scripts, 'EditUSer', '');  
$Form = new EditUserPlate($id);  
?>
<html>
<?php echo $siteStructure->head();?>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <?php echo $siteStructure->TopNav();?>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
 
  <?php echo $siteStructure->PageSidebar($_SESSION['admin_access_token']);?>
    

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?php print $_SESSION['admin_access_name'] ;?> Profile</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
       

      <?php echo $Form->SetupForm();?>


      
    </div>
          <!-- /.col -->

       
          
        <!-- /.row -->
      </div><!--/. container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <?php echo $siteStructure->footer();?>
  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->

<?php print $siteStructure->includeScripts(); ?>

</body>
</html>
