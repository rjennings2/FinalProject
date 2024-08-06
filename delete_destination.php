<?php
session_start();
require_once 'database_connect.php';

define('ADMIN_LOGIN', 'deletemanager');
define('ADMIN_PASSWORD', 'mypass');

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])
    || ($_SERVER['PHP_AUTH_USER'] != ADMIN_LOGIN)
    || ($_SERVER['PHP_AUTH_PW'] != ADMIN_PASSWORD)) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Protected Area"');
    exit("Access Denied: Username and password required.");
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['location_id']) || !is_numeric($_GET['location_id'])) {
    header('Location: destinations.php');
    exit;
}

$location_id = (int)$_GET['location_id'];

$sql = "DELETE FROM Destinations WHERE location_id = :location_id";
$stmt = $db->prepare($sql);
$stmt->bindParam(':location_id', $location_id, PDO::PARAM_INT);

if ($stmt->execute()) {
    header('Location: destinations.php');
    exit;
} else {
    echo "Error deleting destination.";
}
?>
