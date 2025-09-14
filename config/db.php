<?php
$host = "localhost";
$user = "root";       // default XAMPP user
$pass = "";           // default XAMPP password (empty)
$db   = "studenthub"; // your DB name

$conn = new mysqli('localhost', 'root', '', 'studenthub', 3307);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
// else {
//      echo "Database connected successfully";
// }
?>
