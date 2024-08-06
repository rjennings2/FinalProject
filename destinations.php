<?php
session_start();
require_once 'database_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$search_term = isset($_POST['search']) ? trim($_POST['search']) : '';

if ($search_term === '') {
    $sql = "SELECT * FROM Destinations ORDER BY location_name ASC, created_at DESC";
    $search_term_wildcard = '';
} else {
    $sql = "SELECT * FROM Destinations WHERE location_name LIKE :search_term ORDER BY location_name ASC, created_at DESC";
    $search_term_wildcard = "%$search_term%";
}

try {
    $stmt = $db->prepare($sql);
    if ($search_term !== '') {
        $stmt->bindParam(':search_term', $search_term_wildcard);
    }
    $stmt->execute();
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment'])) {
    $location_id = $_POST['location_id'];
    $display_name = sanitize_input($_POST['display_name']);
    $comment = sanitize_input($_POST['comment']);

    if (!empty($comment) && !empty($display_name)) {
        $sql = "INSERT INTO Comments (location_id, display_name, comment) VALUES (:location_id, :display_name, :comment)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':location_id', $location_id);
        $stmt->bindParam(':display_name', $display_name);
        $stmt->bindParam(':comment', $comment);

        if ($stmt->execute()) {
            header('Location: destinations.php');
            exit;
        } else {
            echo '<p>Error adding comment.</p>';
        }
    }
}

if (isset($_GET['action']) && isset($_GET['comment_id'])) {
    $comment_id = $_GET['comment_id'];
    $action = $_GET['action'];

    if ($action === 'delete') {
        $sql = "UPDATE Comments SET is_deleted = TRUE WHERE comment_id = :comment_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':comment_id', $comment_id);
    } elseif ($action === 'hide') {
        $sql = "UPDATE Comments SET is_visible = FALSE WHERE comment_id = :comment_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':comment_id', $comment_id);
    } elseif ($action === 'disemvowel') {
        $sql = "UPDATE Comments SET comment = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(comment, 'a', ''), 'e', ''), 'i', ''), 'o', ''), 'u', ''), 'A', ''), 'E', ''), 'I', ''), 'O', ''), 'U', '') WHERE comment_id = :comment_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':comment_id', $comment_id);
    } else {
        echo '<p>Invalid action.</p>';
    }

    if ($stmt->execute()) {
        header('Location: destinations.php');
        exit;
    } else {
        echo '<p>Error processing comment action.</p>';
    }
}

function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

function get_image_url($location_name) {
    return 'https://source.unsplash.com/300x200/?' . urlencode($location_name);
}

$sql_categories = "SELECT * FROM Categories ORDER BY name ASC";
$stmt_categories = $db->prepare($sql_categories);
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Destinations</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .details {
            display: none;
            margin-top: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
    <script>
        function toggleDetails(id) {
            var details = document.getElementById(id);
            details.style.display = details.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>List of Destinations</h2>
        
        <form method="POST" action="destinations.php">
            <input type="text" name="search" placeholder="Search by location name" value="<?php echo htmlspecialchars($search_term); ?>">
            <input type="submit" value="Search">
        </form>

        <?php if ($search_term !== ''): ?>
            <form method="POST" action="destinations.php">
                <input type="hidden" name="search" value="">
                <input type="submit" value="View All Destinations">
            </form>
        <?php endif; ?>

        <?php if (empty($destinations)): ?>
            <p class="no-matches">No location matches.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($destinations as $destination): ?>
                    <li>
                        <a href="javascript:void(0);" onclick="toggleDetails('details-<?php echo $destination['location_id']; ?>')">
                            <?php echo htmlspecialchars($destination['location_name']); ?> (by <?php echo htmlspecialchars($destination['display_name']); ?>)
                        </a>
                        <div id="details-<?php echo $destination['location_id']; ?>" class="details">
                            <p><strong>Location Name:</strong> <?php echo htmlspecialchars($destination['location_name']); ?></p>
                            <p><strong>Country Name:</strong> <?php echo htmlspecialchars($destination['country_name']); ?></p>
                            <p><strong>Population:</strong> <?php echo htmlspecialchars($destination['population']); ?></p>
                            <p><strong>Currency Used:</strong> <?php echo htmlspecialchars($destination['currency_type']); ?></p>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($destination['description']); ?></p>
                            <p><strong>User:</strong> <?php echo htmlspecialchars($destination['display_name']); ?></p>
                            <p><strong>Posted On:</strong> <?php echo htmlspecialchars($destination['created_at']); ?></p>
                            <p><strong>Category:</strong> 

                            <?php
                            $category_id = $destination['category_id'];
                            $sql_category = "SELECT name FROM Categories WHERE id = :category_id";
                            $stmt_category = $db->prepare($sql_category);
                            $stmt_category->bindParam(':category_id', $category_id);
                            $stmt_category->execute();
                            $category = $stmt_category->fetch(PDO::FETCH_ASSOC);

                            if ($category) {
                                echo htmlspecialchars($category['name']);
                            } else {
                                echo 'Uncategorized';
                            }
                            ?>
                            </p>

                            <p><strong>Image:</strong></p>
                            <img src="https://source.unsplash.com/300x200/?<?php echo urlencode($destination['location_name']); ?>" 
                                 alt="Image of <?php echo htmlspecialchars($destination['location_name']); ?>">
                            
                            <div class="comments">
                                <h3>Comments</h3>
                                <?php
                                $sql_comments = "SELECT * FROM Comments WHERE location_id = :location_id AND is_deleted = FALSE ORDER BY created_at DESC";
                                $stmt_comments = $db->prepare($sql_comments);
                                $stmt_comments->bindParam(':location_id', $destination['location_id']);
                                $stmt_comments->execute();
                                $comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <?php foreach ($comments as $comment): ?>
                                    <?php if ($comment['is_visible']): ?>
                                        <div class="comment">
                                            <p><strong><?php echo htmlspecialchars($comment['display_name']); ?>:</strong></p>
                                            <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                            <p><small>Posted on: <?php echo htmlspecialchars($comment['created_at']); ?></small></p>
                                            <p>
                                                <a href="destinations.php?action=hide&comment_id=<?php echo $comment['comment_id']; ?>">Hide</a> |
                                                <a href="destinations.php?action=disemvowel&comment_id=<?php echo $comment['comment_id']; ?>">Disemvowel</a> |
                                                <a href="destinations.php?action=delete&comment_id=<?php echo $comment['comment_id']; ?>">Delete</a>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                
                                <form action="destinations.php" method="POST">
                                    <input type="hidden" name="location_id" value="<?php echo htmlspecialchars($destination['location_id']); ?>">
                                    <label for="display_name">Your Name:</label>
                                    <input type="text" id="display_name" name="display_name" required><br><br>
                                    <label for="comment">Your Comment:</label><br>
                                    <textarea id="comment" name="comment" rows="4" cols="50" required></textarea><br><br>
                                    <input type="submit" value="Submit Comment">
                                </form>
                            </div>
                            
                            <p>
                                <a href="edit_destination.php?location_id=<?php echo htmlspecialchars($destination['location_id']); ?>">Edit</a> |
                                <a href="delete_destination.php?location_id=<?php echo htmlspecialchars($destination['location_id']); ?>">Delete</a>
                            </p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <br>
        <a href="add.php">Add New Destination</a>
        <br><br>
        <a href="manage_categories.php">Manage Categories</a>
        <br><br>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
