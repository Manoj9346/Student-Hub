<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");

$user_id = $_SESSION['user_id'];

// ✅ Fetch total points
$res = $conn->query("SELECT SUM(points) as total_points FROM analytics WHERE user_id='$user_id'");
$row = $res->fetch_assoc();
$total_points = $row['total_points'] ?? 0;

// ✅ Badge System
$badge = "🌱 Beginner";
if ($total_points >= 50 && $total_points < 100) {
    $badge = "🔥 Active Learner";
} elseif ($total_points >= 100 && $total_points < 200) {
    $badge = "⭐ Knowledge Seeker";
} elseif ($total_points >= 200) {
    $badge = "🏆 Campus Leader";
}

// ✅ Show Action History
$history = $conn->query("SELECT * FROM analytics WHERE user_id='$user_id' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🏅 My Points & Badges - Student Hub</title>
<!-- base styles -->
    <link rel="stylesheet" href="../assets/css/points.css"> <!-- page-specific -->
</head>
<body>
<div class="container">

    <h1>🏅 My Points & Badges</h1>

    <div class="points-summary">
        <p><b>Total Points:</b> <?= $total_points; ?></p>
        <p><b>Badge Earned:</b> <?= $badge; ?></p>
    </div>

    <hr>

    <h2>📊 My Activity</h2>
    <div class="table-wrap">
        <table class="points-table">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Resource/Event</th>
                    <th>Points</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $history->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['action']); ?></td>
                    <td>
                        <?php
                        if ($row['resource_id']) {
                            $resInfo = $conn->query("SELECT title FROM resources WHERE resource_id=".$row['resource_id']);
                            $r = $resInfo->fetch_assoc();
                            echo "📘 Resource: " . htmlspecialchars($r['title']);
                        } elseif ($row['event_id']) {
                            $evInfo = $conn->query("SELECT title FROM events WHERE event_id=".$row['event_id']);
                            $e = $evInfo->fetch_assoc();
                            echo "📅 Event: " . htmlspecialchars($e['title']);
                        } else {
                            echo "-";
                        }
                        ?>
                    </td>
                    <td><?= htmlspecialchars($row['points']); ?></td>
                    <td><?= htmlspecialchars($row['created_at']); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="back-link">
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>

</div>
</body>
</html>
