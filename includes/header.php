<?php
// Ensure user session exists
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}
?>
<header style="display:flex; justify-content:space-between; align-items:center; padding:12px 30px; background:transparent; color:#f9fafb; position:sticky; top:0; z-index:1000;">
    <div style="display:flex; align-items:center; gap:12px;">
        <img src="../assets/uploads/generated-image.png" alt="Student Hub Logo" style="max-height:55px; width:auto; object-fit:contain; filter:drop-shadow(0 0 6px #6366f1); transition:filter 0.3s ease-in-out;" 
             onmouseover="this.style.filter='drop-shadow(0 0 10px #4f46e5)'" 
             onmouseout="this.style.filter='drop-shadow(0 0 6px #6366f1)'">
        <span style="font-size:1.4rem; font-weight:700; color:#06b6d4; letter-spacing:1px; transition: color 0.3s;" 
             
              onmouseout="this.style.color='#06b6d4'">Student Hub</span>
    </div>
    <nav style="display:flex; align-items:center;">
        <a href="profile.php" style="color:#f9fafb; text-decoration:none; margin-left:20px; font-weight:600; font-size:0.95rem; display:flex; align-items:center; gap:6px; transition: color 0.3s;" 
           onmouseover="this.style.color='#06b6d4'" 
           onmouseout="this.style.color='#f9fafb'">
            <i class="fa-solid fa-user" style="font-size:1rem;"></i> Profile
        </a>
        <a href="../auth/logout.php" style="color:#f87171; text-decoration:none; margin-left:20px; font-weight:600; font-size:0.95rem; display:flex; align-items:center; gap:6px; transition: color 0.3s;" 
           onmouseover="this.style.color='#ff4444'" 
           onmouseout="this.style.color='#f87171'">
            <i class="fa-solid fa-door-open" style="font-size:1rem;"></i> Logout
        </a>
    </nav>
</header>
