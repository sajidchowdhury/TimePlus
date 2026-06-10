<?php 



class ProductSetup extends Dbh {




protected  function CreateProduct($product_name, $unit,$productionable,$saftystock) {


    


  $stmt = $this->connect()->prepare("INSERT INTO `setup_product` 
  (
    `product_name`, `unit`, `productionable`, `saftystock`, `created_date`,  `poster`
    
    )
  VALUES
  (
      :product_name, 
      :unit, 
      :productionable,
      :saftystock,
      :created_date, 
      :poster

  )");


  $created_date = date("Y-m-d") . ' ' . date("h:i:s a");  
  if (!isset($_SESSION)) {
    session_start();
}
  $poster = $_SESSION['admin_access_token']; 

  $stmt->bindParam(':product_name', $product_name);
  $stmt->bindParam(':unit', $unit);
  $stmt->bindParam(':productionable', $productionable);
  $stmt->bindParam(':saftystock', $saftystock);
  $stmt->bindParam(':created_date', $created_date);
  $stmt->bindParam(':poster', $poster);



  if( !$stmt->execute()){
    $mess =  18;
  }else{
    $mess =  30;
  }


  return  array(
    'mess' => $mess
);

  $stmt = null;


}



protected function DuplicateProductCheck($product_name) {

    if (!isset($_SESSION)) {
        session_start();
    }

    $stmt = $this->connect()->prepare('SELECT id FROM setup_product WHERE product_name = :product_name ');
    $stmt->bindParam(':product_name', $product_name);


    if (!$stmt->execute()) {
        $mess =  18;
    }else{
        $rowCount = $stmt->rowCount();
        if( $rowCount > 0 ){

            return  false ;

        }else{
            return   true;
        }           
    }

   $stmt = null;

}


protected  function UpdateProduct($product_name, $unit,$productionable,$saftystock,$related_id) {

   

    

            $stmt2 = $this->connect()->prepare("UPDATE setup_product SET  product_name = ? , unit = ? , productionable = ?  , saftystock = ?  , update_date = ? , poster = ?   WHERE id = ?");

            $update_date = date("Y-m-d") . ' ' . date("h:i:s a");  
            if (!isset($_SESSION)) {
                session_start();
            }
            $poster = $_SESSION['admin_access_token']; 
        
            $stmt2->execute([$product_name , $unit  ,  $productionable , $saftystock,  $update_date  , $poster,  $related_id ]);

            if (!$stmt2) {
                $mess =  18;
            }else{
                $mess =  31;
            }
           
            return  array(
                'mess' => $mess
            );
            
              $stmt = null;
            


  }
  


public function DeleteProduct($id){


    if (!$this->clean($id) || !$this->emptyInputLogin($id) || !$this->hasHtmlEntities($id))
     { 
        return 'mess1' ; 
        exit();
     }


     $deleteQuery = $this->connect()->prepare("DELETE FROM setup_product WHERE id = ? ");
     $deleteQuery->execute([$id, $id]);
     if ($deleteQuery){
        return 'mess17';
     }else{
        return 'mess2';
     }

}


public function ProductList(){


    $stmt = $this->connect()->prepare('SELECT * FROM setup_product  ');
    $stmt->execute();
    $Data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = null;
    return  $Data;
   
}

public function AVGPrice($id){


    $stmt = $this->connect()->prepare('SELECT 
    ROUND(AVG(price), 2) AS avg_price 
FROM 
    purchase_item 
WHERE 
    product_id = :id AND created_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY 
    product_id;
 ');
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $Data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = null;
    return  $Data;
   
}


public function LastSalesPrice($id){


    $stmt = $this->connect()->prepare('SELECT sales_price
FROM 
    purchase_item 
WHERE 
    product_id = :id order by  created_date DESC LIMIT 1 
 ');
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $Data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = null;
    return  $Data;
   
}



public function SingleProduct($id){


    $stmt = $this->connect()->prepare('SELECT * FROM setup_product WHERE id = :id ' );
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $profileData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    
    return $profileData;
}




public function ProductStockReport($related_id,$report_type,$date_from,$date_to){


    $report = '';

if($report_type == 'All' ){

    $QUERY = " " ;

}else if ($report_type == 'Product-Wise' ){

    $QUERY = " where A.`id` = '".$related_id."' " ;

}else{
    
    $QUERY = "";
}

$content = '<div class="row">
    <div class="col-md-12">
    
        <div class="panel panel-default">
            <div class="panel-body">
           <div class="table-responsive">
        <table class="table table-hover table-condensed table-striped table-bordered datatable"  id="MSalary">
           ';

    $content .='<thead><tr>
    <th>Sl	</th>
    <th>Product Name	</th>
    <th>Unit </th>
    <th>Current Stock</th>
    </tr></thead>';



   

$sl = 1;
$stmt = $this->connect()->prepare(" SELECT A.* FROM setup_product A $QUERY  " );
$stmt->execute();
$profileData = $stmt->fetchAll(PDO::FETCH_ASSOC);


        foreach($profileData AS $fetch1){ 

            $get_stock = self::ProductStock($fetch1['id']);
            $stock = (!empty($get_stock)) ? $get_stock : 0;

    
            $content .= '<tr>';
            $content .= '<td>'.$sl++.'</td>';
            $content .= '<td>'.$fetch1['product_name'].'</td>';
            $content .= '<td>'.$fetch1['unit'].'</td>';
            $content .= '<td>'.number_format((float)$stock, 2, '.', '').'</td>';
            $content .= '</tr>';
    
        }
    
 

    $content .= '
    </table>
    </div>
    </div>      
  </div>

</div>
</div>';



return    $content ; 


}



public function ProductSafetyStockReport($related_id,$report_type,$date_from,$date_to){


    $report = '';

if($report_type == 'All' ){

    $QUERY = " " ;

}else if ($report_type == 'Product-Wise' ){

    $QUERY = " where A.`id` = '".$related_id."' " ;

}else{
    
    $QUERY = "";
}

$content = '<div class="row">
    <div class="col-md-12">
    
        <div class="panel panel-default">
            <div class="panel-body">
           <div class="table-responsive">
        <table class="table table-hover table-condensed table-striped table-bordered datatable"  id="MSalary">
           ';

    $content .='<thead><tr>
    <th>Sl	</th>
    <th>Product Name	</th>
    <th>Unit </th>
    <th>Safety Stock </th>
    <th>Current Stock</th>
    </tr></thead>';



   

$sl = 1;
$stmt = $this->connect()->prepare(" SELECT A.* FROM setup_product A $QUERY  " );
$stmt->execute();
$profileData = $stmt->fetchAll(PDO::FETCH_ASSOC);


        foreach($profileData AS $fetch1){ 

            $get_stock = self::ProductStock($fetch1['id']);
            $stock = (!empty($get_stock)) ? $get_stock : 0;

             if( $stock <= $fetch1['saftystock']){

                $content .= '<tr>';
                $content .= '<td>'.$sl++.'</td>';
                $content .= '<td>'.$fetch1['product_name'].'</td>';
                $content .= '<td>'.$fetch1['unit'].'</td>';
                $content .= '<td>'.$fetch1['saftystock'].'</td>';
                $content .= '<td>'.number_format((float)$stock, 2, '.', '').'</td>';
                $content .= '</tr>';


             }
         
    
        }
    
 

    $content .= '
    </table>
    </div>
    </div>      
  </div>

</div>
</div>';



return    $content ; 


}


public function ProductStock($id){


    $stmt = $this->connect()->prepare('
    SELECT 
    product_id,
     (
     ( SUM(total_purchased) + SUM(total_stock_in)) -
     ( SUM(total_sold)  + SUM(total_stock_out))
     )
     AS stock

FROM (
    SELECT 
        product_id,
        COALESCE(SUM(quantity), 0) AS total_purchased,
        0 AS total_sold,
        0 as total_stock_in,
        0 AS total_stock_out
    FROM 
        purchase_item
    WHERE 
        product_id = :id and status = "Done"
    GROUP BY 
        product_id
    UNION ALL
    SELECT 
        product_id,
        0 AS total_purchased,
        COALESCE(SUM(quantity), 0) AS total_sold,
        0 as total_stock_in,
        0 AS total_stock_out

    FROM 
        sales_item
    WHERE 
        product_id =  :id  and status = "Done"
    GROUP BY 
        product_id
    UNION ALL
    SELECT 
        product_id,
        0 AS total_purchased,
        0 AS total_sold,
        COALESCE(SUM(stock_in_quantity), 0) AS total_stock_in,
        0 AS total_stock_out
    FROM 
        stock_adjustment_item
    WHERE 
        product_id = :id  and status = "Done"
    GROUP BY 
        product_id
    UNION ALL
    SELECT 
    product_id,
    0 AS total_purchased,
    0 AS total_sold,
    0 as total_stock_in,
    COALESCE(SUM(stock_out_quantity), 0) AS total_stock_out
FROM 
    stock_adjustment_item
WHERE 
    product_id = :id  and status = "Done"
GROUP BY 
    product_id

) AS combined_results
GROUP BY 
    product_id
    ' );
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $profileData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stock = (!empty($profileData[0]['stock'])) ? $profileData[0]['stock'] : 0;
    return $stock;
    
}







} // end of class