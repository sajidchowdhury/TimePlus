<?php 

class CreateUserPlate {

    private $id; 

    public function __construct($id = 'New')
    {
        $this->id = $id; 
    }

    public function SetupForm() { 
        // Ensure session is started before accessing $_SESSION['csrf_token']
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Ensure CSRF token exists
        $csrf_token = isset($_SESSION['csrf_token']) ? htmlspecialchars($_SESSION['csrf_token']) : '';

        // Default blank values
        $employee_name = $email = $mobile = $password = '';
        $login_end = '21:00:00';
        $login_start = '09:00:00';
        $user_type = 'User';
        $block_user = 'No';

        // If not "New", fetch user data
        if ($this->id !== 'New') {
            $fetch = new User();
            $data = $fetch->SingleData($this->id);

            if ($data) { 
                $employee_name = htmlspecialchars($data['employee_name']);
                $email = htmlspecialchars($data['email']);
                $mobile = htmlspecialchars($data['employee_number']);
                $password = htmlspecialchars($data['plainPassword']); 
                $user_type = htmlspecialchars($data['user_type']);
                $block_user = htmlspecialchars($data['block_user']);
                $login_end = htmlspecialchars($data['login_end']);
                $login_start =htmlspecialchars($data['login_start']);
                }
        }

        ob_start(); // Start output buffering
        ?>

        <div class="row">
            <div class="col-md-12" style="margin-bottom: 0px!important;">
                <form id="myForm" class="login100-form validate-form" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="related_id" value="<?= htmlspecialchars($this->id) ?>">
                    <input type="hidden" name="PageName" value="All_Profile">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Entry Table</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="employee_name">Employee Name</label>
                                                <input required type="text" class="form-control" name="employee_name" id="employee_name" value="<?= $employee_name ?>" placeholder="Enter employee name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email">Email</label>
                                                <input required type="email" class="form-control" name="email" id="email" value="<?= $email ?>" placeholder="Enter email">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="mobile">Mobile</label>
                                                <input required type="text" class="form-control" name="mobile" id="mobile" value="<?= $mobile ?>" pattern="^[0-9]{10,15}$" title="Enter a valid mobile number" placeholder="Enter mobile number">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="password">Password</label>
                                                <input required type="text" class="form-control" name="password" id="password" value="<?= $password ?>" title="Enter password" placeholder="Enter password">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="user_type">User Type</label>
                                                <select required class="form-control" name="user_type" id="user_type">
                                                    <option value="User" <?= ($user_type == 'User') ? 'selected' : '' ?>>User</option>
                                                    <option value="Admin" <?= ($user_type == 'Admin') ? 'selected' : '' ?>>Admin</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="block_user">Block</label>
                                                <select required class="form-control" name="block_user" id="block_user">
                                                    <option value="No" <?= ($block_user == 'No') ? 'selected' : '' ?>>No</option>
                                                    <option value="Yes" <?= ($block_user == 'Yes') ? 'selected' : '' ?>>Yes</option>
                                                </select>
                                            </div>
                                        </div>
 <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="login_start">Office In-Time</label>
                                                <input required type="time" class="form-control" name="login_start" id="login_start" value="<?= $login_start ?>" >
                                            </div>
                                        </div>

                                         <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="login_end">Office Out-Time</label>
                                                <input required type="time" class="form-control" name="login_end" id="login_end" value="<?= $login_end ?>" >
                                            </div>
                                        </div>


                                    </div>
                                </div>
                                <div class="card-footer">
                                    <input type="submit" name="kt_submit_button" id="kt_submit_button" class="btn btn-primary" value="Submit">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">List Of User</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body" id="load_data">
                <?php include("list_user.php"); ?>
            </div>
            <!-- /.card-body -->
        </div>

        <?php
        $content = ob_get_clean(); // Get buffered output
        print $content;
    }

}
