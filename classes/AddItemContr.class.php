<?php 
class AddItemContr extends AddItem {

    use SharedFunctionalityTrait;

    private $code; 
    private $product_group; 
    private $product_description; 
    private $pack_size; 
    private $related_id; 
        private $sales_price; 
        private $product_orgin; 


    public function __construct($code, $product_group, $product_orgin  , $product_description, $pack_size, $sales_price, $related_id) {
        $this->code = $code;
        $this->product_group = $product_group;
        $this->product_description = $product_description;
        $this->pack_size = $pack_size;
        $this->related_id = $related_id;
        $this->sales_price = $sales_price;
        $this->product_orgin = $product_orgin;

    }

    public function Action() {
        // Validate inputs
        if (
            !$this->clean($this->code) ||
            !$this->clean($this->product_group) ||
            !$this->clean($this->product_description) ||
            !$this->clean($this->pack_size) ||
            !$this->clean($this->product_orgin) ||
            !$this->clean($this->related_id)
        ) {
            die(json_encode(["status" => "error", "message" => "Invalid input values"]));
        }

        // New Product Creation
        if ($this->related_id === 'New') {
            return $this->CreateData(
                $this->code, 
                $this->product_group, 
                $this->product_orgin, 
                $this->product_description, 
                $this->pack_size,
                $this->sales_price

            );
        } 




         if ($this->checkEditPermission(['edit'], 'add_item.php') !== true) {

            return ["status" => "error", "message" => 'Do not have permission to Edit'];
            exit(); // Stop further execution
            }


        
        // Update Existing Product
        return $this->UpdateData(
            $this->code, 
            $this->product_group, 
            $this->product_orgin, 
            $this->product_description, 
            $this->pack_size,
            $this->sales_price ,
            $this->related_id
        );
    }
}
