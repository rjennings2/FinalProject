<?php
session_start();
require_once 'database_connect.php';

$sql = "SELECT * FROM Destinations ORDER BY location_name ASC, created_at DESC";
try {
    $stmt = $db->query($sql);
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Handle new comment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment'])) {
    $location_id = $_POST['location_id'];
    $display_name = sanitize_input($_POST['display_name']);
    $comment = sanitize_input($_POST['comment']);

    if (!empty($comment) && !empty($display_name)) {
        $sql = "INSERT INTO Comments (location_id, display_name, comment)
                VALUES (:location_id, :display_name, :comment)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':location_id', $location_id);
        $stmt->bindParam(':display_name', $display_name);
        $stmt->bindParam(':comment', $comment);

        if ($stmt->execute()) {
            // Redirect to avoid form resubmission
            header('Location: destinations.php');
            exit;
        } else {
            echo '<p style="color: red;">Error adding comment.</p>';
        }
    }
}

function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

function get_image_url($location_name) {
    return 'https://source.unsplash.com/300x200/?' . urlencode($location_name);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Destinations</title>
    <style>
        .details {
            display: none;
            margin-top: 10px;
        }
        .comments {
            margin-top: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .comment {
            margin-bottom: 10px;
        }
    </style>
    <script>
        function toggleDetails(id) {
            var details = document.getElementById(id);
            if (details.style.display === 'none') {
                details.style.display = 'block';
            } else {
                details.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <h2>List of Destinations</h2>
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
                    <p><strong>Image:</strong></p>
                    <img src="https://source.unsplash.com/300x200/?<?php echo urlencode($destination['location_name']); ?>" 
                         alt="Image of <?php echo htmlspecialchars($destination['location_name']); ?>">
                    
                    <!-- Comments Section -->
                    <div class="comments">
                        <h3>Comments</h3>
                        <?php
                        // Fetch comments for the current destination
                        $sql_comments = "SELECT * FROM Comments WHERE location_id = :location_id ORDER BY created_at DESC";
                        $stmt_comments = $db->prepare($sql_comments);
                        $stmt_comments->bindParam(':location_id', $destination['location_id']);
                        $stmt_comments->execute();
                        $comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <p><strong><?php echo htmlspecialchars($comment['display_name']); ?>:</strong></p>
                                <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                <p><small>Posted on: <?php echo htmlspecialchars($comment['created_at']); ?></small></p>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Comment Form -->
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
    <br>
    <a href="add.php">Add New Destination</a>
    <br><br>
    <a href="logout.php">Logout</a>
</body>
</html>
