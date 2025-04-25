<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';
require 'csrf.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $error = "Invalid or expired CSRF token.";
    } else {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Validate inputs
        if (empty($username) || empty($password)) {
            $error = "All fields are required.";
        } else {
            // Check user credentials
            $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                regenerateCsrfToken(); // Regenerate token on success
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (isset($_SESSION['success'])) { echo "<p style='color:green;'>{$_SESSION['success']}</p>"; unset($_SESSION['success']); } ?>
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
        <p><label>Username:</label><input type="text" name="username" required></p>
        <p><label>Password:</label><input type="password" name="password" required></p>
        <p><input type="submit" value="Login"></p>
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </form>
</body>
</html>