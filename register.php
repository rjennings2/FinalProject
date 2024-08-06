<?php
session_start();
require_once 'database_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password === $confirm_password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO Users (email, password) VALUES (:email, :password)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);

        if ($stmt->execute()) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $email; 
            header('Location: destinations.php');
            exit;
        } else {
            echo '<p>Error registering user.</p>';
        }
    } else {
        echo '<p>Passwords do not match.</p>';
    }
} else {
    header('Location: login.php');
    exit;
}
?>
