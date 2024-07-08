<?php
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
    <title>View Exams</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <script>
        function updateStatus(testID, currentStatus) {
            if (confirm("Are you sure you want to update the status?")) {
                window.location.href = 'update_test_status.php?testID=' + testID + '&currentStatus=' + currentStatus;
            }
        }

        function filterTests(filter) {
            window.location.href = 'view_exam.php?filter=' + filter;
        }
    </script>
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
        <h2>View Exams</h2>
        <div class="filter-buttons">
            <button onclick="filterTests('all')">All</button>
            <button onclick="filterTests('ongoing')">Ongoing</button>
            <button onclick="filterTests('done')">Done</button>
        </div>
        <table border="1">
            <tr>
                <th>Test Name</th>
                <th>Teacher</th>
                <th>Subject</th>
                <th>Class</th>
                <th>Term</th>
                <th>Year</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php
            // Include database connection
            include 'db_connect.php';

            $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
            $query = "SELECT t.testID, t.testName, t.classID, t.subjectID, t.staffID, t.term, t.yearOfTest, t.finished,
                        s.subjectName, u.username
                      FROM tests t
                      JOIN subjects s ON t.subjectID = s.subjectID
                      JOIN users u ON t.staffID = u.staffID";

            if ($filter == 'ongoing') {
                $query .= " WHERE t.finished = 0";
            } elseif ($filter == 'done') {
                $query .= " WHERE t.finished = 1";
            }

            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['testName'] . "</td>";
                    echo "<td>" . $row['username'] . "</td>";
                    echo "<td>" . $row['subjectName'] . "</td>";
                    echo "<td>" . $row['classID'] . "</td>";
                    echo "<td>" . $row['term'] . "</td>";
                    echo "<td>" . $row['yearOfTest'] . "</td>";
                    echo "<td>" . ($row['finished'] == 1 ? 'Done' : 'Ongoing') . "</td>";
                    echo "<td><button onclick=\"updateStatus(" . $row['testID'] . ", " . $row['finished'] . ")\">Update</button></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No records found</td></tr>";
            }

            // Close connection
            $conn->close();
            ?>
        </table>
    </div>
</body>
</html>
