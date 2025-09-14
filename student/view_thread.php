<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}
include("../config/db.php");

$user_id   = $_SESSION['user_id'];
$thread_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($thread_id <= 0) { echo "Invalid thread."; exit(); }

/* Log a view (+2 points) */
$conn->query("INSERT INTO analytics (user_id, thread_id, action, points)
              VALUES ('$user_id', '$thread_id', 'thread_viewed', 2)");

/* Fetch thread */
$tres = $conn->query("
    SELECT t.title, t.body, t.created_at, u.name
    FROM forum_threads t
    JOIN users u ON u.user_id = t.user_id
    WHERE t.thread_id = $thread_id
");
if (!$tres || $tres->num_rows === 0) { echo "Thread not found."; exit(); }
$thread = $tres->fetch_assoc();

/* Post a reply */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_body'])) {
    $body = $conn->real_escape_string(trim($_POST['reply_body']));
    if ($body !== '') {
        if ($conn->query("INSERT INTO forum_replies (thread_id, user_id, body) VALUES ($thread_id, $user_id, '$body')")) {
            $reply_id = $conn->insert_id;
            $conn->query("INSERT INTO analytics (user_id, thread_id, reply_id, action, points)
                          VALUES ('$user_id', '$thread_id', '$reply_id', 'reply_posted', 5)");
            header("Location: view_thread.php?id=".$thread_id);
            exit();
        } else {
            $error_msg = "❌ Error: " . $conn->error;
        }
    } else {
        $error_msg = "❌ Reply cannot be empty.";
    }
}

/* Fetch replies */
$rres = $conn->query("
    SELECT r.body, r.created_at, u.name
    FROM forum_replies r
    JOIN users u ON u.user_id = r.user_id
    WHERE r.thread_id = $thread_id
    ORDER BY r.created_at ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🧵 <?php echo htmlspecialchars($thread['title']); ?> - Student Hub</title>
    <link rel="stylesheet" href="../assets/css/view_thread.css"> <!-- page-specific -->
</head>
<body>
<div class="container">
    <h1 class="thread-title">🧵 <?php echo htmlspecialchars($thread['title']); ?></h1>
    <p class="thread-meta"><b>By:</b> <?= htmlspecialchars($thread['name']); ?> | <b>On:</b> <?= $thread['created_at']; ?></p>
    <div class="thread-body"><?= nl2br(htmlspecialchars($thread['body'])); ?></div>
    <hr>

    <h2>Replies</h2>
    <?php if ($rres && $rres->num_rows > 0): ?>
        <ul class="replies-list">
        <?php while ($r = $rres->fetch_assoc()): ?>
            <li>
                <b><?= htmlspecialchars($r['name']); ?></b> (<?= $r['created_at']; ?>)<br>
                <?= nl2br(htmlspecialchars($r['body'])); ?>
            </li>
        <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p class="no-replies">No replies yet. Be the first to reply!</p>
    <?php endif; ?>

    <?php if(isset($error_msg)): ?>
        <p class="error-msg"><?= htmlspecialchars($error_msg); ?></p>
    <?php endif; ?>

    <h3>Add a Reply</h3>
    <form method="POST" class="reply-form">
        <textarea name="reply_body" rows="4" placeholder="Write your reply..." required></textarea><br>
        <button type="submit">Reply</button>
    </form>

    <div class="back-links">
        <a href="forum.php">⬅ Back to Forum</a> | <a href="dashboard.php">Dashboard</a>
    </div>
</div>
</body>
</html>
