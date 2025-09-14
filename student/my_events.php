<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
$user_id = $_SESSION['user_id'];

$sql = "SELECT e.title, e.description, e.event_date, e.location 
        FROM event_registrations er
        JOIN events e ON er.event_id = e.event_id
        WHERE er.user_id='$user_id'
        ORDER BY e.event_date ASC";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>⭐ My Events - Student Hub</title>
    <style>
        /* Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@700&family=Roboto:wght@400;500&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #111827 100%);
            color: #e6eef8;
            font-family: 'Roboto', 'Inter', 'Segoe UI', sans-serif;
            min-height: 100vh;
            padding: 28px;
        }

        .container {
            max-width: 1100px;
            margin: 36px auto;
            background: rgba(255, 255, 255, 0.03);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(2, 6, 23, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(8px);
        }

        .page-title {
            text-align: center;
            font-size: 2.2rem;
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            margin-bottom: 22px;
            background: linear-gradient(90deg, #06b6d4, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            color: #f8fafc; /* fallback */
            letter-spacing: 0.2px;
        }

        .success-msg,
        .error-msg {
            text-align: center;
            padding: 12px 16px;
            border-radius: 10px;
            width: fit-content;
            margin: 8px auto 18px auto;
            font-weight: 700;
        }
        .success-msg {
            background: rgba(16, 185, 129, 0.12);
            color: #bbf7d0;
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.08);
        }
        .error-msg {
            background: rgba(239, 68, 68, 0.12);
            color: #fecaca;
            box-shadow: 0 6px 18px rgba(239, 68, 68, 0.08);
        }

        .events-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(2, 6, 23, 0.6);
        }
        .events-table th, .events-table td {
            padding: 14px 18px;
            font-size: 0.95rem;
            text-align: left;
            vertical-align: middle;
        }
        .events-table th {
            background: linear-gradient(90deg, #075985, #7c3aed);
            color: #fff;
            font-weight: 700;
            white-space: nowrap;
            font-family: 'Poppins', sans-serif;
        }
        .events-table td {
            color: #dbeafe;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        }
        .events-table tbody tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.015);
        }
        .events-table tbody tr:hover {
            background: rgba(99, 102, 241, 0.1);
            transition: all 0.25s ease;
        }

        .no-data {
            text-align: center;
            color: #94a3b8;
            margin: 24px 0;
            font-size: 1.05rem;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #93c5fd;
            font-weight: 700;
            text-decoration: none;
        }
        .back-link a:hover {
            color: #facc15;
        }

        @media (max-width: 700px) {
            .events-table thead {
                display: none;
            }
            .events-table, .events-table tbody, .events-table tr, .events-table td {
                display: block;
                width: 100%;
            }
            .events-table tr {
                margin-bottom: 16px;
                border-radius: 12px;
                padding: 12px;
                background: rgba(255, 255, 255, 0.03);
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.4);
            }
            .events-table td {
                display: block;
                padding: 10px 12px;
                border-bottom: none;
                color: #e2e8f0;
            }
            .events-table td::before {
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
    <h1 class="page-title">⭐ My Events</h1>

    <?php if ($res && $res->num_rows > 0): ?>
        <table class="events-table">
            <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Date</th>
                <th>Location</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $res->fetch_assoc()): ?>
                <tr>
                    <td data-label="Title"><?= htmlspecialchars($row['title']); ?></td>
                    <td data-label="Description"><?= htmlspecialchars($row['description']); ?></td>
                    <td data-label="Date"><?= htmlspecialchars($row['event_date']); ?></td>
                    <td data-label="Location"><?= htmlspecialchars($row['location']); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">❌ You haven’t registered for any events yet.</p>
    <?php endif; ?>

    <div class="back-link">
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>
