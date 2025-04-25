<?php
session_start();
require 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle note submission (add new note)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['note_text']) && !isset($_POST['edit_note_id'])) {
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

// Handle note update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_note_id'])) {
    $note_id = $_POST['edit_note_id'];
    $note_text = trim($_POST['note_text']);
    $user_id = $_SESSION['user_id'];

    // Validate input
    if (empty($note_text)) {
        $error = "Note cannot be empty.";
    } else {
        // Update note
        $stmt = $pdo->prepare("UPDATE notes SET note_text = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$note_text, $note_id, $user_id])) {
            $success = "Note updated successfully!";
        } else {
            $error = "Failed to update note. Try again.";
        }
    }
}

// Handle note deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_note_id'])) {
    $note_id = $_POST['delete_note_id'];
    $user_id = $_SESSION['user_id'];

    // Delete note
    $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$note_id, $user_id])) {
        $success = "Note deleted successfully!";
    } else {
        $error = "Failed to delete note. Try again.";
    }
}

// Fetch user's notes
$stmt = $pdo->prepare("SELECT id, note_text, created_at FROM notes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle edit mode (populate form with existing note)
$edit_mode = false;
$edit_note = null;
if (isset($_GET['edit_note_id'])) {
    $note_id = $_GET['edit_note_id'];
    $stmt = $pdo->prepare("SELECT id, note_text FROM notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$note_id, $_SESSION['user_id']]);
    $edit_note = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($edit_note) {
        $edit_mode = true;
    }
}
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
        .action-btn {
            padding: 5px 10px;
            margin-right: 5px;
            cursor: pointer;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <p><a href="logout.php">Logout</a></p>

    <h3><?php echo $edit_mode ? 'Edit Note' : 'Add a Note'; ?></h3>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <form method="POST" action="">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="edit_note_id" value="<?php echo $edit_note['id']; ?>">
        <?php endif; ?>
        <p>
            <label>Note:</label><br>
            <textarea name="note_text" rows="4" cols="50" required><?php echo $edit_mode ? htmlspecialchars($edit_note['note_text']) : ''; ?></textarea>
        </p>
        <p><input type="submit" value="<?php echo $edit_mode ? 'Update Note' : 'Add Note'; ?>"></p>
        <?php if ($edit_mode): ?>
            <p><a href="dashboard.php">Cancel Edit</a></p>
        <?php endif; ?>
    </form>

    <h3>Your Notes</h3>
    <?php if (empty($notes)): ?>
        <p>No notes yet.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Note</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($notes as $note): ?>
                <tr>
                    <td><?php echo htmlspecialchars($note['note_text']); ?></td>
                    <td><?php echo $note['created_at']; ?></td>
                    <td>
                        <form method="GET" action="" style="display:inline;">
                            <input type="hidden" name="edit_note_id" value="<?php echo $note['id']; ?>">
                            <input type="submit" value="Edit" class="action-btn">
                        </form>
                        <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this note?');">
                            <input type="hidden" name="delete_note_id" value="<?php echo $note['id']; ?>">
                            <input type="submit" value="Delete" class="action-btn">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>