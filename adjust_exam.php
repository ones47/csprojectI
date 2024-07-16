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
        function updateStatus(examID, status) {
            if (confirm("Are you sure you want to update the status?")) {
                window.location.href = 'update_exam_status.php?examID=' + examID + '&status=' + status;
            }
        }

        function filterExams(filter) {
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
                <li><a href="view_teachers.php">View Teachers</a></li>
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
        <h2>View Exams</h2>
        <table border="1">
            <tr>
                <th>Exam Name</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php
            // Include database connection
            include 'db_connect.php';

            // Fetch exams and their status
            $examQuery = "
                SELECT e.examID, e.examName, 
                CASE WHEN COUNT(t.finished) = 0 THEN 'No tests'
                     WHEN SUM(t.finished) = 0 THEN 'Ongoing'
                     WHEN SUM(t.finished) = COUNT(t.finished) THEN 'Done'
                     ELSE 'Mixed'
                END as status
                FROM exams e
                LEFT JOIN tests t ON e.examID = t.examID
                GROUP BY e.examID, e.examName
            ";
            $result = $conn->query($examQuery);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['examName']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td>
                            <button onclick=\"updateStatus(" . $row['examID'] . ", 0)\">On</button>
                            <button onclick=\"updateStatus(" . $row['examID'] . ", 1)\">Off</button>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No records found</td></tr>";
            }

            // Close connection
            $conn->close();
            ?>
        </table>
    </div>
</body>
</html>
