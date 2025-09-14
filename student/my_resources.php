<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
$user_id = $_SESSION['user_id'];

// ✅ Track download action
if (isset($_GET['download'])) {
    $resource_id = $_GET['download'];
    $conn->query("INSERT INTO analytics (user_id, resource_id, action, points) 
                  VALUES ('$user_id','$resource_id','downloaded',15)");

    $resFile = $conn->query("SELECT file_path FROM resources WHERE resource_id='$resource_id'");
    if ($resFile->num_rows > 0) {
        $f = $resFile->fetch_assoc();
        header("Location: ../assets/uploads/".$f['file_path']);
        exit();
    }
}

// ✅ Track completion action
if (isset($_GET['complete'])) {
    $resource_id = $_GET['complete'];
    // Prevent duplicate "completed" actions
    $check = $conn->query("SELECT * FROM analytics WHERE user_id='$user_id' AND resource_id='$resource_id' AND action='completed'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO analytics (user_id, resource_id, action, points) 
                      VALUES ('$user_id','$resource_id','completed',20)");
    }
    $message = "🎉 Resource marked as Completed (+20 points)";
}

// ✅ Fetch Saved Resources
$sql = "SELECT r.resource_id, r.title, r.description, r.type, r.subject
        FROM my_resources m 
        JOIN resources r ON m.resource_id = r.resource_id
        WHERE m.user_id='$user_id'";
$res = $conn->query($sql);

// ✅ Fetch completed resources for this user
$completedRes = [];
$cq = $conn->query("SELECT DISTINCT resource_id FROM analytics WHERE user_id='$user_id' AND action='completed'");
while ($c = $cq->fetch_assoc()) {
    $completedRes[] = $c['resource_id'];
}

// ✅ Progress bar counts
$total = $res ? $res->num_rows : 0;
$completed = count($completedRes);
$progressPercent = ($total > 0) ? round(($completed / $total) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>⭐ My Saved Resources - Student Hub</title>
    <link rel="stylesheet" href="../assets/css/my_resources.css"> <!-- page-specific -->
</head>
<body>
<div class="container">
<h1 class="page-title" style="text-align:center"><span class="star">⭐</span> My Saved Resources</h1>

    <?php if (isset($message)): ?>
        <p class="success-msg"><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if ($total > 0): ?>
        <!-- ✅ Progress Bar -->
        <div class="progress-box">
            <div class="meta">
                <span><strong>Progress:</strong> <?= $completed; ?> / <?= $total; ?> completed</span>
                <span><?= $progressPercent; ?>%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $progressPercent; ?>%;"></div>
            </div>
        </div>

        <div class="table-wrap">
        <table class="resources-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Subject</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $res->data_seek(0);
            while ($row = $res->fetch_assoc()): 
                $isCompleted = in_array($row['resource_id'], $completedRes);
            ?>
                <tr class="<?= $isCompleted ? 'completed' : ''; ?>">
                    <td data-label="Title"><?= htmlspecialchars($row['title']); ?></td>
                    <td data-label="Description"><?= htmlspecialchars($row['description']); ?></td>
                    <td data-label="Type"><?= htmlspecialchars($row['type']); ?></td>
                    <td data-label="Subject"><?= htmlspecialchars($row['subject']); ?></td>
                    <td data-label="Actions">
                        <a class="btn-download" href="my_resources.php?download=<?= $row['resource_id']; ?>">⬇ Download</a>
                        <?php if (!$isCompleted): ?>
                            <a class="btn-complete" href="my_resources.php?complete=<?= $row['resource_id']; ?>">✅ Complete</a>
                        <?php else: ?>
                            <span class="badge-completed">✔ Completed</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <p class="no-data">❌ No resources saved yet.</p>
    <?php endif; ?>

    <div class="back-link">
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>
