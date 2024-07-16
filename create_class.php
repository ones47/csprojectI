<?php
// Include the database connection file
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $class_id = $_POST['class_id'] ?? null;

    // Validate that class ID is provided
    if ($class_id) {
        // Check if the class ID already exists
        $check_query = "SELECT COUNT(*) FROM classes WHERE classID = ?";
        $stmt_check = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt_check, "s", $class_id);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_bind_result($stmt_check, $count);
        mysqli_stmt_fetch($stmt_check);
        mysqli_stmt_close($stmt_check);

        if ($count > 0) {
            echo "Error: Class ID already exists.";
        } else {
            // Insert data into the classes table
            $insert_query = "INSERT INTO classes (classID) VALUES (?)";
            $stmt_insert = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt_insert, "s", $class_id);

            if (mysqli_stmt_execute($stmt_insert)) {
                echo "Class added successfully!";
            } else {
                echo "Error: " . mysqli_stmt_error($stmt_insert);
            }

            mysqli_stmt_close($stmt_insert);
        }
    } else {
        echo "Class ID is required.";
    }

    // Close the database connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Class</title>
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
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="register_student.php">Add Students</a></li>
                <li><a href="register_teacher.php">Add Teacher</a></li>
                <li><a href="teacher_assignments.php">Assign Teacher</a></li>
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
        <div class="container">
            <h2>Create Class</h2>
            <form action="create_class.php" method="POST">
                <div class="form-group">
                    <label for="class_id">Class:</label>
                    <input type="text" id="class_id" name="class_id" required>
                </div>
                <button type="submit">Add Class</button>
            </form>
        </div>
    </div>
</body>
</html>
