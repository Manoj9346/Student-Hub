<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");

// ✅ Fetch top students
$sql = "SELECT u.name, SUM(a.points) as total_points 
        FROM users u
        JOIN analytics a ON u.user_id = a.user_id
        WHERE u.role = 'student'
        GROUP BY u.user_id
        ORDER BY total_points DESC
        LIMIT 10";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🏆 Leaderboard - Student Hub</title>

    <link rel="stylesheet" href="../assets/css/leaderboard.css"> <!-- page-specific -->
</head>
<body class="student-leaderboard">
<div class="container">

    <h1 class="page-title">🏆 Leaderboard</h1>

    <?php if ($result && $result->num_rows > 0): ?>
        <table class="leaderboard-table">
            <tr>
                <th>Rank</th>
                <th>Student</th>
                <th>Total Points</th>
            </tr>
            <?php 
            $rank = 1;
            while ($row = $result->fetch_assoc()): 
                $medal = "";
                if ($rank == 1) $medal = "🥇";
                elseif ($rank == 2) $medal = "🥈";
                elseif ($rank == 3) $medal = "🥉";
            ?>
                <tr class="rank-<?= $rank; ?>">
                    <td><?= $medal ?: $rank; ?></td>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td><?= $row['total_points']; ?></td>
                </tr>
            <?php $rank++; endwhile; ?>
        </table>
    <?php else: ?>
        <p class="no-data">❌ No leaderboard data yet.</p>
    <?php endif; ?>

    <div class="back-link">
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>
