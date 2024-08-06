<?php
session_start();
require_once 'database_connect.php';

define('ADMIN_LOGIN', 'categorymanager');
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
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_category'])) {
        $category_name = sanitize_input($_POST['category_name']);
        
        if (empty($category_name)) {
            $errors[] = "Category name is required.";
        } else {
            $sql = "INSERT INTO Categories (name) VALUES (:category_name)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':category_name', $category_name);
            
            if ($stmt->execute()) {
                $success_message = "Category added successfully.";
            } else {
                $errors[] = "Error adding category.";
            }
        }
    } elseif (isset($_POST['update_category'])) {
        $category_id = $_POST['category_id'];
        $category_name = sanitize_input($_POST['category_name']);
        
        if (empty($category_name)) {
            $errors[] = "Category name is required.";
        } else {
            $sql = "UPDATE Categories SET name = :category_name WHERE id = :category_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':category_name', $category_name);
            $stmt->bindParam(':category_id', $category_id);
            
            if ($stmt->execute()) {
                $success_message = "Category updated successfully.";
            } else {
                $errors[] = "Error updating category.";
            }
        }
    } elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $category_id = $_GET['id'];
        
        $sql = "DELETE FROM Categories WHERE id = :category_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':category_id', $category_id);
        
        if ($stmt->execute()) {
            $success_message = "Category deleted successfully.";
        } else {
            $errors[] = "Error deleting category.";
        }
    }
}

$sql = "SELECT * FROM Categories ORDER BY name ASC";
$stmt = $db->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .errors, .success {
            margin-bottom: 20px;
        }
        .errors p, .success p {
            color: red;
        }
    </style>
</head>
<body>
    <h2>Manage Categories</h2>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="success">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <h3>Add New Category</h3>
    <form action="manage_categories.php" method="POST">
        <label for="category_name">Category Name:</label>
        <input type="text" id="category_name" name="category_name" required>
        <input type="submit" name="add_category" value="Add Category">
    </form>

    <h3>Existing Categories</h3>
    <ul>
        <?php foreach ($categories as $category): ?>
            <li>
                <?php echo htmlspecialchars($category['name']); ?>
                <a href="manage_categories.php?action=delete&id=<?php echo htmlspecialchars($category['id']); ?>" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                <a href="manage_categories.php?action=edit&id=<?php echo htmlspecialchars($category['id']); ?>">Edit</a>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])): ?>
        <?php
        $edit_category_id = $_GET['id'];
        $sql_edit = "SELECT * FROM Categories WHERE id = :category_id";
        $stmt_edit = $db->prepare($sql_edit);
        $stmt_edit->bindParam(':category_id', $edit_category_id);
        $stmt_edit->execute();
        $edit_category = $stmt_edit->fetch(PDO::FETCH_ASSOC);
        ?>
        <h3>Edit Category</h3>
        <form action="manage_categories.php" method="POST">
            <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($edit_category['id']); ?>">
            <label for="category_name">Category Name:</label>
            <input type="text" id="category_name" name="category_name" value="<?php echo htmlspecialchars($edit_category['name']); ?>" required>
            <input type="submit" name="update_category" value="Update Category">
        </form>
    <?php endif; ?>

    <br>
    <a href="destinations.php">Back to Destinations</a>
</body>
</html>
