<?php 
class AddCustomerContr extends AddCustomer {

    use SharedFunctionalityTrait;

    private $customer_name; 
    private $customer_phone; 
    private $address; 
    private $related_id; 

    public function __construct($customer_name, $customer_phone, $address, $related_id) {
        $this->customer_name = $customer_name;
        $this->customer_phone = $customer_phone;
        $this->address = $address;
        $this->related_id = $related_id;
    }

    public function Action() {


        // Validate inputs
        if (
            !$this->clean($this->customer_name) ||
            !$this->clean($this->customer_phone) ||
            !$this->clean($this->address) ||
            !$this->clean($this->related_id)
        ) {
            die(json_encode(["status" => "error", "message" => "Invalid input values"]));
        }

        // New Customer Creation
        if ($this->related_id === 'New') {


            return $this->CreateData(
                $this->customer_name, 
                $this->customer_phone, 
                $this->address
            );
        } 


         if ($this->checkEditPermission(['edit'], 'add_customer.php') !== true) {

            return ["status" => "error", "message" => 'Do not have permission to Edit'];
            exit(); // Stop further execution
            }



        
        // Update Existing Customer
        return $this->UpdateData(
            $this->customer_name, 
            $this->customer_phone, 
            $this->address, 
            $this->related_id
        );
    }
}
