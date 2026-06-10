<?php
// Load configuration and autoloader  
require_once 'includes/config.inc.php';  
require_once 'includes/autoloader.inc.php';  

// Define required script files  
$scripts = [  
            'plugins/sweetalert2/sweetalert2.min.js',  
            'plugins/jquery-ui/jquery-ui.js',
            'plugins/datatables/jquery.dataTables.min.js',
            'plugins/datatables-bs4/js/dataTables.bootstrap4.min.js',
            'plugins/datatables-responsive/js/dataTables.responsive.min.js',
            'plugins/datatables-responsive/js/responsive.bootstrap4.min.js',
            'js/DataTable.js'  ,
            'js/AddCustomer.js'  
];  



// Initialize site structure and user form  
$siteStructure = new SiteStructure($scripts, 'add_customer', '');  

$id = isset($_GET['id']) ? htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8') : 'New';
$Form = new AddCustomerPlate($id);
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
            <h1 class="m-0 text-dark">Add Customer</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Settings</a></li>

              <li class="breadcrumb-item active">Add Customer</li>
            </ol>
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
