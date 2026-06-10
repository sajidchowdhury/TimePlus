<?php 

class BankSetupPlate {

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
    
         $bank_name = $account_no = '';
    
        if ($this->id !== 'New') {
            $fetch = new BankSetup();
            $data = $fetch->SingleBankData($this->id);
    
            if ($data) { 
                $bank_name = htmlspecialchars($data['bank_name']);
                                $account_no = htmlspecialchars($data['account_no']);

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
                            <h3 class="card-title">Bank Entry </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bank_name">Bank Name</label>
                                        <input required type="text" class="form-control" name="bank_name" id="bank_name" value="<?= $bank_name ?>" placeholder="Enter bank Name">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="account_no">Account No</label>
                                        <input required type="text" class="form-control" name="account_no" id="account_no" value="<?= $account_no ?>" placeholder="Enter account no">
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

        <div class="col-md-12" style="margin-bottom: 0px!important;">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">List Of Ledger</h3>
            </div>
            <div class="card-body" id="load_data">
                <?php include("list_bank.php"); ?>
            </div>
        </div>
    </div>
        <?php
        $content = ob_get_clean();
        print $content;
    }
    

}
