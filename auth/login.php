<?php
session_start();
include("../config/db.php");

$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    // ✅ Secure query using prepared statements
    $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // ✅ Verify hashed password
        if (password_verify($pass, $user['password'])) {
            // Start session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            // ✅ Update last login
            $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $update->bind_param("i", $user['user_id']);
            $update->execute();

            // Redirect user based on role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../student/dashboard.php");
            }
            exit();
        } else {
            $error = "❌ Invalid password!";
        }
    } else {
        $error = "❌ User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Student Hub</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Global -->
    <link rel="stylesheet" href="../assets/css/login.css"> <!-- Page-specific -->
</head>
<body>
    <div class="login-container">
        <h1>🔐 Login</h1>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <p class="switch-link">
            Don’t have an account? <a href="register.php">Register</a>
        </p>
    </div>
</body>
</html>
