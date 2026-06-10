<?php 
class TransactionContr extends Transaction {

    use SharedFunctionalityTrait;

    private $related_id; 
    private $page_name; 

    public function __construct($related_id, $page_name) {
        $this->related_id = $related_id;
        $this->page_name = $page_name;
    }

    public function DeleteFullInvoice() {


       if ( $this->checkEditPermission( ['delete'], $this->page_name ) !== true ) {
            return ["status" => "error", "message" => "You do not have permission to delete"];
        }

       return $this->DeleteInvoice($this->related_id);

    }
}
?>
