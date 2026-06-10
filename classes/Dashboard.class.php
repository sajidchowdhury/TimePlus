<?php 



class Dashboard extends Dbh {

    public function getSalesData() {



    try {
        $db = $this->connect();




        // Fetch this week's sales
        $stmt = $db->prepare("
            SELECT DATE(invoice_date) as sale_date, SUM(total_amount) as total_sales 
            FROM sales_invoices 
            WHERE invoice_date >= CURDATE() - INTERVAL 7 DAY 
            GROUP BY sale_date
            ORDER BY sale_date ASC
        ");
        $stmt->execute();
        $thisWeekData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch last week's sales
        $stmt = $db->prepare("
            SELECT DATE(invoice_date) as sale_date, SUM(total_amount) as total_sales 
            FROM sales_invoices 
            WHERE invoice_date >= CURDATE() - INTERVAL 14 DAY 
            AND invoice_date < CURDATE() - INTERVAL 7 DAY
            GROUP BY sale_date
            ORDER BY sale_date ASC
        ");
        $stmt->execute();
        $lastWeekData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare data for response
        $labels = [];
        $thisWeekSales = [];
        $lastWeekSales = [];
        $percentageChange = [];

        // Convert last week's sales data into an associative array for easy lookup
        $lastWeekSalesMap = [];
        foreach ($lastWeekData as $row) {
            $dateKey = date("jS", strtotime($row['sale_date']));
            $lastWeekSalesMap[$dateKey] = (float)$row['total_sales'];
        }

        // Process this week's sales and calculate percentage change
        $totalThisWeek = 0;
        $totalLastWeek = 0;

        foreach ($thisWeekData as $row) {
            $dateKey = date("jS", strtotime($row['sale_date']));
            $labels[] = $dateKey;
            $thisWeekSales[] = (float)$row['total_sales'];

            // Get last week's sales for the same day (default to 0 if missing)
            $lastWeekSalesValue = $lastWeekSalesMap[$dateKey] ?? 0;
            $lastWeekSales[] = $lastWeekSalesValue;

            // Update total sales for percentage calculation
            $totalThisWeek += $row['total_sales'];
            $totalLastWeek += $lastWeekSalesValue;

            // Calculate percentage change
            if ($lastWeekSalesValue > 0) {
                $change = (($row['total_sales'] - $lastWeekSalesValue) / $lastWeekSalesValue) * 100;
            } else {
                $change = ($row['total_sales'] > 0) ? 100 : 0;
            }
            $percentageChange[] = round($change, 2);
        }

        // Calculate overall percentage change
        $overallPercentageChange = $totalLastWeek > 0 
            ? round((($totalThisWeek - $totalLastWeek) / $totalLastWeek) * 100, 2)
            : ($totalThisWeek > 0 ? 100 : 0);

        return [
            "labels" => $labels,
            "this_week_sales" => $thisWeekSales,
            "last_week_sales" => $lastWeekSales,
            "percentage_change" => $percentageChange,
            "overall_change" => $overallPercentageChange
        ];
    } catch (Exception $e) {
        return ["error" => "Database Error: " . $e->getMessage()];
    }
}

    public function getTodayTransactionSummary() {
    try {
        
        date_default_timezone_set('Asia/Dhaka');
        $date = date("Y-m-d");



        $stmt = $this->connect()->prepare("
            SELECT 
                SUM(CASE WHEN transaction_type = 'credit' AND entity_type = 'customer' THEN amount ELSE 0 END) AS customer_receive,
                SUM(CASE WHEN transaction_type = 'debit' AND entity_type = 'supplier' THEN amount ELSE 0 END) AS supplier_payment,
                SUM(CASE WHEN transaction_type = 'credit' AND entity_type NOT IN ('customer', 'supplier') THEN amount ELSE 0 END) AS other_receive,
                SUM(CASE WHEN transaction_type = 'debit' AND entity_type NOT IN ('customer', 'supplier') THEN amount ELSE 0 END) AS other_expense
            FROM account_transaction
            WHERE transaction_date = '".$date."'
        ");

        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            "customer_receive" => (float)$data['customer_receive'],
            "supplier_payment" => (float)$data['supplier_payment'],
            "income_voucher" => (float)$data['other_receive'],
            "expense_voucher" => (float)$data['other_expense']
        ];
    } catch (Exception $e) {
        return ["error" => "Database Error: " . $e->getMessage()];
    }
}
  

}
