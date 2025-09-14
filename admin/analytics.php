<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include("../config/db.php");

$filter = isset($_GET['filter']) ? $_GET['filter'] : '30'; // default = last 30 days
$dateCondition = "AND a.created_at >= DATE_SUB(NOW(), INTERVAL $filter DAY)";

// ------------------------- Top Students -------------------------
$leaderboardData = [];
$result = $conn->query("
    SELECT u.name, SUM(a.points) as total_points 
    FROM analytics a 
    JOIN users u ON a.user_id = u.user_id 
    WHERE u.role = 'student' $dateCondition
    GROUP BY u.user_id 
    ORDER BY total_points DESC 
    LIMIT 5
");
while ($row = $result->fetch_assoc()) {
    $leaderboardData[] = $row;
}

// ------------------------- Popular Resources -------------------------
$resourceData = [];
$result = $conn->query("
    SELECT r.title, COUNT(a.resource_id) as usage_count 
    FROM analytics a 
    JOIN resources r ON a.resource_id = r.resource_id 
    WHERE a.resource_id IS NOT NULL $dateCondition
    GROUP BY r.resource_id 
    ORDER BY usage_count DESC 
    LIMIT 5
");
while ($row = $result->fetch_assoc()) {
    $resourceData[] = $row;
}

// ------------------------- Popular Events -------------------------
$eventData = [];
$result = $conn->query("
    SELECT e.title, COUNT(a.event_id) as registrations 
    FROM analytics a 
    JOIN events e ON a.event_id = e.event_id 
    WHERE a.action = 'registered_event' $dateCondition
    GROUP BY e.event_id 
    ORDER BY registrations DESC 
    LIMIT 5
");
while ($row = $result->fetch_assoc()) {
    $eventData[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>📊 Admin Analytics</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/analytics.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
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

    <h1 style="color:#5eeedb ; font-size:1.5rem; font-weight:600; margin: 1rem 0 1rem 0; text-align:center;"  >📊 Analytics Dashboard</h1>

    <!-- Filter -->
    <form method="GET" class="filter-form">
        <label>📅 Select Date Range: </label>
        <select name="filter" onchange="this.form.submit()">
            <option value="7"  <?php if($filter=='7') echo 'selected'; ?>>Last 7 Days</option>
            <option value="30" <?php if($filter=='30') echo 'selected'; ?>>Last 30 Days</option>
            <option value="90" <?php if($filter=='90') echo 'selected'; ?>>Last 90 Days</option>
            <option value="365"<?php if($filter=='365') echo 'selected'; ?>>Last 1 Year</option>
        </select>
    </form>

    <!-- Charts Grid -->
    <div class="analytics-grid">
        <div class="card">
            <h2>🏆 Top Students</h2>
            <canvas id="leaderboardChart"></canvas>
        </div>
        <div class="card">
            <h2>📚 Most Popular Resources</h2>
            <canvas id="resourceChart"></canvas>
        </div>
        <div class="card">
            <h2>📅 Most Popular Events</h2>
            <canvas id="eventChart"></canvas>
        </div>
    </div>

    <!-- Backup Table View -->
    <div class="backup-tables">
        <div class="card">
            <h3>🏆 Top Students</h3>
            <ul>
            <?php foreach ($leaderboardData as $row) {
                echo "<li>".$row['name']." - ".$row['total_points']." points</li>";
            } ?>
            </ul>
        </div>

        <div class="card">
            <h3>📚 Most Popular Resources</h3>
            <ul>
            <?php foreach ($resourceData as $row) {
                echo "<li>".$row['title']." - ".$row['usage_count']." times</li>";
            } ?>
            </ul>
        </div>

        <div class="card">
            <h3>📅 Most Popular Events</h3>
            <ul>
            <?php foreach ($eventData as $row) {
                echo "<li>".$row['title']." - ".$row['registrations']." registrations</li>";
            } ?>
            </ul>
        </div>
    </div>

    <br><a href="dashboard.php" class="back-link">⬅ Back to Admin Dashboard</a>
    
</div>
<?php include('../includes/footer.php'); ?>
<script>
// Charts Data
const leaderboardData = <?php echo json_encode($leaderboardData); ?>;
const resourceData = <?php echo json_encode($resourceData); ?>;
const eventData = <?php echo json_encode($eventData); ?>;

// Top Students Chart
new Chart(document.getElementById('leaderboardChart'), {
    type: 'bar',
    data: {
        labels: leaderboardData.map(d => d.name),
        datasets: [{ label: 'Points', data: leaderboardData.map(d => d.total_points), backgroundColor: 'rgba(54, 162, 235, 0.7)' }]
    },
    options: { responsive: true }
});

// Resources Chart
new Chart(document.getElementById('resourceChart'), {
    type: 'bar',
    data: {
        labels: resourceData.map(d => d.title),
        datasets: [{ label: 'Usage Count', data: resourceData.map(d => d.usage_count), backgroundColor: 'rgba(75, 192, 192, 0.7)' }]
    },
    options: { responsive: true }
});

// Events Chart
new Chart(document.getElementById('eventChart'), {
    type: 'pie',
    data: {
        labels: eventData.map(d => d.title),
        datasets: [{
            label: 'Registrations',
            data: eventData.map(d => d.registrations),
            backgroundColor: [
                'rgba(255, 99, 132, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(153, 102, 255, 0.7)'
            ]
        }]
    },
    options: { responsive: true }
});
</script>

</body>
</html>
