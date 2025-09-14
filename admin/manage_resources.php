<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include("../config/db.php");

// ✅ Add Resource
if (isset($_POST['add_resource'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $subject = $_POST['subject'];
    $semester = $_POST['semester'];
    $uploaded_by = $_SESSION['user_id'];

    // File upload
    $file = $_FILES['file']['name'];
    $target = "../assets/uploads/" . basename($file);
    move_uploaded_file($_FILES['file']['tmp_name'], $target);

    $sql = "INSERT INTO resources (title, description, type, subject, semester, file_path, uploaded_by) 
            VALUES ('$title','$description','$type','$subject','$semester','$file','$uploaded_by')";
    if ($conn->query($sql)) {
        $msg = "✅ Resource Added Successfully!";
    } else {
        $msg = "❌ Error: " . $conn->error;
    }
}

// ✅ Delete Resource
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM resources WHERE resource_id=$id";
    if ($conn->query($sql)) {
        $msg = "🗑 Resource Deleted!";
    }
}

// Fetch resources
$res = $conn->query("SELECT * FROM resources ORDER BY resource_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Resources</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/manage_resources.css">
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

    <h1 style="color:#5eeedb ; font-size:1.5rem; font-weight:600; margin: 1rem 0 1rem 0; text-align:center;"  >📚 Manage Resources</h1>


    <?php if (isset($msg)) { ?>
        <div class="card success">
            <?= $msg ?>
        </div>
    <?php } ?>

    <!-- Add Resource -->
    <div class="card">
        <h3>➕ Add New Resource</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Resource Title" required>
            <textarea name="description" placeholder="Description"></textarea>

            <label>Type:</label>
            <select name="type">
                <option value="note">Note</option>
                <option value="ebook">E-Book</option>
                <option value="tutorial">Tutorial</option>
                <option value="project">Project</option>
                <option value="tool">Tool</option>
                <option value="event">Event</option>
            </select>

            <input type="text" name="subject" placeholder="Subject">
            <input type="number" name="semester" placeholder="Semester">
            <input type="file" name="file" required>

            <button type="submit" name="add_resource">Add Resource</button>
        </form>
    </div>

    <!-- Resource List -->
    <div class="card">
        <h3>📄 All Resources</h3>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Subject</th>
                    <th>Semester</th>
                    <th>File</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $res->fetch_assoc()) { ?>
                <tr>
                    <td data-label="ID"><?= $row['resource_id'] ?></td>
                    <td data-label="Title"><?= htmlspecialchars($row['title']) ?></td>
                    <td data-label="Type"><?= htmlspecialchars($row['type']) ?></td>
                    <td data-label="Subject"><?= htmlspecialchars($row['subject']) ?></td>
                    <td data-label="Semester"><?= htmlspecialchars($row['semester']) ?></td>
                    <td data-label="File">
                        <a href="../assets/uploads/<?= $row['file_path'] ?>" target="_blank" class="btn-view">View</a>
                    </td>
                    <td data-label="Action">
                        <a href="?delete=<?= $row['resource_id'] ?>" class="btn-danger"
                           onclick="return confirm('Delete this resource?')">Delete</a>
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
