<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}
include("../config/db.php");

$user_id = $_SESSION['user_id'];

$successMsg = $errorMsg = "";

/* ✅ Create thread (POST) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['body'])) {
    $title = $conn->real_escape_string(trim($_POST['title']));
    $body  = $conn->real_escape_string(trim($_POST['body']));

    if ($title !== '' && $body !== '') {
        $sql = "INSERT INTO forum_threads (user_id, title, body) VALUES ('$user_id', '$title', '$body')";
        if ($conn->query($sql)) {
            $thread_id = $conn->insert_id;
            // award points
            $conn->query("INSERT INTO analytics (user_id, thread_id, action, points)
                          VALUES ('$user_id', '$thread_id', 'thread_created', 10)");
            $successMsg = "✅ Thread created!";
        } else {
            $errorMsg = "❌ Error: " . $conn->error;
        }
    } else {
        $errorMsg = "❌ Title and Body are required.";
    }
}

/* ✅ Fetch threads */
$q = "
SELECT t.thread_id, t.title, t.created_at, u.name,
       (SELECT COUNT(*) FROM forum_replies r WHERE r.thread_id = t.thread_id) AS replies
FROM forum_threads t
JOIN users u ON u.user_id = t.user_id
ORDER BY t.created_at DESC
";
$threads = $conn->query($q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🧵 Forum - Student Hub</title>
    <!-- <link rel="stylesheet" href="../assets/css/style.css"> main -->
    <link rel="stylesheet" href="../assets/css/forum.css"> <!-- page-specific -->
</head>
<body>
<div class="container">

    <h1 class="page-title">🧵 Student Forum</h1>

    <?php if (!empty($successMsg)) echo "<p class='success-msg'>$successMsg</p>"; ?>
    <?php if (!empty($errorMsg)) echo "<p class='error-msg'>$errorMsg</p>"; ?>

    <!-- ✅ Thread Creation Form -->
    <div class="thread-form">
        <h2>Create a New Thread</h2>
        <form method="POST">
            <input type="text" name="title" placeholder="Thread title" required>
            <textarea name="body" placeholder="Describe your question/idea..." rows="5" required></textarea>
            <button type="submit">Post Thread</button>
        </form>
    </div>

    <!-- ✅ Threads List -->
    <div class="threads-list">
        <h2>Recent Threads</h2>
        <?php if ($threads && $threads->num_rows > 0): ?>
            <table class="threads-table">
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Replies</th>
                    <th>Created</th>
                    <th>Open</th>
                </tr>
                <?php while ($row = $threads->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']); ?></td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= $row['replies']; ?></td>
                        <td><?= $row['created_at']; ?></td>
                        <td><a class="view-btn" href="view_thread.php?id=<?= $row['thread_id']; ?>">View</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p class="no-threads">No threads yet. Be the first to post!</p>
        <?php endif; ?>
    </div>

    <div class="back-link">
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>
