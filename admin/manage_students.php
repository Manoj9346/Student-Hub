<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include("../config/db.php");

// ---------- Helpers ----------
function clean($conn, $val) {
    return $conn->real_escape_string(trim($val));
}

$status = "";

// ---------- Add Student ----------
if (isset($_POST['add_student'])) {
    $name    = clean($conn, $_POST['name'] ?? '');
    $email   = clean($conn, $_POST['email'] ?? '');
    $password= $_POST['password'] ?? '';
    $branch  = clean($conn, $_POST['branch'] ?? '');
    $year    = intval($_POST['year'] ?? 1);
    $skills  = clean($conn, $_POST['skills'] ?? '');

    if ($name && $email && $password) {
        // ✅ Securely hash the password
        $passwordToStore = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (name, email, password, role, branch, year, skills)
                VALUES ('$name', '$email', '$passwordToStore', 'student', '$branch', '$year', '$skills')";
        if ($conn->query($sql)) {
            $status = "✅ Student added successfully.";
        } else {
            $status = "❌ Error adding student: " . $conn->error;
        }
    } else {
        $status = "❌ Name, Email and Password are required.";
    }
}

// ---------- Update Student ----------
if (isset($_POST['update_student'])) {
    $uid     = intval($_POST['user_id'] ?? 0);
    $name    = clean($conn, $_POST['name'] ?? '');
    $email   = clean($conn, $_POST['email'] ?? '');
    $branch  = clean($conn, $_POST['branch'] ?? '');
    $year    = intval($_POST['year'] ?? 1);
    $skills  = clean($conn, $_POST['skills'] ?? '');
    $password= $_POST['password'] ?? '';

    if ($uid > 0 && $name && $email) {
        if ($password !== '') {
            // ✅ Re-hash password if changed
            $passwordToStore = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET 
                        name='$name',
                        email='$email',
                        branch='$branch',
                        year='$year',
                        skills='$skills',
                        password='$passwordToStore'
                    WHERE user_id=$uid AND role='student'";
        } else {
            $sql = "UPDATE users SET 
                        name='$name',
                        email='$email',
                        branch='$branch',
                        year='$year',
                        skills='$skills'
                    WHERE user_id=$uid AND role='student'";
        }
        if ($conn->query($sql)) {
            $status = "✅ Student updated successfully.";
        } else {
            $status = "❌ Error updating student: " . $conn->error;
        }
    } else {
        $status = "❌ Name and Email are required.";
    }
}

// ---------- Delete Student ----------
if (isset($_GET['delete'])) {
    $student_id = intval($_GET['delete']);
    if ($student_id > 0) {
        $conn->query("DELETE FROM users WHERE user_id=$student_id AND role='student'");
        header("Location: manage_students.php?msg=deleted");
        exit();
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $status = "🗑️ Student deleted.";
}

// ---------- Fetch Students ----------
$students = $conn->query("SELECT user_id, name, email, branch, year, skills, created_at 
                          FROM users 
                          WHERE role='student' 
                          ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students</title>
    <link rel="stylesheet" href="../assets/css/manage_student.css?v=10">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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

    <h1 style="color:#5eeedb ; font-size:1.5rem; font-weight:600; margin: 1rem 0 1rem 0; text-align:center;"  >👩‍🎓 Manage Students</h1>

     
    
    <?php if (!empty($status)): ?>
        <div class="status-banner"><?= htmlspecialchars($status) ?></div>
    <?php endif; ?>

    <!-- Add / Edit Form -->
    <div class="card">
        <div class="card-header">
            <h3 id="form-title">➕ Add New Student</h3>
            <button type="button" class="btn ghost" id="btn-reset" title="Reset form" aria-label="Reset form">
                <i class="fa-solid fa-rotate-left"></i>
            </button>
        </div>

        <form method="post" class="student-form" id="student-form" autocomplete="off">
            <input type="hidden" name="user_id" id="user_id">
            <div class="form-row">
                <div class="field">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" placeholder="Student Name" required>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="email@example.com" required>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label for="branch">Branch</label>
                    <input type="text" name="branch" id="branch" placeholder="CSE / ECE / ME ...">
                </div>
                <div class="field">
                    <label for="year">Year</label>
                    <input type="number" name="year" id="year" min="1" max="8" placeholder="1-8">
                </div>
                <div class="field">
                    <label for="password">Password <span class="muted" id="pwd-hint">(required for Add, leave empty to keep existing)</span></label>
                    <input type="text" name="password" id="password" placeholder="Set/Update Password">
                </div>
            </div>

            <div class="form-row">
                <div class="field full">
                    <label for="skills">Skills</label>
                    <input type="text" name="skills" id="skills" placeholder="e.g. Java, Python, ML">
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn primary" name="add_student" id="btn-add">
                    <i class="fa-solid fa-user-plus"></i> Add Student
                </button>
                <button type="submit" class="btn success hidden" name="update_student" id="btn-update">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
                <button type="button" class="btn ghost hidden" id="btn-cancel-edit">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </button>
            </div>
        </form>
    </div>

    <!-- Controls -->
    <div class="card compact">
        <div class="controls">
            <div class="search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="table-search" placeholder="Search by name or email...">
            </div>
            <div class="hint">Tip: Click <b>Edit</b> to load a student into the form.</div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card">
        <div class="card-header">
            <h3>Student Accounts</h3>
        </div>

        <div class="table-wrap">
            <table class="styled-table" id="students-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Branch</th>
                        <th>Year</th>
                        <th>Skills</th>
                        <th>Joined</th>
                        <th class="center">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $i = 1;
                if ($students && $students->num_rows > 0):
                    while ($row = $students->fetch_assoc()):
                ?>
                    <tr data-id="<?= (int)$row['user_id'] ?>"
                        data-name="<?= htmlspecialchars($row['name']) ?>"
                        data-email="<?= htmlspecialchars($row['email']) ?>"
                        data-branch="<?= htmlspecialchars($row['branch']) ?>"
                        data-year="<?= (int)$row['year'] ?>"
                        data-skills="<?= htmlspecialchars($row['skills']) ?>">
                        <td data-label="#"> <?= $i++ ?></td>
                        <td data-label="Name"><?= htmlspecialchars($row['name']) ?></td>
                        <td data-label="Email"><?= htmlspecialchars($row['email']) ?></td>
                        <td data-label="Branch"><?= htmlspecialchars($row['branch']) ?></td>
                        <td data-label="Year"><?= (int)$row['year'] ?></td>
                        <td data-label="Skills" class="skills-cell"><?= htmlspecialchars($row['skills']) ?></td>
                        <td data-label="Joined"><?= htmlspecialchars($row['created_at']) ?></td>
                        <td class="center" data-label="Action">
                            <button class="btn btn-edit" type="button" title="Edit this student">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>
                            <a href="?delete=<?= (int)$row['user_id'] ?>"
                               class="btn btn-danger"
                               onclick="return confirm('Delete this student permanently?')"
                               title="Delete student">
                                <i class="fa-solid fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr><td colspan="8" class="no-data">No students found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
// ---- Client-side search ----
const searchInput = document.getElementById('table-search');
const table = document.getElementById('students-table');
searchInput.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(r => {
        const name = (r.dataset.name || '').toLowerCase();
        const email = (r.dataset.email || '').toLowerCase();
        r.style.display = (name.includes(q) || email.includes(q)) ? '' : 'none';
    });
});

// ---- Edit flow: load row into form ----
const form = document.getElementById('student-form');
const formTitle = document.getElementById('form-title');
const btnAdd = document.getElementById('btn-add');
const btnUpdate = document.getElementById('btn-update');
const btnCancel = document.getElementById('btn-cancel-edit');
const btnReset = document.getElementById('btn-reset');

function toEditMode(row) {
    document.getElementById('user_id').value = row.dataset.id || '';
    document.getElementById('name').value   = row.dataset.name || '';
    document.getElementById('email').value  = row.dataset.email || '';
    document.getElementById('branch').value = row.dataset.branch || '';
    document.getElementById('year').value   = row.dataset.year || '';
    document.getElementById('skills').value = row.dataset.skills || '';
    document.getElementById('password').value = '';

    formTitle.textContent = '✏️ Edit Student';
    btnAdd.classList.add('hidden');
    btnUpdate.classList.remove('hidden');
    btnCancel.classList.remove('hidden');

    document.getElementById('pwd-hint').textContent = '(leave empty to keep existing)';
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
function toAddMode() {
    form.reset();
    document.getElementById('user_id').value = '';
    formTitle.textContent = '➕ Add New Student';
    btnAdd.classList.remove('hidden');
    btnUpdate.classList.add('hidden');
    btnCancel.classList.add('hidden');
    document.getElementById('pwd-hint').textContent = '(required for Add, leave empty to keep existing)';
}

table.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-edit');
    if (!btn) return;
    const row = e.target.closest('tr');
    if (!row) return;
    toEditMode(row);
});

btnCancel.addEventListener('click', toAddMode);
btnReset.addEventListener('click', toAddMode);
</script>
<?php include('../includes/footer.php'); ?>
</body>
</html>
