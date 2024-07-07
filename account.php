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

// Fetch user details
function fetchUserDetails($conn, $staffID) {
    $query = "SELECT fname, lname, username, designation FROM users WHERE staffID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $staffID);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

$userDetails = fetchUserDetails($conn, $staffID);

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Account Details</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table, th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn-update {
            background-color: #4CAF50; /* Green */
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
        }
    </style>
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
                <li><a href="exam.php">Add Exam</a></li>
                <li><a href="class_details.php">View Class</a></li>
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
        <h2>View Account Details</h2>
        <table class="details-table">
            <tr>
                <th>First Name</th>
                <td><?= htmlspecialchars($userDetails['fname']) ?></td>
            </tr>
            <tr>
                <th>Last Name</th>
                <td><?= htmlspecialchars($userDetails['lname']) ?></td>
            </tr>
            <tr>
                <th>Username</th>
                <td><?= htmlspecialchars($userDetails['username']) ?></td>
            </tr>
            <tr>
                <th>Designation</th>
                <td><?= htmlspecialchars($userDetails['designation']) ?></td>
            </tr>
            <tr>
                <th>Password</th>
                <td>
                    <input type="password" id="password" value="********" readonly>
                    <button class="btn-update" onclick="window.location.href='update_password.php'">Update</button>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
