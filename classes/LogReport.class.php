<?php 
class LogReport extends Dbh {



   protected function AllUser($start,$end) {
    $conn = $this->connect(); // Get DB connection

    $content = '<div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Log from :: '.date('d-m-Y', strtotime($start)).' to '.date('d-m-Y', strtotime($end)).' </h3>
            </div><div class="card-body"><div class="table-responsive">';



$start = DateTime::createFromFormat('d/m/Y', $start)->format('Y-m-d');
$end = DateTime::createFromFormat('d/m/Y', $end)->format('Y-m-d');



    $query = "
       SELECT A.*,B.employee_name FROM server_log A JOIN admin B ON (A.user_id=B.id) WHERE A.log_date BETWEEN :start AND :end ORDER BY A.log_date DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":start", $start, PDO::PARAM_STR);
    $stmt->bindValue(":end", $end, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 


        $content .= "<table class='table table-bordered' id='example1'>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User Name</th>
                         <th>Activity </th>
                        <th>Details</th>

                    </tr>
                </thead>
                <tbody>";

        foreach ($result as $row) { 
        

           $content .= "<tr>
            <td>". $row['log_date'] . "</td>
                        <td>". $row['employee_name'] . "</td>
                        <td>". $row['action_type'] . "</td>
                        <td>". $row['details'] . "</td>
        </tr>";

         }

        // Add tfoot section for total due
        $content .= "</tbody>
               
            </table></div></div>";


    return $content;
}

protected function SingleUser($userid,$start,$end) {
    $conn = $this->connect(); // Get DB connection

    $content = '<div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Log from :: '.date('d-m-Y', strtotime($start)).' to '.date('d-m-Y', strtotime($end)).' </h3>
            </div><div class="card-body"><div class="table-responsive">';



$start = DateTime::createFromFormat('d/m/Y', $start)->format('Y-m-d');
$end = DateTime::createFromFormat('d/m/Y', $end)->format('Y-m-d');



    $query = "
       SELECT A.*,B.employee_name FROM server_log A JOIN admin B ON (A.user_id=B.id) WHERE A.user_id = :userid AND A.log_date BETWEEN :start AND :end ORDER BY A.log_date DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(":start", $start, PDO::PARAM_STR);
    $stmt->bindValue(":end", $end, PDO::PARAM_STR);
    $stmt->bindValue(":userid", $userid, PDO::PARAM_STR);

    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 


        $content .= "<table class='table table-bordered' id='example1'>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User Name</th>
                         <th>Activity </th>
                        <th>Details</th>

                    </tr>
                </thead>
                <tbody>";

        foreach ($result as $row) { 
        
        $details = json_decode($row['details'], true); // Decode JSON to an array

           $content .= "<tr>
            <td>". $row['log_date'] . "</td>
                        <td>". $row['employee_name'] . "</td>
                        <td>". $details['action'] . "</td>
                        <td>". $details['details'] . "</td>
        </tr>";

         }

        // Add tfoot section for total due
        $content .= "</tbody>
          
            </table></div></div>";
 

    return $content;
}



}
