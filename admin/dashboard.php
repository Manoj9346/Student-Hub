<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Hub</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="admin-leaderboard">
<div class="container">

    <!-- Top Navigation -->
   <header style="display:flex; justify-content:space-between; align-items:center; padding:12px 30px; background:transparent; color:#f9fafb; position:sticky; top:0; z-index:1000;">
    <div style="display:flex; align-items:center; gap:12px;">
        <img src="../assets/uploads/generated-image.png" alt="Student Hub Logo" style="max-height:55px; width:auto; object-fit:contain; filter:drop-shadow(0 0 6px #6366f1); transition:filter 0.3s ease-in-out;" 
             onmouseover="this.style.filter='drop-shadow(0 0 10px #4f46e5)'" 
             onmouseout="this.style.filter='drop-shadow(0 0 6px #6366f1)'">
        <span style="font-size:1.4rem; font-weight:700; color:#06b6d4; letter-spacing:1px; transition: color 0.3s;" 
             
              onmouseout="this.style.color='#06b6d4'">Admin Hub</span>
    </div>
    <nav style="display:flex; align-items:center;">
        <a href="admin_profile.php" style="color:#f9fafb; text-decoration:none; margin-left:20px; font-weight:600; font-size:0.95rem; display:flex; align-items:center; gap:6px; transition: color 0.3s;" 
           onmouseover="this.style.color='#06b6d4'" 
           onmouseout="this.style.color='#f9fafb'">
            <i class="fa-solid fa-user" style="font-size:1rem;"></i> Profile
        </a>
        <a href="../auth/logout.php" style="color:#f87171; text-decoration:none; margin-left:20px; font-weight:600; font-size:0.95rem; display:flex; align-items:center; gap:6px; transition: color 0.3s;" 
           onmouseover="this.style.color='#ff4444'" 
           onmouseout="this.style.color='#f87171'">
            <i class="fa-solid fa-door-open" style="font-size:1rem;"></i> Logout
        </a>
    </nav>
</header>
    <!-- Admin Dashboard Cards -->
    <div class="dashboard-grid">

        <div class="card">
            <i class="fa-solid fa-book-open fa-3x"></i>
            <h3>Manage Resources</h3>
            <p>Add, edit, or delete learning materials and resources.</p>
            <a href="manage_resources.php">Go</a>
        </div>

        <div class="card">
            <i class="fa-solid fa-calendar-days fa-3x"></i>
            <h3>Manage Events</h3>
            <p>Create, update, or remove campus events.</p>
            <a href="manage_events.php">Go</a>
        </div>

        <div class="card">
            <i class="fa-solid fa-users fa-3x"></i>
            <h3>Manage Students</h3>
            <p>View and manage student accounts.</p>
            <a href="manage_students.php">Go</a>
        </div>

        <div class="card">
            <i class="fa-solid fa-chart-line fa-3x"></i>
            <h3>Analytics</h3>
            <p>Track student engagement and resource usage.</p>
            <a href="analytics.php">Go</a>
        </div>

        <div class="card">
            <i class="fa-solid fa-trophy fa-3x"></i>
            <h3>Leaderboard</h3>
            <p>View top students based on points.</p>
            <a href="leaderboard.php">Go</a>
        </div>

        <div class="card">
            <i class="fa-solid fa-comments fa-3x"></i>
            <h3>Forum Management</h3>
            <p>Moderate forum threads and replies.</p>
            <a href="forum_moderate.php">Go</a>
        </div>

    </div>

</div>
<!-- Include footer -->
    <?php include('../includes/footer.php'); ?>

</body>
</html>
