<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
$user_id = $_SESSION['user_id'];

// Register for Event + Award Points
if (isset($_GET['register'])) {
    $event_id = $_GET['register'];
    $checkStmt = $conn->prepare("SELECT * FROM event_registrations WHERE user_id = ? AND event_id = ?");
    $checkStmt->bind_param("ii", $user_id, $event_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
        $errorMsg = "❌ You are already registered for this event.";
    } else {
        $insertStmt = $conn->prepare("INSERT INTO event_registrations (user_id, event_id) VALUES (?, ?)");
        $insertStmt->bind_param("ii", $user_id, $event_id);
        if ($insertStmt->execute()) {
            $successMsg = "✅ Registered for Event!";
            $analyticsStmt = $conn->prepare("INSERT INTO analytics (user_id, event_id, action, points) VALUES (?, ?, 'registered_event', 20)");
            $analyticsStmt->bind_param("ii", $user_id, $event_id);
            $analyticsStmt->execute();
        } else {
            $errorMsg = "❌ Error registering for event: " . $conn->error;
        }
    }
}

// Get Events
$events = $conn->query("SELECT * FROM events ORDER BY event_date ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>📅 Events - Student Hub</title>
    <!-- <link rel="stylesheet" href="../assets/css/events.css"> -->
     <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@700&family=Roboto:wght@400;500&display=swap');
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  background: linear-gradient(135deg, #0f172a 0%, #111827 100%);
  color: red;
  font-family: 'Roboto', sans-serif;
  min-height: 100vh;
  padding: 28px;
}

.container {
  max-width: 1100px;
  margin: 36px auto;
  background: rgba(255,255,255,0.03);
  padding: 30px;
  border-radius: 16px;
  box-shadow: 0 12px 40px rgba(2,6,23,0.65);
  border: 1px solid rgba(255,255,255,0.04);
  backdrop-filter: blur(8px);
}

.page-title {
  text-align: center;
  font-size: 2rem;
  font-weight: 800;
  margin-bottom: 22px;
  background: linear-gradient(90deg, #06b6d4, #6366f1);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  font-family: 'Poppins', sans-serif;
  color: unset;
}

.success-msg, .error-msg {
  text-align: center;
  padding: 12px 16px;
  border-radius: 10px;
  width: fit-content;
  margin: 8px auto 18px auto;
  font-weight: 700;
  box-shadow: 0 6px 18px rgba(0,0,0,0.2);
}

.success-msg {
  background: rgba(16,185,129,0.12);
  color: #bbf7d0;
}
.error-msg {
  background: rgba(239,68,68,0.12);
  color: #fecaca;
}

.events-table {
  width: 100%;
  border-collapse: collapse;
  margin: 18px auto;
  background: rgba(232, 213, 213, 0.02);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 30px rgba(2, 6, 23, 0.6);
}

.events-table th {
  padding: 14px 18px;
  text-align: left;
  background: linear-gradient(90deg, #075985, #7c3aed);
  color: #fff;
  font-weight: 700;
  font-size: 1rem;
  white-space: nowrap;
  font-family: 'Poppins', sans-serif;
}

.events-table td {
  padding: 14px 18px;
  font-size: 0.95rem;
  color: #e2e8f0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  vertical-align: top;
}

.events-table tbody tr:nth-child(even) {
  background: rgba(239, 228, 228, 0.03);
}

.events-table tbody tr:hover {
  background: rgba(99, 102, 241, 0.15);
  transition: all 0.25s ease;
}

.register-btn {
  display: inline-block;
  padding: 8px 14px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.9rem;
  text-decoration: none;
  color: #061826;
  background: linear-gradient(90deg, #06b6d4, #0891b2);
  box-shadow: 0 6px 18px rgba(6, 182, 212, 0.15);
  transition: all 0.25s ease;
  border: none;
}

.register-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 28px rgba(6, 182, 212, 0.25);
}

.no-events {
  text-align: center;
  color: #94a3b8;
  margin: 20px 0;
  font-size: 1.1rem;
}

.back-link { text-align: center; margin-top: 20px; }
.back-link a {
  color: #93c5fd;
  font-weight: 700;
  text-decoration: none;
  background: none;
  padding: 0;
}
.back-link a:hover { color: #facc15; }

@media (max-width: 700px) {
  .events-table thead { display: none; }
  .events-table tbody tr {
    display: block;
    margin-bottom: 16px;
    border-radius: 12px;
    background: rgba(255,255,255,0.03);
    padding: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.4);
  }
  .events-table tbody td {
    display: block;
    width: 100%;
    padding: 10px 12px;
    font-size: 0.95rem;
    border-bottom: none;
    color: #e2e8f0;
  }
  .events-table tbody td::before {
    content: attr(data-label);
    font-weight: 700;
    color: #93c5fd;
    display: block;
    margin-bottom: 4px;
    font-size: 0.85rem;
  }
}

     </style>
</head>
<body>
<div class="container">

    <h1 class="page-title">📅 Upcoming Events</h1>

    <?php if (!empty($successMsg)) echo "<p class='success-msg'>$successMsg</p>"; ?>
    <?php if (!empty($errorMsg)) echo "<p class='error-msg'>$errorMsg</p>"; ?>

    <?php if ($events->num_rows > 0): ?>
        <table class="events-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $events->fetch_assoc()): ?>
                <tr>
                    <td data-label="Title"><?= htmlspecialchars($row['title']); ?></td>
                    <td data-label="Description"><?= htmlspecialchars($row['description']); ?></td>
                    <td data-label="Date"><?= htmlspecialchars($row['event_date']); ?></td>
                    <td data-label="Location"><?= htmlspecialchars($row['location']); ?></td>
                    <td data-label="Action">
                        <a class="register-btn" href="events.php?register=<?= $row['event_id']; ?>">Register</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-events">❌ No events available.</p>
    <?php endif; ?>

    <div class="back-link">
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>
