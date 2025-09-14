<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Hub</title>
    <!-- External CSS -->
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>

    <!-- Include header -->
    <?php include('../includes/header.php'); ?>

    <div class="container">
        <div class="dashboard-grid">

            <!-- Learning -->
            <div class="card">
                <i class="fa-solid fa-book-open fa-3x"></i>
                <h3>View Resources</h3>
                <p>Access all learning materials and notes.</p>
                <a href="resources.php">Go</a>
            </div>

            <div class="card">
                <i class="fa-solid fa-floppy-disk fa-3x"></i>
                <h3>My Saved Resources</h3>
                <p>Your bookmarked learning materials.</p>
                <a href="my_resources.php">Go</a>
            </div>

            <!-- Events -->
            <div class="card">
                <i class="fa-solid fa-calendar-days fa-3x"></i>
                <h3>Upcoming Events</h3>
                <p>Check and register for events.</p>
                <a href="events.php">Go</a>
            </div>

            <div class="card">
                <i class="fa-solid fa-star fa-3x"></i>
                <h3>My Events</h3>
                <p>View events you are registered for.</p>
                <a href="my_events.php">Go</a>
            </div>

            <!-- Progress -->
            <div class="card">
                <i class="fa-solid fa-medal fa-3x"></i>
                <h3>My Points & Badges</h3>
                <p>Track your achievements and badges.</p>
                <a href="points.php">Go</a>
            </div>

            <div class="card">
                <i class="fa-solid fa-trophy fa-3x"></i>
                <h3>Leaderboard</h3>
                <p>See the top students in points.</p>
                <a href="leaderboard.php">Go</a>
            </div>

            <div class="card">
                <i class="fa-solid fa-chart-line fa-3x"></i>
                <h3>My Progress</h3>
                <p>Analyze your learning over time.</p>
                <a href="progress.php">Go</a>
            </div>

            <!-- Community -->
            <div class="card">
                <i class="fa-solid fa-comments fa-3x"></i>
                <h3>Forum</h3>
                <p>Ask questions or help peers.</p>
                <a href="forum.php">Go</a>
            </div>

        </div>
    </div>

    <!-- Include footer -->
    <?php include('../includes/footer.php'); ?>

</body>
</html>
