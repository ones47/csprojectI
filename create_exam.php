<?php
// Include the database connection file
include 'db_connect.php';
session_start();

// Check if user is logged in and is an administrator
if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['designation'] === 'administrator')) {
    // Redirect to login page or error page
    header("location: index.php"); // Redirect to your login page
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $exam_name = $_POST['exam_name'] ?? null;

    // Validate that exam name is provided
    if ($exam_name) {
        // Insert data into the exams table
        $insert_query = "INSERT INTO exams (examName) VALUES (?)";
        $stmt_insert = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt_insert, "s", $exam_name);

        if (mysqli_stmt_execute($stmt_insert)) {
            echo "Exam added successfully!";
        } else {
            echo "Error: " . mysqli_stmt_error($stmt_insert);
        }

        mysqli_stmt_close($stmt_insert);
    } else {
        echo "Exam name is required.";
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
    <title>Create Exam</title>
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
                <li><a href="create_class.php">Add New Class</a></li>
                <li><a href="exam.php">Add Test</a></li>
                <li><a href="class_details.php">View Class</a></li>
                <li><a href="view_exam.php">View Exam</a></li>
                <li><a href="account.php">Account</a></li>
                <li><a href="view_teachers.php">View Teachers</a></li>
                <!-- Add more list items if needed -->
            </ul>
        </div>
        <div class="logout-button-container">
            <form action="logout.php" method="POST">
                <button type="submit">LOG OUT</button>
            </form>
        </div>
    </div>

    <div class="main" id="mainContent">
        <div class="container">
            <h2>Create Exam</h2>
            <form action="create_exam.php" method="POST">
                <div class="form-group">
                    <label for="exam_name">Exam Name:</label>
                    <input type="text" id="exam_name" name="exam_name" required>
                </div>
                <button type="submit">Add Exam</button>
            </form>
        </div>
    </div>
</body>
</html>
