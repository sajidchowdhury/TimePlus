<?php


    include "autoloader.inc.php";


session_start();
unset($_SESSION['otp_start_time']);

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['user_id']) || !isset($input['device_data']) || !isset($input['page'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$deviceLogger = new DeviceLogger();

$userId = $input['user_id'];
$deviceData = $input['device_data'];
$page = $input['page'];
$otp = $input['otp'] ?? null;

$response = $deviceLogger->logDeviceInfo($userId, $deviceData, $page, $otp);
echo $response;
