<?php
include 'db_connect.php';

// Handle form submission for registering students
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_student'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $class = $_POST['class'];
    $parent_contacts = $_POST['parent_contacts'];
    $dob = $_POST['dob'];

    $sql = "INSERT INTO students (student_Fname, student_Lname, classID, parent_contacts, dob) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiss", $first_name, $last_name, $class, $parent_contacts, $dob);

    if ($stmt->execute()) {
        echo "New student registered successfully";
        header("location: assign_class.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
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
                <li><a href="register_teacher.php">Add Teacher</a></li>
                <li><a href="create_class.php">Create Class</a></li>
                <li><a href="exam.php">Add Exam</a></li>
                <li><a href="class_details.php">View Class</a></li>
                <li><a href="teacher_assignments.php">Assign Teacher/Class</a></li>
                <!-- Add more list items if needed -->
            </ul>
        </div>
    </div>

    <div class="main" id="mainContent">
        <!-- Add your main content here -->
        <div class="container">
            <h2>Register Student</h2>
            <form action="register_student.php" method="POST">
                <input type="hidden" name="register_student" value="1">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="class">Class (Year of Graduation):</label>
                    <input type="text" id="class" name="class" required>
                </div>
                <div class="form-group">
                    <label for="parent_contacts">Parent Contacts (Phone Number):</label>
                    <input type="text" id="parent_contacts" name="parent_contacts" required>
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" required>
                </div>
                <button type="submit">Register Student</button>
            </form>
        </div>
    </div>
</body>
</html>
