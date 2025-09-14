<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");

$user_id = $_SESSION['user_id'];

// ✅ Fetch current profile data
$sql = "SELECT * FROM users WHERE user_id='$user_id' LIMIT 1";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// ✅ Update profile
if (isset($_POST['update'])) {
    $name    = trim($_POST['name']);
    $branch  = trim($_POST['branch']);
    $year    = $_POST['year'];
    $skills  = trim($_POST['skills']);
    $bio     = trim($_POST['bio']);
    $phone   = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // ✅ Normalize URLs function
    function normalize_url($url, $domain) {
        if (empty($url)) return "";
        $url = trim($url);
        $url = preg_replace('/\s+/', '', $url);
        if (!preg_match("~^https?://~i", $url)) {
            $url = "https://" . ltrim($url, '/');
        }
        if (strpos($url, $domain) === false) {
            return ""; // invalid, clear
        }
        return rtrim($url, "/");
    }

    // ✅ Social Links
    $linkedin = normalize_url($_POST['linkedin'], "linkedin.com");
    $github   = normalize_url($_POST['github'], "github.com");

    // ✅ Handle profile picture
    if (!empty($_FILES['profile_pic']['name'])) {
        $profile_pic = $_FILES['profile_pic']['name'];
        $target = "../assets/uploads/" . basename($profile_pic);
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target);
    } else {
        $profile_pic = $user['profile_pic']; // keep old one
    }

    // ✅ Update query
    $update = "UPDATE users SET 
                name='$name',
                branch='$branch',
                year='$year',
                skills='$skills',
                bio='$bio',
                phone='$phone',
                address='$address',
                linkedin='$linkedin',
                github='$github',
                profile_pic='$profile_pic'
               WHERE user_id='$user_id'";

    if ($conn->query($update)) {
        $_SESSION['msg'] = "✅ Profile Updated Successfully!";
        header("Location: profile.php");
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
    <title>👤 My Profile - Student Hub</title>

    <link rel="stylesheet" href="../assets/css/profile.css"> <!-- page-specific -->
</head>
<body>
<div class="container">
    <h1>👤 My Profile</h1>
    <?php if (isset($_SESSION['msg'])): ?>
        <p class="status-msg"><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></p>
    <?php endif; ?>

    <!-- Profile Card -->
    <div class="profile-card">
        <div class="profile-pic">
            <?php if ($user['profile_pic']) { ?>
                <img src="../assets/uploads/<?= htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture">
            <?php } else { ?>
                <div class="default-pic">👤</div>
            <?php } ?>
        </div>

        <div class="profile-info">
            <p><b>Name:</b> <?= htmlspecialchars($user['name']); ?></p>
            <p><b>Email:</b> <?= htmlspecialchars($user['email']); ?></p>
            <p><b>Phone:</b> <?= htmlspecialchars($user['phone']); ?></p>
            <p><b>Branch:</b> <?= htmlspecialchars($user['branch']); ?></p>
            <p><b>Year:</b> <?= htmlspecialchars($user['year']); ?></p>
            <p><b>Skills:</b> <?= htmlspecialchars($user['skills']); ?></p>
            <p><b>Bio:</b> <?= htmlspecialchars($user['bio']); ?></p>
            <p><b>Address:</b> <?= htmlspecialchars($user['address']); ?></p>
            <?php if ($user['linkedin']) { ?><p><b>LinkedIn:</b> <a href="<?= $user['linkedin'] ?>" target="_blank"><?= $user['linkedin'] ?></a></p><?php } ?>
            <?php if ($user['github']) { ?><p><b>GitHub:</b> <a href="<?= $user['github'] ?>" target="_blank"><?= $user['github'] ?></a></p><?php } ?>
        </div>
    </div>

    <hr>

    <!-- Update Form -->
    <h2>✏️ Update Profile</h2>
    <form method="post" enctype="multipart/form-data" class="profile-form">
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required>
        <input type="text" name="branch" value="<?= htmlspecialchars($user['branch']); ?>">
        <input type="number" name="year" value="<?= htmlspecialchars($user['year']); ?>">
        <textarea name="skills"><?= htmlspecialchars($user['skills']); ?></textarea>
        <textarea name="bio"><?= htmlspecialchars($user['bio']); ?></textarea>
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']); ?>" placeholder="Phone">
        <input type="text" name="address" value="<?= htmlspecialchars($user['address']); ?>" placeholder="Address">

        <input type="url" name="linkedin" value="<?= htmlspecialchars($user['linkedin']); ?>" placeholder="LinkedIn Profile">
        <input type="url" name="github" value="<?= htmlspecialchars($user['github']); ?>" placeholder="GitHub Profile">

        <input type="file" name="profile_pic">

        <button type="submit" name="update">Update Profile</button>
    </form>

    <div class="back-link">
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>

</div>
</body>
</html>
