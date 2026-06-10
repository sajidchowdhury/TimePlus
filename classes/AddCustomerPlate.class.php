<?php 

class AddCustomerPlate {

    private $id; 

    public function __construct($id = 'New')
    {
        $this->id = $id; 
    }

    public function SetupForm() { 
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    
        $csrf_token = isset($_SESSION['csrf_token']) ? htmlspecialchars($_SESSION['csrf_token']) : '';
    
        $code = $customer_name = $customer_phone = $address = '';
    
        if ($this->id !== 'New') {
            $fetch = new AddCustomer();
            $data = $fetch->SingleData($this->id);
    
            if ($data) { 
                $customer_name = htmlspecialchars($data['customer_name']);
                $customer_phone = htmlspecialchars($data['customer_phone']);
                $address = htmlspecialchars($data['address']);
            }
        }
    
        ob_start(); 
        ?>
    
    <div class="row">
    <div class="col-md-12" style="margin-bottom: 0px!important;">
    <form id="myForm" class="login100-form validate-form" method="post">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="related_id" value="<?= htmlspecialchars($this->id) ?>">

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Customer Entry</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="customer_name">Customer Name</label>
                                        <input required type="text" class="form-control" name="customer_name" id="customer_name" value="<?= $customer_name ?>" placeholder="Enter customer name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="customer_phone">Customer Phone</label>
                                        <input required type="text" class="form-control" name="customer_phone" id="customer_phone" value="<?= $customer_phone ?>" placeholder="Enter customer phone">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <input required type="text" class="form-control" name="address" id="address" value="<?= $address ?>" placeholder="Enter address">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <input type="submit" name="kt_submit_button" id="kt_submit_button" class="btn btn-primary" value="Submit">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

    
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">List Of Customer</h3>
            </div>
            <div class="card-body" id="load_data">
                <?php include("list_customer.php"); ?>
            </div>
        </div>
    
        <?php
        $content = ob_get_clean();
        print $content;
    }
    

}
