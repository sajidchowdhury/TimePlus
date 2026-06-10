<?php 
class Stock extends Dbh {

        use SharedFunctionalityTrait;



public function SingleProductStockByExpiry($product_id, $as_of_date = null) {
   
   $conn = $this->connect(); // Get DB connection

    $query = "
        SELECT expiry_date, SUM(quantity) AS stock_in
        FROM purchase_invoices_items
        WHERE product_id = :product_id
        GROUP BY expiry_date
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(":product_id", $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Now subtract sales from each expiry bucket
    $querySales = "
        SELECT sii.expiry_date, SUM(sii.quantity) AS stock_out
        FROM sales_invoices_items sii
        WHERE sii.product_id = :product_id
        GROUP BY sii.expiry_date
    ";

    $stmt = $conn->prepare($querySales);
    $stmt->bindValue(":product_id", $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $sales = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // expiry_date => quantity


// Fetch sales return
$queryReturns = "
    SELECT expiry_date, SUM(quantity) AS returned
    FROM sales_return_items
    WHERE product_id = :product_id
    GROUP BY expiry_date
";
$stmt = $conn->prepare($queryReturns);
$stmt->bindValue(":product_id", $product_id, PDO::PARAM_INT);
$stmt->execute();
$sales_return = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // expiry_date => returned quantity


    // Match expiry dates
    $result = [];
foreach ($purchases as $row) {
    $expiry = $row['expiry_date'];
    $in = (!empty($row['stock_in'])) ?  $row['stock_in'] : 0;
    $out = isset($sales[$expiry]) ? $sales[$expiry] : 0;
    $returned = isset($sales_return[$expiry]) ? $sales_return[$expiry] : 0;
    $available = $in - $out + $returned;

    if ($available > 0) {
        $result[] = [
            'expiry_date' => $expiry,
            'stock' => $available
        ];
    }
}


    return json_encode($result); // Return as JSON


}


public function SingleProductStock($product_id, $upto_date = null) {
    $conn = $this->connect();

    $conditions = '';
    if ($upto_date) {
        $conditions = " AND pi.invoice_date <= :upto_date";
    }

    $query = "
        SELECT 
            COALESCE(SUM(p.purchase_qty), 0) 
          - COALESCE(SUM(p.sales_qty), 0) 
          + COALESCE(SUM(p.return_qty), 0) AS stock
        FROM (
            -- Purchases
            SELECT 
                pii.product_id, 
                SUM(pii.quantity) AS purchase_qty, 
                0 AS sales_qty, 
                0 AS return_qty
            FROM purchase_invoices_items pii
            JOIN purchase_invoices pi ON pi.id = pii.invoice_id
            WHERE pii.product_id = :product_id $conditions
            GROUP BY pii.product_id

            UNION ALL

            -- Sales
            SELECT 
                sii.product_id, 
                0 AS purchase_qty, 
                SUM(sii.quantity) AS sales_qty, 
                0 AS return_qty
            FROM sales_invoices_items sii
            JOIN sales_invoices si ON si.id = sii.invoice_id
            WHERE sii.product_id = :product_id " . ($upto_date ? "AND si.invoice_date <= :upto_date" : "") . "
            GROUP BY sii.product_id

            UNION ALL

            -- Sales Returns
            SELECT 
                sri.product_id, 
                0 AS purchase_qty, 
                0 AS sales_qty, 
                SUM(sri.quantity) AS return_qty
            FROM sales_return_items sri
            JOIN sales_return sr ON sr.id = sri.invoice_id
            WHERE sri.product_id = :product_id " . ($upto_date ? "AND sr.invoice_date <= :upto_date" : "") . "
            GROUP BY sri.product_id
        ) p
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":product_id", $product_id, PDO::PARAM_INT);
    if ($upto_date) {
        $stmt->bindValue(":upto_date", $upto_date);
    }
    $stmt->execute();

    return (int) $stmt->fetchColumn();
}

    
    
   
    protected function TodayStockSummery() {
        $conn = $this->connect(); // Get DB connection


    $ReportName = "Stock Report :: till " . date('d-m-Y') ; 



    $content = '<div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">'.$ReportName.'</h3>
            </div><div class="card-body"><div class="table-responsive">';

        $query = " SELECT * from setup_product ";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all rows
        if (count($result) > 0) {
            $content .= "<table class='table table-bordered' id='example'>
                    <thead>
                        <tr>                            <th>id</th>

                            <th>Code</th>
                            <th>Group</th>
                            <th>Description</th>
                            <th>Size</th>
                            <th>Stock</th>
                            <th style='display:none'>Ex: Stock</th>
                        </tr>
                    </thead>
                    <tbody>";
$total_stock = 0 ;
            foreach ($result as $row) {
    $stock = $this->SingleProductStock($row['id']);
    $expiryData = json_decode($this->SingleProductStockByExpiry($row['id']), true);

    // Prepare expiry-wise stock display
    $expiryList = '';
    $Totalqty = 0 ;
    if (!empty($expiryData)) {
        foreach ($expiryData as $exp) {
            $expiry = htmlspecialchars($exp['expiry_date']);
            $qty = (int)$exp['stock'];
            $expiryList .= "<div>{$expiry}: <strong>{$qty}</strong></div>";

            $Totalqty +=$qty;
        }
    } else {
        $expiryList = "<span class='text-muted'>No stock</span>";
        $Totalqty += 0 ;
    }

if($stock == $Totalqty ){

 $print  = "STOCK OK";
}else{
    $print  = $expiryList ; 
}
    $content .= "<tr>
          <td>{$row['id']}</td>

        <td>{$row['code']}</td>
        <td>{$row['product_group']}</td>
        <td>{$row['product_description']}</td>
        <td>{$row['pack_size']}</td>
        <td>{$stock}</td>
        <td style='display:none'>{$print}</td>
    </tr>";

    $total_stock += $stock;
}

            $content .= "</tbody>
            <tfoot>
                <tr>
                    <td ></td><td ></td><td ></td><td ></td>
                    <td ><strong>Total</strong></td>
                    <td><strong>{$total_stock}</strong></td>
                    <td style='display:none' ></td>
                </tr>
            </tfoot>
        </table></div></div>";

 $content .= $this->LoadExportScript($ReportName) ; 



        } else {
            $content .= "<p>No product found.</p>";
        }

        return $content;
    }

}
