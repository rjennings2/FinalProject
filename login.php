<?php
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: destinations.php');
    exit;
}

$error = '';
$registration_success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $valid_username = 'admin';
        $valid_password = 'password';

        if ($username === $valid_username && $password === $valid_password) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;

            header('Location: destinations.php');
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } elseif (isset($_POST['register'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            $registration_success = "Registration successful!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login and Register</title>
</head>
<body>
    <h2>Login Page</h2>
    <?php if ($error) echo "<p style='color: red;'>$error</p>"; ?>
    <?php if ($registration_success) echo "<p style='color: green;'>$registration_success</p>"; ?>

    <form action="login.php" method="POST">
        <h3>Login</h3>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" name="login" value="Login">
    </form>

    <form action="login.php" method="POST">
        <h3>Register</h3>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>
        <input type="submit" name="register" value="Register">
    </form>
</body>
</html>
