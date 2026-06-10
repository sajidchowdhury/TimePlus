<?php 

class AddItemPlate {

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
    
        $code = $product_group = $product_description = $pack_size = $sales_price = $product_orgin = '' ;


            $fetch = new AddItem();
            $jsondata = $fetch->XMLtemList();
    
        if ($this->id !== 'New') {


            $data = $fetch->SingleData($this->id);
    
            if ($data) { 
                $code = htmlspecialchars($data['code']);
                $product_group = htmlspecialchars($data['product_group']);
                $product_description = htmlspecialchars($data['product_description']);
                $pack_size = htmlspecialchars($data['pack_size']);
                 $sales_price = htmlspecialchars($data['price']);
                 $product_orgin = htmlspecialchars($data['product_orgin']);

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
                                    <h3 class="card-title">Product Entry</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="code">Code</label>
                                                <input required type="text" class="form-control" name="code" id="code" value="<?= $code ?>" placeholder="Enter code">
                                            </div>
                                        </div>

                                         <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="product_orgin">Product Orgin</label>
                                                <input required type="text" class="form-control" name="product_orgin" id="product_orgin" value="<?= $product_orgin ?>" placeholder="Enter product orgin">
                                            </div>
                                        </div>


                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="product_group">Product Group</label>
                                                <input required type="text" class="form-control" name="product_group" id="product_group" value="<?= $product_group ?>" placeholder="Enter product group">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="product_description">Product Description</label>
                                                <input required type="text" class="form-control" name="product_description" id="product_description" value="<?= $product_description ?>" placeholder="Enter product description">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="pack_size">Pack Size</label>
                                                <input required type="text" class="form-control" name="pack_size" id="pack_size" value="<?= $pack_size ?>" placeholder="Enter pack size">
                                            </div>
                                        </div>
                                           <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="sales_price">Sale Price</label>
                                                <input required type="number" class="form-control" name="sales_price" id="sales_price" value="<?= $sales_price ?>" placeholder="Enter sales price">
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
                <h3 class="card-title">List Of Products</h3>
            </div>
            <div class="card-body" id="load_data">
                <?php include("list_product.php"); ?>
            </div>
        </div>
    
        <?php
        $content = ob_get_clean();
        print $content;
    }
    

}
