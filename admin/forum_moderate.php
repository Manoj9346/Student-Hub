<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include("../config/db.php");

// Delete thread
if (isset($_GET['delete_thread'])) {
    $id = intval($_GET['delete_thread']);
    $conn->query("DELETE FROM forum_threads WHERE thread_id=$id");
    $conn->query("DELETE FROM forum_replies WHERE thread_id=$id");
    header("Location: forum_moderate.php");
    exit();
}

// Delete reply
if (isset($_GET['delete_reply'])) {
    $id = intval($_GET['delete_reply']);
    $conn->query("DELETE FROM forum_replies WHERE reply_id=$id");
    header("Location: forum_moderate.php");
    exit();
}

// Fetch threads
$threads = $conn->query("
    SELECT t.thread_id, t.title, u.name, t.created_at
    FROM forum_threads t
    JOIN users u ON t.user_id = u.user_id
    ORDER BY t.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forum Management</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
        <link rel="stylesheet" href="../assets/css/forum_moderate.css">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
 
<header style="display:flex; justify-content:space-between; align-items:center; padding:12px 30px; background:transparent; color:#f9fafb; position:sticky; top:0; z-index:1000;">
    <div style="display:flex; align-items:center; gap:12px;">
        <img src="../assets/uploads/generated-image.png" alt="Student Hub Logo" style="max-height:55px; width:auto; object-fit:contain; filter:drop-shadow(0 0 6px #6366f1); transition:filter 0.3s ease-in-out;" 
             onmouseover="this.style.filter='drop-shadow(0 0 10px #4f46e5)'" 
             onmouseout="this.style.filter='drop-shadow(0 0 6px #6366f1)'">
        <span style="font-size:1.4rem; font-weight:700; color:#06b6d4; letter-spacing:1px; transition: color 0.3s;" 
             
              onmouseout="this.style.color='#06b6d4'">Student Hub</span>
    </div>
    <nav style="display:flex; align-items:center;">
        <a href="admin_profile.php" style="color:#f9fafb; text-decoration:none; margin-left:20px; font-weight:600; font-size:0.95rem; display:flex; align-items:center; gap:6px; transition: color 0.3s;" 
           onmouseover="this.style.color='#06b6d4'" 
           onmouseout="this.style.color='#f9fafb'">
            <i class="fa-solid fa-user" style="font-size:1rem;"></i> Profile
        </a>
        <a href="dashboard.php" style="color:#f9fafb; text-decoration:none; margin-left:20px; font-weight:600; font-size:0.95rem; display:flex; align-items:center; gap:6px; transition: color 0.3s;" 
           onmouseover="this.style.color='#06b6d4'" 
           onmouseout="this.style.color='#f9fafb'">
            <i class="fa-solid fa-user" style="font-size:1rem;"></i> Dashboard
        </a>
        <a href="../auth/logout.php" style="color:#f87171; text-decoration:none; margin-left:20px; font-weight:600; font-size:0.95rem; display:flex; align-items:center; gap:6px; transition: color 0.3s;" 
           onmouseover="this.style.color='#ff4444'" 
           onmouseout="this.style.color='#f87171'">
            <i class="fa-solid fa-door-open" style="font-size:1rem;"></i> Logout
        </a>
    </nav>
</header>

<div class="container">

    <div class="logo" style="text-align: center;"><h2>🛠️ Forum Management</h2></div>
    <div class="card">
        <h3>Forum Threads</h3>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Thread Title</th>
                    <th>Created By</th>
                    <th>Date</th>
                    <th>⚙️ Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($thread = $threads->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($thread['title']) ?></td>
                        <td><?= htmlspecialchars($thread['name']) ?></td>
                        <td><?= $thread['created_at'] ?></td>
                        <td>
                            <a href="?delete_thread=<?= $thread['thread_id'] ?>" 
                               class="btn-danger"
                               onclick="return confirm('Delete this thread?')">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
