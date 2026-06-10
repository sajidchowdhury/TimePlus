<?php 
class BankSetupContr extends BankSetup {

    use SharedFunctionalityTrait;

    private $bank_name; 
    private $related_id; 
    private $account_no; 


    public function __construct($bank_name, $account_no, $related_id) {
        $this->bank_name = $bank_name;
        $this->related_id = $related_id;
        $this->account_no = $account_no ?? '';
    }

    public function Action() {
        // Validate inputs
        if (
            !$this->clean($this->account_no) ||
            !$this->clean($this->related_id)
        ) {
            return ["status" => "error", "message" => "Invalid input values"];
        }

        // New Customer Creation
        if ((string) $this->related_id === 'New') {
            return $this->CreateBankData($this->bank_name, $this->account_no );
        } 
        


     if ($this->checkEditPermission(['edit'], 'bank_setup.php') !== true) {

            return ["status" => "error", "message" => 'Do not have permission to Edit'];
            exit(); // Stop further execution
            }



        // Update Existing Customer
        return $this->UpdateBankData($this->bank_name, $this->account_no , $this->related_id);
    }

}
