<?php
session_start();
require_once 'database_connect.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $display_name = $_POST['display_name'];
    $location_name = $_POST['location_name'];
    $country_name = $_POST['country_name'];
    $population = $_POST['population'];
    $currency_type = $_POST['currency_type'];
    $description = $_POST['description'];

    $sql = "INSERT INTO Destinations (location_name, country_name, population, currency_type, description, display_name)
            VALUES (:location_name, :country_name, :population, :currency_type, :description, :display_name)";
    $stmt = $db->prepare($sql);

    $stmt->bindParam(':location_name', $location_name);
    $stmt->bindParam(':country_name', $country_name);
    $stmt->bindParam(':population', $population);
    $stmt->bindParam(':currency_type', $currency_type);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':display_name', $display_name);

    if ($stmt->execute()) {
        header('Location: destinations.php'); 
        exit;
    } else {
        echo "Error adding destination.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Destination</title>
</head>
<body>
    <h2>Add New Destination</h2>
    <form action="add.php" method="POST">
        <label for="display_name">Your Name:</label>
        <input type="text" id="display_name" name="display_name" required><br><br>
        <label for="location_name">Location:</label>
        <input type="text" id="location_name" name="location_name" required><br><br>
        <label for="country_name">Country:</label>
        <input type="text" id="country_name" name="country_name" required><br><br>
        <label for="population">Population:</label>
        <input type="number" id="population" name="population"><br><br>
        <label for="currency_type">Currency Used:</label>
        <input type="text" id="currency_type" name="currency_type" required><br><br>
        <label for="description">Description:</label><br>
        <textarea id="description" name="description" rows="4" cols="50" required></textarea><br><br>
        <input type="submit" value="Submit">
    </form>
    <br>
    <a href="destinations.php">Back to Destinations</a>
</body>
</html>
