<?php
session_start();
require_once 'database_connect.php';

define('ADMIN_LOGIN', 'createmanager');
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

$errors = [];
$location_name = '';
$categories = [];

$sql_categories = "SELECT * FROM Categories ORDER BY name ASC";
$stmt_categories = $db->prepare($sql_categories);
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

function get_image_url($location_name) {
    $image_url = 'https://source.unsplash.com/300x200/?' . urlencode($location_name);
    return $image_url;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $display_name = sanitize_input($_POST['display_name']);
    $location_name = sanitize_input($_POST['location_name']);
    $country_name = sanitize_input($_POST['country_name']);
    $population = $_POST['population'];
    $currency_type = sanitize_input($_POST['currency_type']);
    $description = sanitize_input($_POST['description']);
    $category_id = $_POST['category_id'];

    if (!is_numeric($population) || $population <= 0) {
        $errors[] = "Please enter a valid population number.";
    }

    if (!preg_match("/^[a-zA-Z ]*$/", $location_name)) {
        $errors[] = "Location name should contain only letters and spaces.";
    }
    if (!preg_match("/^[a-zA-Z ]*$/", $country_name)) {
        $errors[] = "Country name should contain only letters and spaces.";
    }
    if (!preg_match("/^[a-zA-Z ]*$/", $currency_type)) {
        $errors[] = "Currency type should contain only letters and spaces.";
    }

    if (empty($errors)) {
        $image_url = get_image_url($location_name);

        $sql = "INSERT INTO Destinations (location_name, country_name, population, currency_type, description, display_name, image_url, category_id)
                VALUES (:location_name, :country_name, :population, :currency_type, :description, :display_name, :image_url, :category_id)";
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':location_name', $location_name);
        $stmt->bindParam(':country_name', $country_name);
        $stmt->bindParam(':population', $population);
        $stmt->bindParam(':currency_type', $currency_type);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':display_name', $display_name);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':category_id', $category_id);

        if ($stmt->execute()) {
            header('Location: destinations.php');
            exit;
        } else {
            $errors[] = "Error adding destination.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Destination</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function focusInvalidField() {
            var errors = <?php echo json_encode($errors); ?>;
            if (errors.length > 0) {
                var focusField = '';
                if (document.getElementById('location_name').value === '' || document.getElementById('location_name').value.length < 1) {
                    focusField = 'location_name';
                } else if (document.getElementById('country_name').value === '' || document.getElementById('country_name').value.length < 1) {
                    focusField = 'country_name';
                } else if (document.getElementById('currency_type').value === '' || document.getElementById('currency_type').value.length < 1) {
                    focusField = 'currency_type';
                } else if (document.getElementById('population').value === '' || !/^\d+$/.test(document.getElementById('population').value)) {
                    focusField = 'population';
                }
                if (focusField) {
                    document.getElementById(focusField).focus();
                }
            }
        }

        window.onload = focusInvalidField;
    </script>
</head>
<body>
    <div class="container">
        <h2>Add New Destination</h2>
        <?php
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo '<p class="errors">' . htmlspecialchars($error) . '</p>';
            }
        }
        ?>
        <form action="add.php" method="POST">
            <label for="display_name">Your Name:</label>
            <input type="text" id="display_name" name="display_name" value="<?php echo htmlspecialchars($display_name ?? ''); ?>" required><br><br>
            <label for="location_name">Location:</label>
            <input type="text" id="location_name" name="location_name" value="<?php echo htmlspecialchars($location_name ?? ''); ?>" required><br><br>
            <label for="country_name">Country:</label>
            <input type="text" id="country_name" name="country_name" value="<?php echo htmlspecialchars($country_name ?? ''); ?>" required><br><br>
            <label for="population">Population:</label>
            <input type="number" id="population" name="population" min="1" value="<?php echo htmlspecialchars($population ?? ''); ?>"><br><br>
            <label for="currency_type">Currency Used:</label>
            <input type="text" id="currency_type" name="currency_type" value="<?php echo htmlspecialchars($currency_type ?? ''); ?>" required><br><br>
            <label for="category_id">Category:</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['id']); ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>
            <label for="description">Description:</label><br>
            <textarea id="description" name="description" rows="4" cols="50" required><?php echo htmlspecialchars($description ?? ''); ?></textarea><br><br>
            <input type="submit" value="Submit">
        </form>
        <br>
        <a href="destinations.php">Back to Destinations</a>
    </div>
</body>
</html>
