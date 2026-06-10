<?php 
class UpdateMenuPermissionContr extends User {

    use SharedFunctionalityTrait;

    private $user_id; 
    private $menu_id;

    private $status;
    private $permission_type;

    public function __construct($user_id, $menu_id,$permission_type,$status) {



        $this->user_id = $user_id;
        $this->menu_id = $menu_id;
        $this->status = $status;
        $this->permission_type = $permission_type;

    }

    public function Action() {
       

       

            return $this->addUserPermission($this->user_id, $this->menu_id,$this->permission_type, $this->status);
      

    }
}
