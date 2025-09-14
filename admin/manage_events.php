<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");

// ✅ Add Event
if (isset($_POST['add_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $location = $_POST['location'];
    $created_by = $_SESSION['user_id'];

    // Check if event date is in the past
    $today = date("Y-m-d");
    if ($event_date < $today) {
        $msg = "❌ Cannot add an event in the past. Please choose a future date.";
    } else {
        $sql = "INSERT INTO events (title, description, event_date, location, created_by) 
                VALUES ('$title','$description','$event_date','$location','$created_by')";
        if ($conn->query($sql)) {
            $msg = "✅ Event Added Successfully!";
        } else {
            $msg = "❌ Error: " . $conn->error;
        }
    }
}

// ✅ Delete Event
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM events WHERE event_id=$id";
    if ($conn->query($sql)) {
        $msg = "🗑 Event Deleted!";
    }
}

// Fetch events
$res = $conn->query("SELECT * FROM events ORDER BY event_date ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Events</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/manage_events.css">
</head>
<body>
<div class="container">
    
    <!-- Top Nav -->
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
<h1 style="color:#5eeedb ; font-size:1.5rem; font-weight:600; margin: 1rem 0 1rem 0; text-align:center;"  >📅 Manage Events</h1>


    <?php if (isset($msg)) { ?>
        <div class="card success">
            <?= $msg ?>
        </div>
    <?php } ?>

    <!-- Add Event -->
    <div class="card">
        <h3>➕ Add New Event</h3>
        <form method="post">
            <input type="text" name="title" placeholder="Event Title" required>
            <textarea name="description" placeholder="Event Description"></textarea>
            <input type="date" name="event_date" required>
            <input type="text" name="location" placeholder="Event Location" required>
            <button type="submit" name="add_event">Add Event</button>
        </form>
    </div>

    <!-- Event List -->
    <div class="card">
        <h3>📋 All Events</h3>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $res->fetch_assoc()) { ?>
                <tr>
                    <td data-label="ID"><?= $row['event_id'] ?></td>
                    <td data-label="Title"><?= htmlspecialchars($row['title']) ?></td>
                    <td data-label="Date"><?= htmlspecialchars($row['event_date']) ?></td>
                    <td data-label="Location"><?= htmlspecialchars($row['location']) ?></td>
                    <td data-label="Action">
                        <a href="?delete=<?= $row['event_id'] ?>" class="btn-danger"
                           onclick="return confirm('Delete this event?')">Delete</a>
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
