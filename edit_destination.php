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

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $sql = "SELECT * FROM Destinations WHERE location_id = :location_id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':location_id', $location_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $destination = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "Error fetching destination.";
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $country_name = $_POST['country_name'];
    $population = $_POST['population'];
    $currency_type = $_POST['currency_type'];
    $description = $_POST['description'];

    $sql = "UPDATE Destinations
            SET country_name = :country_name, population = :population, currency_type = :currency_type, description = :description
            WHERE location_id = :location_id";
    $stmt = $db->prepare($sql);

    $stmt->bindParam(':country_name', $country_name);
    $stmt->bindParam(':population', $population);
    $stmt->bindParam(':currency_type', $currency_type);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':location_id', $location_id);

    if ($stmt->execute()) {
        header('Location: destinations.php');
        exit;
    } else {
        echo "Error updating destination.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Destination</title>
</head>
<body>
    <h2>Edit Destination</h2>
    <form action="edit_destination.php?location_id=<?php echo htmlspecialchars($location_id); ?>" method="POST">
        <label for="country_name">Country:</label>
        <input type="text" id="country_name" name="country_name" value="<?php echo htmlspecialchars($destination['country_name']); ?>" required><br><br>
        <label for="population">Population:</label>
        <input type="number" id="population" name="population" value="<?php echo htmlspecialchars($destination['population']); ?>"><br><br>
        <label for="currency_type">Currency Used:</label>
        <input type="text" id="currency_type" name="currency_type" value="<?php echo htmlspecialchars($destination['currency_type']); ?>" required><br><br>
        <label for="description">Description:</label><br>
        <textarea id="description" name="description" rows="4" cols="50" required><?php echo htmlspecialchars($destination['description']); ?></textarea><br><br>
        <input type="submit" value="Update">
    </form>
    <br>
    <a href="destinations.php">Back to Destinations</a>
</body>
</html>
