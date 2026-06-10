<?php
// Include the config.php file
include 'includes/config.inc.php';

// Include Configuration File
include "includes/autoloader.inc.php";



$scripts = array(
            'plugins/select2/js/select2.full.min.js',
            'plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js',
            'plugins/moment/moment.min.js',
            'plugins/inputmask/min/jquery.inputmask.bundle.min.js',
            'plugins/daterangepicker/daterangepicker.js',
            'js/gijgo.min.js',
            'plugins/sweetalert2/sweetalert2.min.js',
            'plugins/datatables/jquery.dataTables.min.js',
            'plugins/datatables-bs4/js/dataTables.bootstrap4.min.js',
            'plugins/datatables-responsive/js/dataTables.responsive.min.js',
            'plugins/datatables-responsive/js/responsive.bootstrap4.min.js',
            'js/datepicker.js',
            'js/DataTable.js'  ,
            'js/modal.js'  ,
            'js/AllReport.js',
            'js/sales_return.js'


    );




$siteStructure = new SiteStructure($scripts,'sales_return','');
$Form = new SalesReturnPlate();

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
            <h1 class="m-0 text-dark">Sales Return</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Sales Return</a></li>
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
