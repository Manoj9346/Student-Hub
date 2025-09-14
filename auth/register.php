<?php
include("../config/db.php");

$error = "";

if (isset($_POST['register'])) {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    // ✅ Check if email already exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $error = "⚠️ Email already registered!";
    } else {
        // ✅ Hash password securely
        $hashed_pass = password_hash($pass, PASSWORD_BCRYPT);

        // ✅ All new users are students by default
        $role = "student";

        // ✅ Use prepared statement for insert
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) 
                                VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $name, $email, $hashed_pass, $role);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            $error = "⚠️ Error: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Student Hub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/register.css">
</head>
<body>
    <div class="register-container">
        <h1>📝 Register</h1>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <!-- 🔒 Removed the role select box for security -->
            
            <button type="submit" name="register">Register</button>
        </form>

        <p class="switch-link">Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>
