<?php 

trait SharedFunctionalityTrait {



function secure_encode($data) {
    $secret_key = "whyevenIneed100%passwordTimePlus"; // keep it private
    $secret_iv = "2561";        // keep it private

    $encrypt_method = "AES-256-CBC";
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    $encrypted = openssl_encrypt(json_encode($data), $encrypt_method, $key, 0, $iv);
    return base64_encode($encrypted);
}

function secure_decode($string) {
    $secret_key = "whyevenIneed100%passwordTimePlus"; // keep it private
    $secret_iv = "2561";

    $encrypt_method = "AES-256-CBC";
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    $decrypted = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    return json_decode($decrypted, true);
}




  public function clean($field_one) {
  
    if ( addslashes(strip_tags(trim($field_one))) ) {
        $result = true; 
    }else{
        $result = false; 
    }
    return $result ; 
}


    public function emptyInputLogin($field_one) {
  
        if (empty($field_one) ) {
            $result = false; 
        }else{
            $result = true; 
        }
        return $result ; 
    }


    public function hasHtmlEntities($field_one) {
  
     
        if ( htmlspecialchars($field_one)) {
            $result = true; 
        }else{
            $result = false; 
        }
        return $result ; 
    }


    public function isValidPassword($field_one) {
        // Use a regular expression to check the password format
        $pattern = '/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        return preg_match($pattern, $field_one) === 1;
    }





    public function isMatched($field_one, $field_two) {
    
        if (
            ($field_one !== $field_two) 
            ) {
            $result = false ; 
        }else{
            $result = true; 
        }
        return $result ; 
    }



public function checkEditPermission($type, $link) {

    if (!isset($_SESSION)) {
        session_start();
    }

    // Ensure admin_access_token is set
    if (!isset($_SESSION['admin_access_token'])) {
        return false; // Or handle it as needed
    }

    $info = new User();
    $userPermissions = $info->getUserPermissions($_SESSION['admin_access_token'], $link);

    // Default to true, will set false if any required permission is missing
    $result = true;

    // Check each permission type in the array
    foreach ($type as $permission) {
        if ($permission === 'edit' && empty($userPermissions['can_edit'])) {
            $result = false;
        }
        if ($permission === 'delete' && empty($userPermissions['can_delete'])) {
            $result = false;
        }
        if ($permission === 'backdate' && empty($userPermissions['can_backdate'])) {
            $result = false;
        }
    }

    if($link == 'EditUSer.php' ){
   $result = true;
    }

    return $result;
}



        public function LoadExportScript($ReportName,$condition = 'true') {

    $content = <<<SCRIPT
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
  $(document).ready(function () {
    if ($.fn.DataTable.isDataTable('#example')) {
      $('#example').DataTable().destroy();
    }

    $('#example').DataTable({
      dom: 'Bfrtip',
        paging: $condition,
        searching: $condition,
        info: $condition,
        ordering: $condition,

      buttons: [
  {
    extend: 'copy',
    footer: true,
    title: "$ReportName"
  },
  {
    extend: 'excelHtml5',
    footer: true,
    title: "$ReportName"
  },
  {
    extend: 'pdfHtml5',
    footer: true,
    title: "$ReportName",
    customize: function (doc) {
      doc.styles.title = {
        color: 'red',
        fontSize: 15,
        alignment: 'center'
      };
    }
  },
  {
    extend: 'print',
    footer: true,
    title: '',
    customize: function (win) {
      $(win.document.body)
        .prepend('<h2 style="text-align:center;color:red;font-size:15pt;">$ReportName</h2>');
    },
    exportOptions: {
      columns: ':not(.no-export)',
      footer: true
    }
  }
],
      pageLength: 100
    });
  });
</script>
<style>
  tfoot {
    display: table-footer-group !important;
  }
</style>
SCRIPT;


    return $content;


    }




        public function logAction( $action, $details) {

        if (!isset($_SESSION)) {
        session_start();
        }

        // Ensure admin_access_token is set
        if (!isset($_SESSION['admin_access_token'])) {
        return false; // Or handle it as needed
        }
 

        $info = new ActiveLog();
        $result = $info->CreatLog($_SESSION['admin_access_token'] , $action, $details);
        return $result;

    }


 

    

}

