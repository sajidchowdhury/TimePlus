<?php 

class AccountSetupPlate {

    private $id; 

    public function __construct($id = 'New')
    {
        $this->id = $id; 
    }

    public function SetupForm() { 
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    
                $fetch = new LedgerSetup();

        $csrf_token = isset($_SESSION['csrf_token']) ? htmlspecialchars($_SESSION['csrf_token']) : '';
    
         $account_name = $ledger_id =  '';
    
        if ($this->id !== 'New') {
            $data = $fetch->SingleAccountData($this->id);
    
            if ($data) { 
                $ledger_id = htmlspecialchars($data['ledger_id']);
                 $account_name = htmlspecialchars($data['account_name']);
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
                                        <label for="ledger_id">Ledger Name</label>
                                        <select name="ledger_id" id="ledger_id" required class="form-control select2" style="width: 100%;">
                                            <option value="">Select One</option> 
                                            <?php 
                                            foreach ($fetch->ListLedgerData() as $row) { ?>
                                               <option <?php if($ledger_id == $row['id'] ) { ?> selected="selected " <?php }else{}?> value="<?php print $row['id'] ;?> "><?php print $row['ledger_name'] ;?></option>';
                                         <?php    }
                                         ?>
                                        </select>
                                    </div>
                                </div>
                                
                                 <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="account_name">Account Name</label>
                                        <input required type="text" class="form-control" name="account_name" id="account_name" value="<?= $account_name ?>" placeholder="Enter Account Name">
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
                <?php include("list_account.php"); ?>
            </div>
        </div>
    </div>
        <?php
        $content = ob_get_clean();
        print $content;
    }
    

}
