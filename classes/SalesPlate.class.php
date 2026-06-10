<?php 





class SalesPlate {


	private $id ; 



	public function __construct($id = 'New')

	{

		$this->id = $id ; 



	}





	public function SetupForm(){ 

    





    if($this->id == 'New' ){         

  

     $customer_id = $invoice_no = time(); $invoice_date =  date("Y-m-d") ; $discount_percentage = 0.00 ; 

     $actual_discount = 0.00;



     $sales_person = $_SESSION['admin_access_token'] ; 

     $button = ' <input type="submit" name="finalSubmit" id="finalSubmit" class="btn btn-primary" value="Submit">';



    

    }else{

            $info = new Sales();

            $data = $info->InvoiceDetails($this->id);



           $customer_id = $data['customer_id'] ;

           $invoice_no = $data['invoice_no'] ;

           $invoice_date = $data['invoice_date'] ;

           $discount_percentage = !empty($data['discount']) ? $data['discount'] : 0.00  ;

           $actual_discount = ($discount_percentage > 0) ? number_format(($data['total_amount'] * ($discount_percentage/100)), 2, '.', '')  : 0.00 ;

           $sales_person = $data['sales_person'] ;



     $button = '<input type="submit" name="finalSubmit" id="finalSubmit" class="btn btn-primary" value="Submit">';

    }

            $List = new AddCustomer();

            $UserList = new User();



  



        $content =  '';





        $content .=  ' <div class="row">

        <div class="col-md-12" style="margin-bottom: 0px!important;"> 



        <form action="" id="kt_form" class="form-horizontal" method="post">

        <input type="hidden" name="csrf_token"  value="'.$_SESSION['csrf_token'].'">

        <input type="hidden" name="related_id" id="related_id" value="'.$this->id.'">

        <input type="hidden" name="user_id" id="user_id" value="'.$_SESSION['admin_access_token'].'">

        <input type="hidden" name="prev_invoice_date" id="prev_invoice_date" value="'.$invoice_date.'">



       <div class="row">

          <!-- left column -->

          <div class="col-md-8">

            <!-- general form elements -->

            <div class="card card-primary">

              <div class="card-header">

                <h3 class="card-title">Entry Table</h3>

              </div>

              <!-- /.card-header -->

              <!-- form start -->

              <form role="form" id="myForm">

              <input type="hidden" name="action" id="action" value="save">



                <div class="card-body">

                <div class="row">



               <div class="col-md-8">

                     <div class="form-group">

                  <label>Item Name</label>

<select id="productSearch" class="form-control" style="width: 100%;"></select>

                </div>

                  </div>



  

<div class="col-md-4">

  <div class="form-group">

    <label for="sales_rate">Sales Rate</label>

    <input required  READONLY type="number" step=0.01 class="form-control" name="sales_rate" id="sales_rate" value="" />

  </div>

</div>





    <div class="col-md-8">

                    <div class="form-group">

                    <label for="qty">QTY</label>

                      <input required type="number" step=0.01 class="form-control" name="qty" id="qty"  value="" autocomplete="off" />

                    </div>

                  </div>





  <div class="col-md-4">

  <div class="form-group">

    <label for="product_stock_select">Stock (Expiry Date-wise)</label>

    <select class="form-control" id="product_stock_select" name="product_stock_select">

      <option value="">Select stock</option>

    </select>

  </div>

</div>





              



                </div>

                </div>

                <!-- /.card-body -->



                <div class="card-footer">

                <input type="submit" name="submit" id="submit" class="btn btn-secondary float-right" value="Add To Cart">

                </div>

              </form>

            </div>

            <!-- /.card -->

          </div>

          <!--/.col (left) -->



          <div class="col-md-4">

            <!-- general form elements -->

            <div class="card card-primary">

              <div class="card-header">

                <h3 class="card-title">Final Entry</h3>

              </div>

              <!-- /.card-header -->

              <form role="form" id="myForm2">

                <div class="card-body">

                <input type="hidden" name="action" id="action" value="update">

                <div class="row">





              <div class="col-md-12">

                  <div class="form-group">

                      <label for="sales_person">Sales By</label>

                      <select name="sales_person" id="sales_person" required class="form-control select2" style="width: 100%;">

                          <option value="">Select One</option>';

                          foreach ($UserList->ListData() as $row) { 

                              $content .= '<option ';

                          if($sales_person == $row['id']){ $content .= ' selected = "selected" '; } else{ } $content .= ' value="'.$row['id'].'">'.$row['employee_name'].'</option>';

                          }

                          $content .= '

                      </select>

                  </div>

              </div>







                  <div class="col-md-12">

                    <div class="form-group">

                      <label for="invoice">Date</label>

                      <input type="date" class="form-control" id="invoice_date" name="invoice_date"  value="'.$invoice_date.'">



                    </div>

                  </div>







                  <div class="col-md-12">

                    <div class="form-group">

                      <label for="invoice">Invoice</label>

                      <input type="text" class="form-control" id="invoice" name="invoice" placeholder="00" value="'.$invoice_no.'">

                    </div>

                  </div>



               <div class="col-md-12">

                                    <div class="form-group">

                                        <label for="customer_id">Customer Name</label>

                                        <select name="customer_id" id="customer_id" required class="form-control select2" style="width: 100%;">

                                            <option value="">Select One</option>';

                                            foreach ($List->ListData() as $row) { 

                                                $content .= '<option ';

                                            if($customer_id == $row['id']){ $content .= ' selected = "selected" '; } else{ } $content .= ' value="'.$row['id'].'">'.$row['customer_phone'].' - '.$row['customer_name'].'</option>';

                                            }

                                            $content .= '

                                        </select>

                                    </div>

                                </div>

                </div>

                </div>

                <div class="card-footer">



               '.$button.'



                </div>

                </form>

            </div>

            <!-- /.card -->

          </div>



        </div> <!-- /.row -->



        <div class="row">

          <!-- left column -->

          <div class="col-md-12">

            <!-- general form elements -->

            <div class="card card-primary">

              <div class="card-header">

                <h3 class="card-title">Cart</h3>

              </div>

              <!-- /.card-header -->

              <!-- form start -->

              

            </div>';

   









      $content .= '<div id="load_cart">';

      ob_start();

      include("sales_cart.php");

      $content .= ob_get_clean();

      $content .= '</div>





  </div>

   

      </div>

      </div>

      ';





print $content;





    }











}