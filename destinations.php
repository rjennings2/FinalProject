<?php
session_start();
require_once 'database_connect.php';

$sql = "SELECT * FROM Destinations ORDER BY created_at DESC";
try {
    $stmt = $db->query($sql);
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Destinations</title>
</head>
<body>
    <h2>List of Destinations</h2>
    <table border="1">
        <tr>
            <th>Location Name</th>
            <th>Country Name</th>
            <th>Population</th>
            <th>Currency Used</th>
            <th>Description</th>
            <th>User</th>
            <th>Posted On</th>
            <th>Image</th>
            <th>Action</th>
        </tr>
        <?php foreach ($destinations as $destination): ?>
            <tr>
                <td><?php echo htmlspecialchars($destination['location_name']); ?></td>
                <td><?php echo htmlspecialchars($destination['country_name']); ?></td>
                <td><?php echo htmlspecialchars($destination['population']); ?></td>
                <td><?php echo htmlspecialchars($destination['currency_type']); ?></td>
                <td><?php echo htmlspecialchars($destination['description']); ?></td>
                <td><?php echo htmlspecialchars($destination['display_name']); ?></td>
                <td><?php echo htmlspecialchars($destination['created_at']); ?></td>
                <td>
                    <img src="https://source.unsplash.com/300x200/?<?php echo urlencode($destination['location_name']); ?>"
                         alt="Image of <?php echo htmlspecialchars($destination['location_name']); ?>">
                </td>
                <td>
                    <a href="edit_destination.php?location_id=<?php echo htmlspecialchars($destination['location_id']); ?>">Edit</a> |
                    <a href="delete_destination.php?location_id=<?php echo htmlspecialchars($destination['location_id']); ?>">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="add.php">Add New Destination</a>
    <br><br>
    <a href="logout.php">Logout</a>
</body>
</html>
