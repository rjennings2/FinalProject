<?php
session_start();
require_once 'database_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$location_id = isset($_GET['location_id']) ? intval($_GET['location_id']) : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location_name = sanitize_input($_POST['location_name']);
    $country_name = sanitize_input($_POST['country_name']);
    $population = sanitize_input($_POST['population']);
    $currency_type = sanitize_input($_POST['currency_type']);
    $description = sanitize_input($_POST['description']);
    $category_id = intval($_POST['category_id']);

    if (!empty($location_name) && !empty($country_name) && !empty($population) && !empty($currency_type)) {
        $sql = "UPDATE Destinations SET location_name = :location_name, country_name = :country_name, population = :population, currency_type = :currency_type, description = :description, category_id = :category_id WHERE location_id = :location_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':location_name', $location_name);
        $stmt->bindParam(':country_name', $country_name);
        $stmt->bindParam(':population', $population);
        $stmt->bindParam(':currency_type', $currency_type);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':location_id', $location_id);

        if ($stmt->execute()) {
            header('Location: destinations.php');
            exit;
        } else {
            $error_message = "Error updating destination.";
        }
    } else {
        $error_message = "All fields are required.";
    }
} else {
    $sql = "SELECT * FROM Destinations WHERE location_id = :location_id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':location_id', $location_id);
    $stmt->execute();
    $destination = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$destination) {
        header('Location: destinations.php');
        exit;
    }
}

function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

$sql_categories = "SELECT * FROM Categories ORDER BY name ASC";
$stmt_categories = $db->prepare($sql_categories);
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Destination</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Edit Destination</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="errors">
                <p><?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php endif; ?>

        <form action="edit_destination.php?location_id=<?php echo htmlspecialchars($location_id); ?>" method="POST">
            <label for="location_name">Location Name:</label>
            <input type="text" id="location_name" name="location_name" value="<?php echo htmlspecialchars($destination['location_name']); ?>" required><br><br>

            <label for="country_name">Country Name:</label>
            <input type="text" id="country_name" name="country_name" value="<?php echo htmlspecialchars($destination['country_name']); ?>" required><br><br>

            <label for="population">Population:</label>
            <input type="number" id="population" name="population" value="<?php echo htmlspecialchars($destination['population']); ?>" required><br><br>

            <label for="currency_type">Currency Type:</label>
            <input type="text" id="currency_type" name="currency_type" value="<?php echo htmlspecialchars($destination['currency_type']); ?>" required><br><br>

            <label for="description">Description:</label><br>
            <textarea id="description" name="description" rows="4" cols="50" required><?php echo htmlspecialchars($destination['description']); ?></textarea><br><br>

            <label for="category_id">Category:</label>
            <select id="category_id" name="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo ($category['id'] == $destination['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <input type="submit" value="Update Destination">
        </form>

        <br>
        <a href="destinations.php">Back to Destinations</a>
    </div>
</body>
</html>
