<?php
// Include the database connection file
include 'db_connect.php';

// Fetch class IDs from the classes table
$class_query = "SELECT classID FROM classes";
$class_result = mysqli_query($conn, $class_query);
$classes = [];
while ($row = mysqli_fetch_assoc($class_result)) {
    $classes[] = $row['classID'];
}

// Fetch teacher usernames from the users table where designation is 'teacher'
$teacher_query = "SELECT username FROM users WHERE designation = 'teacher'";
$teacher_result = mysqli_query($conn, $teacher_query);
$teachers = [];
while ($row = mysqli_fetch_assoc($teacher_result)) {
    $teachers[] = $row['username'];
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $test_name = $_POST['test_name'] ?? null;
    $class_id = $_POST['class_id'] ?? null;
    $subject = $_POST['subject'] ?? null;
    $teacher_username = $_POST['teacher_username'] ?? null;
    $test_type = $_POST['test_type'] ?? null;

    // Validate all fields are filled
    if ($test_name && $class_id && $subject && $teacher_username && $test_type) {
        // Get the staffID for the selected teacher
        $staff_query = "SELECT staffID FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $staff_query);
        mysqli_stmt_bind_param($stmt, "s", $teacher_username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $staffID);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($staffID) {
            // Insert data into the tests table
            $insert_query = "INSERT INTO tests (testID, classID, subject, staffID, testtype) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            $test_id = uniqid(); // Generate a unique ID for the test
            mysqli_stmt_bind_param($stmt, "sssss", $test_id, $class_id, $subject, $staffID, $test_type);
            
            if (mysqli_stmt_execute($stmt)) {
                echo "Test added successfully!";
            } else {
                echo "Error: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Error: Teacher not found.";
        }
    } else {
        echo "All fields are required.";
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
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="register_student.php">Add Students</a></li>
                <li><a href="register_teacher.php">Add Teachers</a></li>
                <li><a href="teacher_assignments.php">Assign Teacher</a></li>
                <li><a href="create_class.php">Create Class</a></li>
                <li><a href="class_details.php">View Class</a></li>
                <!-- Add more list items if needed -->
            </ul>
        </div>
    </div>

    <div class="main" id="mainContent">
        <h2>Add Test</h2>
        <form action="exam.php" method="POST">
            <label for="test_name">Name of Test:</label>
            <input type="text" id="test_name" name="test_name" required><br><br>

            <label for="class_id">Class:</label>
            <select id="class_id" name="class_id" required>
                <?php foreach ($classes as $classID) : ?>
                    <option value="<?= $classID ?>"><?= $classID ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" required><br><br>

            <label for="teacher_username">Assigned Teacher:</label>
            <select id="teacher_username" name="teacher_username" required>
                <?php foreach ($teachers as $username) : ?>
                    <option value="<?= $username ?>"><?= $username ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <label for="test_type">Test Type:</label>
            <select id="test_type" name="test_type" required>
                <option value="quiz">Quiz</option>
                <option value="exam">Exam</option>
            </select><br><br>

            <input type="submit" value="Add Test">
        </form>
    </div>
</body>
</html>
