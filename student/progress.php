<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}
include("../config/db.php");

$user_id = $_SESSION['user_id'];

// --- Points Over Time (last 30 days) ---
$pointsData = [];
$stmt = $conn->prepare("
    SELECT DATE(created_at) AS date, SUM(points) AS total_points
    FROM analytics 
    WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pointsData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// --- Resource Usage Breakdown ---
$resourceData = [];
$stmt = $conn->prepare("
    SELECT r.title, COUNT(a.resource_id) AS usage_count
    FROM analytics a
    JOIN resources r ON a.resource_id = r.resource_id
    WHERE a.user_id = ? AND a.resource_id IS NOT NULL
    GROUP BY a.resource_id
    ORDER BY usage_count DESC
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resourceData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// --- Event Participation ---
$eventData = [];
$stmt = $conn->prepare("
    SELECT e.title, COUNT(a.event_id) AS registrations
    FROM analytics a
    JOIN events e ON a.event_id = e.event_id
    WHERE a.user_id = ? AND a.action = 'registered_event'
    GROUP BY a.event_id
    ORDER BY registrations DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$eventData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>📈 My Progress - Student Hub</title>
    <link rel="stylesheet" href="../assets/css/progress.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div style="max-width: 800px; margin: auto; padding: 20px; height: 30%;">
    <h2 style="text-align: center; font-size: 30px; margin-bottom: 20px; color: white;">📈 My Progress Dashboard</h2>

    <div class="charts-wrapper">
        <div class="chart-card">
            <h2>🎯 Points Last 30 Days</h2>
            <div class="chart-container">
                <canvas id="pointsChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h2>📚 Most Used Resources</h2>
            <div class="chart-container">
                <canvas id="resourcesChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h2>📅 Event Participation</h2>
            <div class="chart-container">
                <canvas id="eventsChart"></canvas>
            </div>
        </div>
    </div>

    <div class="back-link">
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>

<script>
const pointsData = <?= json_encode($pointsData); ?>;
const resourceData = <?= json_encode($resourceData); ?>;
const eventData = <?= json_encode($eventData); ?>;

// --- Points Over Time Chart ---
new Chart(document.getElementById('pointsChart'), {
    type: 'line',
    data: {
        labels: pointsData.map(d => d.date),
        datasets: [{
            label: 'Points',
            data: pointsData.map(d => d.total_points),
            borderColor: 'rgba(99,102,241,1)',
            backgroundColor: 'rgba(99,102,241,0.3)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { labels: { color: '#f9fafb' } } },
        scales: {
            x: { ticks: { color: '#f9fafb' }, grid: { color: 'rgba(255,255,255,0.1)' } },
            y: { ticks: { color: '#f9fafb' }, grid: { color: 'rgba(255,255,255,0.1)' } }
        }
    }
});

// --- Resource Usage Chart ---
new Chart(document.getElementById('resourcesChart'), {
    type: 'bar',
    data: {
        labels: resourceData.map(d => d.title),
        datasets: [{
            label: 'Usage Count',
            data: resourceData.map(d => d.usage_count),
            backgroundColor: 'rgba(16,185,129,0.7)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { labels: { color: '#f9fafb' } } },
        scales: {
            x: { ticks: { color: '#f9fafb' }, grid: { color: 'rgba(255,255,255,0.1)' } },
            y: { ticks: { color: '#f9fafb' }, grid: { color: 'rgba(255,255,255,0.1)' } }
        }
    }
});

// --- Event Participation Chart ---
const eventColors = eventData.map((_, i) => `hsl(${i*60 % 360}, 70%, 50%)`);

new Chart(document.getElementById('eventsChart'), {
    type: 'pie',
    data: {
        labels: eventData.map(d => d.title),
        datasets: [{
            label: 'Registrations',
            data: eventData.map(d => d.registrations),
            backgroundColor: eventColors
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { labels: { color: '#f9fafb' } } }
    }
});
</script>
</body>
</html>
