<?php 
class CreateUserContr extends User {

    use SharedFunctionalityTrait;

    private $employee_name; 
    private $email; 
    private $related_id; 
    private $mobile; 
    private $user_type; 
    private $block_user; 
    private $password; 
    private $PageName; 
    private $login_start; 
    private $login_end; 


    public function __construct($employee_name, $email,$related_id, $mobile,$user_type,$block_user,$password,$PageName = 'Personal_Profile' ,$login_start = '09:00:00' ,$login_end = '18:00:00' ) {
        $this->employee_name = $employee_name;
        $this->email = $email;
        $this->related_id = $related_id;
        $this->mobile = $mobile;
        $this->user_type = $user_type;
        $this->block_user = $block_user;
        $this->password = $password;
        $this->PageName = $PageName;
        $this->login_start = $login_start;
        $this->login_end = $login_end;

    }

    public function Action() {
        // Validate inputs
        if (
            !$this->clean($this->employee_name) ||
            !$this->clean($this->email) ||
            !$this->clean($this->related_id) ||
            !$this->clean($this->mobile) ||
            !$this->clean($this->user_type) ||
            !$this->clean($this->password) ||
            !$this->clean($this->block_user)
        ) {
            die(json_encode(["status" => "error", "message" => "Invalid input values"]));
        }

        // New User Creation
        if ($this->related_id === 'New') {




            return $this->CreateData(
                $this->employee_name, 
                $this->email, 
                $this->mobile, 
                $this->user_type,
                $this->password, 
                $this->block_user,
                $this->login_start, 
                $this->login_end
            );
        } 
        
       
      
      if($this->PageName == 'All_Profile' ){

       if ( $this->checkEditPermission(['edit'], 'user_create.php') !== true  ) {

        return ["status" => "error", "message" => 'Do not have permission to Edit'];
        exit(); // Stop further execution
        }

        
      }
 


        // Update Existing User
        return $this->UpdateData(
            $this->employee_name, 
            $this->email, 
            $this->related_id, 
            $this->mobile, 
            $this->user_type, 
            $this->password, 
            $this->block_user,
            $this->login_start, 
            $this->login_end
        );
    }
}
