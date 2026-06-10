<?php 


class ReportStockPlate {


  private $mess ; 
  public function __construct($mess = 'New')
  {
    $this->mess = $mess ; 
  }


  public function SetupForm(){ 
    


        $content =  '';


        $content .=  ' <div class="row">
          <!-- left column -->
          <div class="col-md-12">
            <!-- general form elements -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Entry Table</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <form role="form"  method="get">

                <div class="card-body">
                <div class="row">

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="exampleInputEmail1">Report Type</label>
                                        <select name="report_type" id="report_type" required class="form-control select2" onchange="ReportWiseData(this.value);" style="width: 100%;">
                          <option value="">-- Select -- </option>
    
                           <option data-custom="Need-No-Date" value="Summery" > Summery  </option>



                      </select>
                    </div>
                  </div>

                  
                        
                      <div class="col-md-6" id="load-report-content"></div>
                      <div class="col-md-6" id="load-date-content"></div>
                      <input type="hidden" id="report_name" name="report_name" value="Stock Report">


                  
                  


                  
                 
                  
                </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
        <button type="button" id="searchReport" class="btn btn-primary">Search</button>
                </div>
              </form>
            </div>
            <!-- /.card -->


          </div>
          <!--/.col (left) -->
        </div>
        <!-- /.row -->

       
            <div class="row"><div class="col-md-12"><div id="load_data"></div></div></div>
            <!-- /.card-body -->
       
      ';


print $content;


    }





}