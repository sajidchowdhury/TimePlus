<?php
// Include the config.php file
include 'includes/config.inc.php';

// Include Configuration File
include "includes/autoloader.inc.php";


$scripts = array(
    'plugins/jquery/jquery.min.js',
    'plugins/bootstrap/js/bootstrap.bundle.min.js',
    'plugins/chart.js/Chart.min.js',
    'dist/js/demo.js',
    'js/dashboard.js'
    );

$menuItems = [
    ['name' => 'ড্যাশবোর্ড', 'link' => 'home.php'],
];



$siteStructure = new SiteStructure($scripts,'home','');
 $Form = new DashboardPlate();


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
            <h1 class="m-0 text-dark">Dashboard</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Dashboard</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
             
                   <?php  echo $Form->SetupForm();?>

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
