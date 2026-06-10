<?php 
class LedgerSetupContr extends LedgerSetup {

    use SharedFunctionalityTrait;

    private $ledger_name; 
    private $related_id; 
    private $account_name; 


    public function __construct($ledger_name ,$related_id, $account_name = null) {
        $this->ledger_name = $ledger_name;
        $this->related_id = $related_id;
        $this->account_name = $account_name ?? '';
    }

    public function LedgerAction() {
        // Validate inputs
        if (
            !$this->clean($this->ledger_name) ||
            !$this->clean($this->related_id)
        ) {
            return ["status" => "error", "message" => "Invalid input values"];
        }

        // New Customer Creation
        if ((string) $this->related_id === 'New') {
            return $this->CreateLedgerData($this->ledger_name);
        } 
        

      
     if ($this->checkEditPermission(['edit'], 'ledger_setup.php') !== true) {

            return ["status" => "error", "message" => 'Do not have permission to Edit'];
            exit(); // Stop further execution
            }



        // Update Existing Customer
        return $this->UpdateLedgerData($this->ledger_name, $this->related_id);
    }

    public function AccountAction() {
        // Validate inputs
        if (
            !$this->clean($this->ledger_name) ||
            !$this->clean($this->related_id) ||
            !$this->clean($this->account_name)
        ) {
            return ["status" => "error", "message" => "Invalid input values"];
        }

        // New Customer Creation
        if ((string) $this->related_id === 'New') {
            return $this->CreateAccountData($this->ledger_name, $this->account_name);
        } 
        

         if ($this->checkEditPermission(['edit'], 'account_setup.php') !== true) {

            return ["status" => "error", "message" => 'Do not have permission to Edit'];
            exit(); // Stop further execution
            }



        // Update Existing Customer
        return $this->UpdateAccountData($this->ledger_name, $this->account_name, $this->related_id);
    }
}
