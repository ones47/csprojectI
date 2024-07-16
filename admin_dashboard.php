<?php
// Include database connection
include 'db_connect.php';
session_start();

// Start session
session_start();

// Check if user is logged in and is an administrator
if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['designation'] === 'administrator')) {
    // Redirect to login page or error page
    header("location: index.php"); // Redirect to your login page
    exit;
}

$staffID = $_SESSION['staffID'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <!-- Topbar Start-->
    <div class="topbar">
        <div class="topbar-left">
            <img src="img/logo.png" alt="School Logo" class="logo">
            <span class="school-name">St Charles Lwanga</span>
        </div>
    </div>
    <!-- Topbar End-->
    <div class="sidebar" id="mySidebar">
        <div id="list-container">
            <ul>
                <li><a href="register_student.php">Add Students</a></li>
                <li><a href="register_teacher.php">Add Teacher</a></li>
                <li><a href="teacher_assignments.php">Assign Teacher</a></li>
                <li><a href="create_class.php">Add New Class</a></li>
                <li><a href="create_exam.php">Add Exam</a></li>
                <li><a href="exam.php">Add Test</a></li>
                <li><a href="class_details.php">View Class</a></li>
                <li><a href="view_teachers.php">View Teachers</a></li>
                <li><a href="adjust_exam.php">View Exams</a><li>
                <li><a href="view_exam.php">View Tests</a></li>
                <li><a href="account.php">Account</a></li>
                <li>
                    <form action="logout.php" method="POST">
                        <button type="submit" class="logout-button">LOG OUT</button>
                    </form>
                </li>
                <!-- Add more list items if needed -->
            </ul>
        </div>
    </div>

    <div class="main" id="mainContent">
        <!-- Add your main content here -->
    </div>
</body>
</html>
