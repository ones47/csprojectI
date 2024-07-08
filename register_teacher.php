<?php
// Include the database connection file
include 'db_connect.php';

// Start session
session_start();

// Check if user is logged in and is an administrator
if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['designation'] === 'administrator')) {
    // Redirect to login page or error page
    header("location: index.php"); // Redirect to your login page
    exit;
}

$staffID = $_SESSION['staffID'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = $_POST['username'] ?? null;
    $fname = $_POST['fname'] ?? null;
    $lname = $_POST['lname'] ?? null;
    $password = $_POST['password'] ?? null;
    $designation = $_POST['designation'] ?? null;

    // Check if any required field is missing
    if ($username && $fname && $lname && $password && $designation) {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare SQL query to insert data into the users table
        $sql = "INSERT INTO users (username, fname, lname, password, designation) VALUES (?, ?, ?, ?, ?)";

        // Initialize prepared statement
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            // Bind parameters
            mysqli_stmt_bind_param($stmt, "sssss", $username, $fname, $lname, $hashed_password, $designation);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                echo "User added successfully!";
            } else {
                echo "Error: " . mysqli_stmt_error($stmt);
            }

            // Close the statement
            mysqli_stmt_close($stmt);
        } else {
            echo "Error preparing statement: " . mysqli_error($conn);
        }
    } else {
        echo "All fields are required.";
    }

    // Close the database connection
    mysqli_close($conn);
} else {
    echo "Invalid request method.";
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
                <li><a href="teacher_assignments.php">Assign Teacher</a></li>
                <li><a href="create_class.php">Add New Class</a></li>
                <li><a href="create_exam.php">Add Exam</a></li>
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
            <h2>Add User</h2>
            <form action="register_teacher.php" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="fname">First Name:</label>
                    <input type="text" id="fname" name="fname" required>
                </div>

                <div class="form-group">
                    <label for="lname">Last Name:</label>
                    <input type="text" id="lname" name="lname" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="designation">Designation:</label>
                    <select id="designation" name="designation" required>
                        <option value="administrator">Administrator</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>

                <button type="submit">Add User</button>
            </form>
        </div>
    </div>
</body>
</html>
