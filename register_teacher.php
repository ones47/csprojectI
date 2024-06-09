<?php
// Include the database connection file
include 'db_connect.php';

// Retrieve form data
$username = $_POST['username'];
$fname = $_POST['fname'];
$lname = $_POST['lname'];
$password = $_POST['password'];
$designation = $_POST['designation'];

// Hash the password for security
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// SQL query to insert data into the users table
$sql = "INSERT INTO users (username, fname, lname, password, designation) VALUES ('$username', '$fname', '$lname', '$hashed_password', '$designation')";

// Execute the query
if (mysqli_query($conn, $sql)) {
    echo "User added successfully!";
} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}

// Close the database connection
mysqli_close($conn);
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
        <div class="topbar-right">
            <a href="about.html">About</a>
            <a href="contact.html">Contact</a>
        </div>
    </div>
    <!-- Topbar End-->
    <div class="sidebar" id="mySidebar">
        <div id="list-container">
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="register_student.php">Add Students</a></li>
                <li><a href="teacher_assignments.php">Assign Teacher</a></li>
                <li><a href="create_class.php">Create Class</a></li>
                <li><a href="exam.php">Add Exam</a></li>
                <li><a href="class_details.php">View Class</a></li>
                <!-- Add more list items if needed -->
            </ul>
        </div>
    </div>

    <div class="main" id="mainContent">
        <h2>Add User</h2>
        <form action="register_teacher.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br><br>

            <label for="fname">First Name:</label>
            <input type="text" id="fname" name="fname" required><br><br>

            <label for="lname">Last Name:</label>
            <input type="text" id="lname" name="lname" required><br><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br><br>

            <label for="designation">Designation:</label>
            <select id="designation" name="designation" required>
                <option value="administrator">Administrator</option>
                <option value="teacher">Teacher</option>
            </select><br><br>

            <input type="submit" value="Add User">
        </form>
    </div>
</body>
</html>
