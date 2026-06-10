<?php



if ($_SERVER["REQUEST_METHOD"] === "POST") {



$user_id = $_POST['user_id'];
$menu_id = $_POST['menu_id'];
$permission_type = $_POST['permission_type'];
$status = $_POST['status'];



    if (!empty($user_id) && !empty($menu_id)) {

        include_once "autoloader.inc.php";
 
        $action = new UpdateMenuPermissionContr($user_id, $menu_id,$permission_type,$status);
        $result = $action->Action(); 
    
        echo json_encode(["status" => $result['status'], "message" => $result['message']]);
        exit();
    
    } else {
        echo json_encode(["status" => "error", "message" => "Missing parameters."]);
    }
}
?>
