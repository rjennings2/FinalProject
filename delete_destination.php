<?php
session_start();
require_once 'database_connect.php';

if (!isset($_SESSION['loggedin'])) {
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