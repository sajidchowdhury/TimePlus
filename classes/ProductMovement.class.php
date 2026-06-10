<?php 

class ProductMovement extends Dbh {

    public function getMovement($productId, $date1, $date2) {
        $conn = $this->connect();

       // Convert dates
$date1 = DateTime::createFromFormat('d/m/Y', $date1)->format('Y-m-d');
$date2 = DateTime::createFromFormat('d/m/Y', $date2)->format('Y-m-d');

// Get previous date
$previousDay = date('Y-m-d', strtotime($date1 . ' -1 day'));

$stock = New Stock();
$openingBalance = $stock->SingleProductStock($productId, $previousDay);

// Start movement with Previous Balance
$movement = [[
    'serial' => 0,
    'date' => $previousDay,
    'time' => '00:00:00',
    'poster' => 'System',
    'qty' => $openingBalance,
    'description' => 'Previous Balance',
    'type' => 'IN',
    'link' => 'No Link',
    'balance' => $openingBalance // Initial
]];
$serial = 0; // will increment from 1 for actual movements



        // === 1. PURCHASE (IN) ===
        $stmt = $conn->prepare("
            SELECT A.invoice_no,A.invoice_date, D.employee_name, B.quantity, C.supplier_name
            FROM purchase_invoices A
            JOIN purchase_invoices_items B ON A.id = B.invoice_id
            JOIN setup_suppliers C ON A.supplier_id = C.id
            JOIN admin D ON A.poster = D.id

            WHERE B.product_id = ? AND A.invoice_date BETWEEN ? AND ?
        ");
        $stmt->execute([$productId, $date1, $date2]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $movement[] = [
                'serial' => ++$serial,
                'date' => $row['invoice_date'],
                'time' => date('H:i:s', strtotime($row['invoice_date'])),
                'poster' => $row['employee_name'],
                'qty' => $row['quantity'],
                'description' => "Purchase >> " . $row['supplier_name'] . "<br>Inv: " . $row['invoice_no'],
                'type' => 'IN',
                'link' => 'No Link'
            ];
        }

        // === 2. SALES (OUT) ===
        $stmt = $conn->prepare("
            SELECT A.invoice_no,A.invoice_date, E.employee_name, B.quantity, D.customer_name
            FROM sales_invoices A
            JOIN sales_invoices_items B ON A.id = B.invoice_id
            JOIN setup_customer D ON A.customer_id = D.id
            JOIN admin E ON A.poster = E.id

            WHERE B.product_id = ? AND A.invoice_date BETWEEN ? AND ?
        ");
        $stmt->execute([$productId, $date1, $date2]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $movement[] = [
                'serial' => ++$serial,
                'date' => $row['invoice_date'],
                'time' => date('H:i:s', strtotime($row['invoice_date'])),
                'poster' => $row['employee_name'],
                'qty' => $row['quantity'],
                'description' => "Sales >> " . $row['customer_name']. "<br>Inv:  " . $row['invoice_no'],
                'type' => 'OUT',
                'link' => 'No Link'
            ];
        }

        // === 3. SALES RETURN (IN) ===
        $stmt = $conn->prepare("
             SELECT A.invoice_no,A.invoice_date ,E.employee_name, B.quantity, D.customer_name
            FROM sales_return A
            JOIN sales_return_items B ON A.id = B.invoice_id
            JOIN setup_customer D ON A.customer_id = D.id
            LEFT JOIN admin E ON A.poster = E.id

            WHERE B.product_id = ? AND A.invoice_date BETWEEN ? AND ?
        ");
        $stmt->execute([$productId, $date1, $date2]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $movement[] = [
                'serial' => ++$serial,
                'date' => $row['invoice_date'],
                'time' => date('H:i:s', strtotime($row['invoice_date'])),
                'poster' => $row['employee_name'],
                'qty' => $row['quantity'],
                'description' => "Sales Return >> " . $row['customer_name']. "<br>Inv:  " . $row['invoice_no'],
                'type' => 'IN',
                'link' => 'No Link'
            ];
        }

        // === Sort by date + time ===
        usort($movement, function($a, $b) {
            return strtotime($a['date'] . ' ' . $a['time']) <=> strtotime($b['date'] . ' ' . $b['time']);
        });

// === Calculate running balance ===
$balance = $movement[0]['balance']; // Start from previous balance
foreach ($movement as $i => &$row) {
    if ($i === 0) continue; // skip previous balance row
    if ($row['type'] === 'IN') {
        $balance += $row['qty'];
    } else {
        $balance -= $row['qty'];
    }
    $row['balance'] = $balance;
}

        return $this->renderReport($movement, $date1, $date2);
    }

    public function renderReport($data, $from, $to) {
        $html = '<div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Product Movement Report :: From '.date('d-m-Y', strtotime($from)).' To '.date('d-m-Y', strtotime($to)).'</h3>
            </div><div class="card-body"><div class="table-responsive">
            <table class="table table-bordered table-striped"><thead>
            <tr>
                <th>Date</th><th>User</th><th>Description</th><th>Quantity</th><th>Type</th><th>Balance</th>
            </tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= "<tr>
                <td>{$row['date']}</td>
                <td>{$row['poster']}</td>
                <td>{$row['description']}</td>
                                <td>{$row['qty']}</td>

                <td>{$row['type']}</td>
                <td>{$row['balance']}</td>
            </tr>";
        }

        $html .= '</tbody></table></div></div></div>';
        return $html;
    }
}
