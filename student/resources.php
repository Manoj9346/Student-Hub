<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
$user_id = $_SESSION['user_id'];

// Save to My Resources + log points
if (isset($_GET['save'])) {
    $resource_id = $_GET['save'];
    $sql = "INSERT INTO my_resources (user_id, resource_id) VALUES ('$user_id','$resource_id')";
    if ($conn->query($sql)) {
        $message = "✅ Resource Saved to My Resources!";
        $conn->query("INSERT INTO analytics (user_id, resource_id, action, points) VALUES ('$user_id','$resource_id','saved',10)");
    } else {
        $message = "❌ Error: " . $conn->error;
    }
}

// Log resource view
if (isset($_GET['view'])) {
    $resource_id = $_GET['view'];
    $conn->query("INSERT INTO analytics (user_id, resource_id, action, points) VALUES ('$user_id','$resource_id','viewed',5)");
    $fileRes = $conn->query("SELECT file_path FROM resources WHERE resource_id='$resource_id'");
    if ($fileRes->num_rows > 0) {
        $f = $fileRes->fetch_assoc();
        header("Location: ../assets/uploads/".$f['file_path']);
        exit();
    }
}

// Fetch resources
$res = $conn->query("SELECT * FROM resources ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>📚 Available Resources - Student Hub</title>
    <!-- <link rel="stylesheet" href="../assets/css/resources.css"> -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@700&family=Roboto:wght@400;500&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #f9fafb;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            padding: 25px;
            background: rgba(255,255,255,0.02);
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.5);
        }

        .page-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 30px;
            letter-spacing: 0.2px;
            font-family: 'Poppins', sans-serif;
        }
        .page-title .star {
            font-size: 2.4rem;
            margin-right: 8px;
            display: inline-block;
            vertical-align: middle;
            /* NO gradient or text-fill on emoji! */
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.35));
        }
        .page-title .title-text {
            background: linear-gradient(90deg,#6366f1,#06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            color: unset;
            vertical-align: middle;
            display: inline-block;
        }

        .alert-msg {
            text-align: center;
            padding: 10px;
            background: rgba(16,185,129,0.3);
            color: #10b981;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .resources-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        }
        .resources-table th, .resources-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            font-size: 14px;
            color: #f9fafb;
        }
        .resources-table th {
            background: rgba(99,102,241,0.8);
            color: #f9fafb;
        }
        .resources-table tr:hover td {
            background: rgba(99,102,241,0.25);
            color: #f9fafb;
            transition: 0.3s ease;
        }
        .btn-view, .btn-save {
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: 0.3s;
            display: inline-block;
        }
        .btn-view {
            background: rgba(59,130,246,0.8);
            color: #f9fafb;
        }
        .btn-view:hover {
            background: rgba(59,130,246,1);
        }
        .btn-save {
            background: rgba(16,185,129,0.8);
            color: #f9fafb;
        }
        .btn-save:hover {
            background: rgba(16,185,129,1);
        }
        .resources-table td a {
            color: #fff !important;
        }
        .no-data {
            text-align: center;
            color: #f87171;
            font-weight: 600;
        }
        .back-link {
            text-align: center;
        }
        .back-link a {
            color: #93c5fd;
            font-weight: 700;
            text-decoration: none;
        }
        .back-link a:hover {
            color: #facc15;
        }
        @media (max-width: 768px) {
            .resources-table, .resources-table thead,
            .resources-table tbody, .resources-table th,
            .resources-table td, .resources-table tr {
                display: block;
                width: 100%;
            }
            .resources-table tr {
                margin-bottom: 15px;
                border-bottom: 2px solid rgba(255,255,255,0.1);
                display: block;
                padding: 10px;
            }
            .resources-table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }
            .resources-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                text-align: left;
                font-weight: 600;
                color: #93c5fd;
            }
        }
    </style>
</head>
<body>
<div class="container">

    <h1 class="page-title">
        <span class="star">📚</span>
        <span class="title-text">Available Resources</span>
    </h1>

    <?php if (isset($message)): ?>
        <p class="alert-msg"><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if ($res && $res->num_rows > 0): ?>
        <table class="resources-table">
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Type</th>
                <th>Subject</th>
                <th>Semester</th>
                <th>File</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $res->fetch_assoc()): ?>
                <tr>
                    <td data-label="Title"><?= htmlspecialchars($row['title']); ?></td>
                    <td data-label="Description"><?= htmlspecialchars($row['description']); ?></td>
                    <td data-label="Type"><?= htmlspecialchars($row['type']); ?></td>
                    <td data-label="Subject"><?= htmlspecialchars($row['subject']); ?></td>
                    <td data-label="Semester"><?= htmlspecialchars($row['semester']); ?></td>
                    <td data-label="File"><a class="btn-view" href="resources.php?view=<?= $row['resource_id']; ?>" target="_blank">View</a></td>
                    <td data-label="Action"><a class="btn-save" href="resources.php?save=<?= $row['resource_id']; ?>">Save</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p class="no-data">❌ No resources available.</p>
    <?php endif; ?>

    <div class="back-link">
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>
