<?php
session_start();
require 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle note submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['note_text'])) {
    $note_text = trim($_POST['note_text']);
    $user_id = $_SESSION['user_id'];

    // Validate input
    if (empty($note_text)) {
        $error = "Note cannot be empty.";
    } else {
        // Insert note
        $stmt = $pdo->prepare("INSERT INTO notes (user_id, note_text) VALUES (?, ?)");
        if ($stmt->execute([$user_id, $note_text])) {
            $success = "Note added successfully!";
        } else {
            $error = "Failed to add note. Try again.";
        }
    }
}

// Fetch user's notes
$stmt = $pdo->prepare("SELECT note_text, created_at FROM notes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <p><a href="logout.php">Logout</a></p>

    <h3>Add a Note</h3>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <form method="POST" action="">
        <p>
            <label>Note:</label><br>
            <textarea name="note_text" rows="4" cols="50" required></textarea>
        </p>
        <p><input type="submit" value="Add Note"></p>
    </form>

    <h3>Your Notes</h3>
    <?php if (empty($notes)): ?>
        <p>No notes yet.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Note</th>
                <th>Created At</th>
            </tr>
            <?php foreach ($notes as $note): ?>
                <tr>
                    <td><?php echo htmlspecialchars($note['note_text']); ?></td>
                    <td><?php echo $note['created_at']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>