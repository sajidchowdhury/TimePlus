<?php 


class DashboardPlate {


	private $id ; 

	public function __construct($id = 'New')
	{
		$this->id = $id ; 

	}


	public function SetupForm(){ 
    

  

        $content =  '';


        $content .=  '    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-6 ">
            <div class="card">
              <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                  <h3 class="card-title"></h3>
                  <a ></a>
                </div>
              </div>
              <div class="card-body">
                <div class="d-flex">
                  <p class="d-flex flex-column">
                    <span class="text-bold text-lg"></span>
                    <span></span>
                  </p>
                  <p class="ml-auto d-flex flex-column text-right">
                    <span class="text-success">
                      <i class="fas fa-arrow-up"></i> <b id="sales-percentage">0 %  </b>
                    </span>
                    <span class="text-muted">Since last week</span>
                  </p>
                </div>
                <!-- /.d-flex -->

                <div class="position-relative mb-4 ">
                  <canvas id="visitors-chart" height="200"></canvas>
                </div>

                <div class="d-flex flex-row justify-content-end">
                  <span class="mb-4">
                  </span>

                  <span>
                  </span>
                </div>
              </div>
            </div>
            <!-- /.card -->

           
           
          </div>
          <!-- /.col-md-6 -->
          <div class="col-lg-6">
            <div class="card">
             
                

                <div class="position-relative mb-4">

            <!-- Info Boxes Style 2 -->
            <div class="info-box mb-2 bg-warning">
    <span class="info-box-icon"><i class="fas fa-tag"></i></span>
    <div class="info-box-content">
        <span class="info-box-text">Today Customer Receive</span>
        <span class="info-box-number" id="todayCustomerReceive">0</span>
    </div>
</div>

<div class="info-box mb-2 bg-success">
    <span class="info-box-icon"><i class="far fa-heart"></i></span>
    <div class="info-box-content">
        <span class="info-box-text">Today Supplier Payment</span>
        <span class="info-box-number" id="todaySupplierPayment">0</span>
    </div>
</div>

<div class="info-box mb-2 bg-danger">
    <span class="info-box-icon"><i class="fas fa-cloud-download-alt"></i></span>
    <div class="info-box-content">
        <span class="info-box-text">Today Income Voucher</span>
        <span class="info-box-number" id="todayIncomeVoucher">0</span>
    </div>
</div>

<div class="info-box mb-2 bg-info">
    <span class="info-box-icon"><i class="far fa-comment"></i></span>
    <div class="info-box-content">
        <span class="info-box-text">Today Expense Voucher</span>
        <span class="info-box-number" id="todayExpenseVoucher">0</span>
    </div>
</div>

          
        
            <!-- /.card -->
          </div>

         

                <div class="d-flex flex-row justify-content-end">
               
                </div>
              </div>
            </div>
            <!-- /.card -->

           
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </div>
      ';


print $content;


    }





}