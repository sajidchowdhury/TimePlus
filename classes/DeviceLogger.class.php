<?php

class DeviceLogger extends Dbh {

    public function logDeviceInfo($userId, $deviceData, $page, $otp = null) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $screenRes = $deviceData['screen_res'] ?? 'Unknown';
        $lang = $deviceData['lang'] ?? 'Unknown';
        $tzOffset = $deviceData['tz_offset'] ?? 0;

        if ($page !== 'otp_verify') {
            $this->logDeviceLogout($userId);
            return json_encode(['status' => 'success', 'message' => 'Logout logged']);
        }

        if ($page === 'otp_verify') {
            if ($otp === null) {
                return json_encode(['status' => 'error', 'message' => 'OTP is required']);
            }

           if (!$this->OtpVarifiaction($userId, $otp)) {
                return json_encode(['status' => 'error', 'message' => 'Invalid OTP']);
            }
        }

        // Avoid duplicate logging
        if (!empty($_SESSION['device_logged']) && $_SESSION['device_logged'] === true) {
            return json_encode(['status' => 'success', 'message' => 'Already logged']);
        }

        try {
            // Log user activity (login)
            $activityStmt = $this->connect()->prepare("
                INSERT INTO user_activity_log (user_id, ip_address, user_agent, login_time)
                VALUES (:user_id, :ip, :ua, NOW())
            ");
            $activityStmt->execute([
                ':user_id' => $userId,
                ':ip' => $ip,
                ':ua' => $userAgent
            ]);

            // Check for existing fingerprint
            $checkStmt = $this->connect()->prepare("
                SELECT id FROM user_device_fingerprint
                WHERE user_id = :user_id AND ip_address = :ip AND user_agent = :ua 
                AND screen_res = :res AND language = :lang AND timezone_offset = :tz
            ");
            $checkStmt->execute([
                ':user_id' => $userId,
                ':ip' => $ip,
                ':ua' => $userAgent,
                ':res' => $screenRes,
                ':lang' => $lang,
                ':tz' => $tzOffset
            ]);

            if ($checkStmt->rowCount() === 0) {
                // Insert new fingerprint
                $insertStmt = $this->connect()->prepare("
                    INSERT INTO user_device_fingerprint 
                    (user_id, ip_address, user_agent, screen_res, language, timezone_offset)
                    VALUES (:user_id, :ip, :ua, :res, :lang, :tz)
                ");
                $insertStmt->execute([
                    ':user_id' => $userId,
                    ':ip' => $ip,
                    ':ua' => $userAgent,
                    ':res' => $screenRes,
                    ':lang' => $lang,
                    ':tz' => $tzOffset
                ]);
            }

            $_SESSION['device_logged'] = true;

            return json_encode(['status' => 'success', 'message' => 'Login successful']);
        } catch (Exception $e) {
            return json_encode(['status' => 'error', 'message' => 'Server error']);
        }
    }

    public function logDeviceLogout($userId) {
        try {
            $updateStmt = $this->connect()->prepare("
                UPDATE user_activity_log
                SET logout_time = NOW()
                WHERE user_id = :user_id AND logout_time IS NULL
                ORDER BY id DESC
                LIMIT 1
            ");
            $updateStmt->execute([':user_id' => $userId]);

            $_SESSION['device_logged'] = false;
        } catch (Exception $e) {
            // optional: log the error
        }
    }

private function OtpVarifiaction($userId, $otp) {



    $stmt = $this->connect()->prepare('SELECT * FROM admin WHERE id = :userId and otp = :otp ');
    $stmt->bindParam(':userId', $userId);
    $stmt->bindParam(':otp', $otp);

    if (!$stmt->execute()) {
        return false;
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = null;

    if (!$user) {
        return false;
    }

    // Load the user's stored secret
  $_SESSION['admin_access_token'] = $user['id'];
        $_SESSION['admin_access_name'] = $user['employee_name'];
        $_SESSION['admin_access_roll'] = $user['user_type'];
        $_SESSION['logintime'] = $user['login_start'];
        $_SESSION['logouttime'] = $user['login_end'];
         return true;

/*
    $otp2 = new OtpHelper($user['otp']); // assuming 'otp' column stores the OTP secret

    if ($otp2->verifyOtp($otp)) {
        
        $_SESSION['admin_access_token'] = $user['id'];
        $_SESSION['admin_access_name'] = $user['employee_name'];
        $_SESSION['admin_access_roll'] = $user['user_type'];
        $_SESSION['logintime'] = $user['login_start'];
        $_SESSION['logouttime'] = $user['login_end'];

        return true;
    } else {
        return false;
    }
    
    */
}




    private function resetOTP($user_id) {
        $stmt = $this->connect()->prepare("
            UPDATE admin SET otp = NULL , otp_created_at = NULL, otp_attempts = 0  WHERE id = :id
        ");
        $stmt->execute([':id' => $user_id]);
    }

}
