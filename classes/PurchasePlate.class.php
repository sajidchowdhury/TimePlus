<?php 


class PurchasePlate {


	private $id ; 
	public function __construct($id = 'New')
	{
		$this->id = $id ; 
	}


	public function SetupForm(){ 
    
    
  
     if($this->id == 'New' ){
    $rate = 0 ; 
   $qty = $invoice = $supplier_id = ''; $expiry_date = $invoice_date =  date("Y-m-d") ; 
     $button = '<input type="submit" name="finalSubmit" id="finalSubmit" class="btn btn-primary" value="Submit">';
    
    }else{

                  $info = new Purchase();
            $data = $info->InvoiceDetails($this->id);

           $supplier_id = $data['supplier_id'] ;
           $invoice = $data['invoice_no'] ;
           $invoice_date = $data['invoice_date'] ;
           $expiry_date = '' ;

            $rate = $qty  = '';
     $button = '<input type="submit" name="finalSubmit" id="finalSubmit" class="btn btn-primary" value="Submit">';
    }




        $content =  '';


        $content .=  ' <div class="row">
        <div class="col-md-12" style="margin-bottom: 0px!important;"> 

        <form action="" id="kt_form" class="form-horizontal" method="post">
        <input type="hidden" name="csrf_token"  value="'.$_SESSION['csrf_token'].'">
        <input type="hidden" name="related_id" id="related_id" value="'.$this->id.'">
        <input type="hidden" name="user_id" id="user_id" value="'.$_SESSION['admin_access_token'].'">
        <input type="hidden" name="supplier_id" id="supplier_id" value="1">
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

               <div class="col-md-12">
                     <div class="form-group">
                  <label>Item Name</label>
<select id="productSearch" class="form-control" style="width: 100%;"></select>
                </div>
                  </div>
 
  <div class="col-md-6">
                    <div class="form-group">
                      <label for="rate">Purchase Rate</label>
                      <input required type="number" step=0.01 class="form-control" name="rate" id="rate" value="'.$rate.'" />
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                    <label for="qty">QTY</label>
                      <input required type="number" step=0.01 class="form-control" name="qty" id="qty"  value="'.$qty.'" autocomplete="off" />
                    </div>
                  </div>

    <div class="col-md-6">
                    <div class="form-group">
                    <label for="expiry_date">Expiry Date</label>
                      <input type="date" class="form-control" id="expiry_date" name="expiry_date"  value="'.$expiry_date.'">
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
                      <label for="invoice">Date</label>
                      <input type="date" class="form-control" id="invoice_date" name="invoice_date"  value="'.$invoice_date.'">

                    </div>
                  </div>
                  
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="invoice">Invoice</label>
                      <input type="text" class="form-control" id="invoice" name="invoice" placeholder="invoice no" value="'.$invoice.'">
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="sup_name">Supplier Name</label>
                      <input type="text" class="form-control" id="sup_name" name="sup_name" value="LINEAR" required>
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
    include("purchase_cart.php");
      $content .= ob_get_clean();
      $content .= '</div>


  </div>
   
      </div>
      </div>
      ';


print $content;


    }





}