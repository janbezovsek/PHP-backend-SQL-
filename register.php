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
        $email = trim($_POST['email']);
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

        // Validate inputs
        if (empty($username) || empty($email) || empty($_POST['password'])) {
            $error = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = "Username or email already taken.";
            } else {
                // Insert user
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $email, $password])) {
                    $_SESSION['success'] = "Registration successful! Please log in.";
                    regenerateCsrfToken(); // Regenerate token on success
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Registration failed. Try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h2>Register</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
        <p><label>Username:</label><input type="text" name="username" required></p>
        <p><label>Email:</label><input type="email" name="email" required></p>
        <p><label>Password:</label><input type="password" name="password" required></p>
        <p><input type="submit" value="Register"></p>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </form>
</body>
</html>