<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include("../config/db.php");

// Get leaderboard
$result = $conn->query("
    SELECT u.name, SUM(a.points) as total_points 
    FROM analytics a
    JOIN users u ON a.user_id = u.user_id 
    WHERE u.role='student'
    GROUP BY u.user_id
    ORDER BY total_points DESC
    LIMIT 20
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/leaderboard.css?v=3">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
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

    <h1 style="color:#5eeedb ; font-size:1.5rem; font-weight:600; margin: 1rem 0 1rem 0; text-align:center;"  >🏆 Leaderboard</h1>
    

    <div class="card">
        <h3>Top Students</h3>
        <table class="leaderboard-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Name</th>
                    <th>Total Points</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // assign CSS class for top 3
                        $rowClass = "";
                        if ($rank == 1) $rowClass = "rank-1";
                        elseif ($rank == 2) $rowClass = "rank-2";
                        elseif ($rank == 3) $rowClass = "rank-3";
                ?>
                    <tr class="<?= $rowClass ?>">
                        <td>#<?= $rank ?></td>
                        <td class="name-cell">
                            <?php 
                                if ($rank == 1) echo "🥇";
                                elseif ($rank == 2) echo "🥈";
                                elseif ($rank == 3) echo "🥉";
                            ?>
                            <?= htmlspecialchars($row['name']) ?>
                        </td>
                        <td><?= $row['total_points'] ?></td>
                    </tr>
                <?php 
                        $rank++;
                    }
                } else {
                    echo '<tr><td colspan="3" class="no-data">No students found</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

</div>
<?php include('../includes/footer.php'); ?>
</body>
</html>
