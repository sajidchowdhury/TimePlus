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
            'js/AllReport.js',
            'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js',
            'https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js',
            'https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js',
            'https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js'

    );








$siteStructure = new SiteStructure($scripts,'stock_report','');
$Form = new ReportStockPlate();

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
            <h1 class="m-0 text-dark">Stock Report</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Report</a></li>
              <li class="breadcrumb-item active">Stock Report</li>
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
