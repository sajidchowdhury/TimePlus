<?php
session_start();
if (!isset($_SESSION['admin_access_token'])) {
    header("Location: login.php");
    exit();
}

require_once 'includes/autoloader.inc.php';

$userId = $_SESSION['admin_access_token'] ?? null;

if ($userId) {
    $logger = new DeviceLogger();
    $logger->logDeviceLogout($userId); // ✅ use new logout method
}

// Clean up session
$_SESSION = [];
$params = session_get_cookie_params();
setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
session_destroy();

header("Location: login.php");
exit;
