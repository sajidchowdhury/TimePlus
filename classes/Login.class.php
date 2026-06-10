<?php
// Namespaces go at the very top — before the class starts
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Login extends Dbh {

    protected function getAdmin($user_email, $user_password) {
        date_default_timezone_set('Asia/Dhaka');
        $current_time = date("H:i:s");

        $stmt = $this->connect()->prepare('SELECT * FROM admin WHERE email = :email');
        $stmt->bindParam(':email', $user_email);

        if (!$stmt->execute()) {
            return ['mess' => 18, 'session_key' => 'NOT_FOUND'];
        }

        if ($stmt->rowCount() === 0) {
            return ['mess' => 22, 'session_key' => 'NOT_FOUND'];
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = null;

        if ($user['block_user'] === 'Yes') {
            return ['mess' => 20, 'session_key' => 'BLOCKED'];
        }
        
        if ($user['user_type'] === 'SuperAdmin') {
        return ['mess' => 'ACTION_REQUIRED', 'session_key' => $user];
        }

        if ($current_time < $user['login_start'] || $current_time > $user['login_end']) {
            return ['mess' => 99, 'session_key' => 'NOT_ALLOWED_TIME'];
        }

        if (password_verify($user_password, $user['hash_pass'])) {
            $this->resetLoginAttempts($user['id']);

            if ($user['otp_date'] != date('Y-m-d')) {
                $this->CreateOtp($user['id'], $user['employee_name']);
            }

            return ['mess' => 'ACTION_REQUIRED', 'session_key' => $user];
        } else {
            $this->recordFailedLogin($user['id'], $user['login_attempts']);
            return ['mess' => 6, 'session_key' => 'NOT_FOUND'];
        }
    }

    private function recordFailedLogin($user_id, $current_attempts) {
        $new_attempts = $current_attempts + 1;
        $block = ($new_attempts >= 8) ? 'Yes' : 'No';

        $stmt = $this->connect()->prepare("
            UPDATE admin SET 
                login_attempts = :attempts, 
                block_user = :block,
                last_login_attempt = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':attempts' => $new_attempts,
            ':block' => $block,
            ':id' => $user_id
        ]);
    }

    private function resetLoginAttempts($user_id) {
        $stmt = $this->connect()->prepare("
            UPDATE admin SET login_attempts = 0, last_login_attempt = NULL WHERE id = :id
        ");
        $stmt->execute([':id' => $user_id]);
    }

    public function generateOTP() {
        return mt_rand(100000, 999999);
    }

    private function CreateOtp($user_id, $employee_name) {
    $db = $this->connect();
    $sms_date = date('Y-m-d');

    // Generate OTP
    $userSecret = $this->generateOTP();
    $user_email = 'salim.uddin197@gmail.com'; // recipient email from DB or passed as parameter

    // Update DB with OTP
    $stmt = $db->prepare("UPDATE admin SET otp = :otp, otp_date = :sms_date WHERE id = :id");
    $stmt->execute([
        ':otp' => $userSecret,
        ':id' => $user_id,
        ':sms_date' => $sms_date
    ]);

    require_once __DIR__ . '/../vendor/autoload.php';
    $mail = new PHPMailer(true);

    try {
        // Gmail SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mycreativecodeotp@gmail.com'; // your Gmail
        $mail->Password   = 'lxepfflfqtxyuyiw';   // Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Email headers
        $mail->setFrom('mycreativecodeotp@gmail.com', 'TimePlus OTP');
        $mail->addAddress($user_email, $employee_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "OTP for $employee_name";
        $mail->Body    = "
            <p>Hello {$employee_name},</p>
            <p>Your OTP code is: <strong>{$userSecret}</strong></p>
            <p>This code will be valid for 24 hours.</p>
            <br>
            <p>Regards,<br>TimePlus Tech Team</p>
        ";
        $mail->AltBody = "Hello {$employee_name},\nYour OTP code is: {$userSecret}\nThis code will expire soon.";

        if ($mail->send()) {
           // error_log("✅ OTP email sent successfully to {$user_email}");
        } else {
            error_log("❌ Mailer Error: " . $mail->ErrorInfo);
        }

    } catch (Exception $e) {
        error_log("❌ PHPMailer Exception: " . $e->getMessage());
    }
}



}
