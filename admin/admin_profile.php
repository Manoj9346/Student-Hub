<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");

$admin_id = $_SESSION['user_id'];

// Fetch current admin data
$result = $conn->query("SELECT * FROM users WHERE user_id='$admin_id' LIMIT 1");
$admin = $result->fetch_assoc();

// Update profile
if (isset($_POST['update'])) {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // Profile picture
    $profile_pic = $admin['profile_pic'];
    if (!empty($_FILES['profile_pic']['name'])) {
        $profile_pic = time() . "_" . basename($_FILES['profile_pic']['name']);
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], "../assets/uploads/" . $profile_pic);
    }

    // Build update query
    $update = "UPDATE users SET 
                name='$name',
                email='$email',
                phone='$phone',
                profile_pic='$profile_pic'";

    if (!empty($_POST['password'])) {
        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $update .= ", password='$hashedPassword'";
    }

    $update .= " WHERE user_id='$admin_id'";

    if ($conn->query($update)) {
        $_SESSION['msg'] = "✅ Profile Updated Successfully!";
        header("Location: admin_profile.php");
        exit();
    } else {
        $_SESSION['msg'] = "❌ Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile - Student Hub</title>
    <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>


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

    
<div class="container">
    <!-- Status Message -->
    <?php if (isset($_SESSION['msg'])): ?>
        <p class="status-msg"><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></p>
    <?php endif; ?>

    <!-- Profile Card -->
    <div class="profile-card">
        <div class="profile-pic">
            <?php if (!empty($admin['profile_pic'])): ?>
                <img src="../assets/uploads/<?= htmlspecialchars($admin['profile_pic']); ?>" alt="Profile Picture">
            <?php else: ?>
                <div class="default-pic">👤</div>
            <?php endif; ?>
        </div>
        <div class="profile-info">
            <p><b>Name:</b> <?= htmlspecialchars($admin['name']); ?></p>
            <p><b>Email:</b> <?= htmlspecialchars($admin['email']); ?></p>
            <p><b>Phone:</b> <?= htmlspecialchars($admin['phone'] ?? 'N/A'); ?></p>
            <p><b>Role:</b> <?= htmlspecialchars($admin['role']); ?></p>
            <p><b>Last Login:</b> <?= htmlspecialchars($admin['last_login'] ?? 'N/A'); ?></p>
            <p><b>Account Created:</b> <?= htmlspecialchars($admin['created_at'] ?? 'N/A'); ?></p>
        </div>
    </div>

    <hr>

    <!-- Update Profile Form -->
    <h2>✏️ Update Profile</h2>
    <form method="post" enctype="multipart/form-data" class="profile-form">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($admin['name']); ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']); ?>" required>

        <label>Phone Number</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($admin['phone'] ?? ''); ?>">

        <label>Profile Picture</label>
        <input type="file" name="profile_pic">

        <label>New Password (leave blank if not changing)</label>
        <input type="password" name="password" placeholder="Enter new password">

        <button type="submit" name="update">Update Profile</button>
    </form>

    <div class="back-link">
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>

</div>
<?php include('../includes/footer.php'); ?>
</body>
</html>
