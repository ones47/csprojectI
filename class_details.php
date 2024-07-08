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

// Fetch distinct class years from the students table
$class_years_query = "SELECT DISTINCT classID FROM students ORDER BY classID ASC";
$class_years_result = mysqli_query($conn, $class_years_query);

// Check for errors in the query
if (!$class_years_result) {
    die("Query failed: " . mysqli_error($conn));
}

// Fetch all class years
$class_years = mysqli_fetch_all($class_years_result, MYSQLI_ASSOC);

// Fetch students based on selected class year
$selected_class = $_GET['class'] ?? ($class_years[0]['classID'] ?? null);

if ($selected_class) {
    $students_query = "
    SELECT 
        studentID, 
        fname, 
        lname, 
        classID, 
        contacts, 
        dob 
    FROM 
        students
    WHERE 
        classID = ?
    ORDER BY 
        fname ASC, lname ASC";

    $stmt = mysqli_prepare($conn, $students_query);
    mysqli_stmt_bind_param($stmt, "i", $selected_class);
    mysqli_stmt_execute($stmt);
    $students_result = mysqli_stmt_get_result($stmt);

    // Check for errors in the query
    if (!$students_result) {
        die("Query failed: " . mysqli_error($conn));
    }

    // Fetch all rows
    $students = mysqli_fetch_all($students_result, MYSQLI_ASSOC);

    // Close the statement
    mysqli_stmt_close($stmt);
} else {
    // Handle case where no class is selected
    $students = [];
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Details</title>
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
                <li><a href="exam.php">Add Exam</a></li>
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
            <h2>Class List</h2>

            <!-- Class Year Buttons -->
            <div>
                <?php foreach ($class_years as $year): ?>
                    <button onclick="window.location.href='class_details.php?class=<?= htmlspecialchars($year['classID']) ?>'">
                        <?= htmlspecialchars($year['classID']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Students List -->
            <table border="1">
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Class</th>
                    <th>Date of Birth</th>
                    <!-- Add more columns if needed -->
                </tr>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= htmlspecialchars($student['fname']) ?></td>
                        <td><?= htmlspecialchars($student['lname']) ?></td>
                        <td><?= htmlspecialchars($student['classID']) ?></td>
                        <td><?= htmlspecialchars($student['dob']) ?></td>
                        <!-- Add more columns if needed -->
                    </tr>
                <?php endforeach; ?>
            </table>

            <!-- Download PDF Button -->
            <form action="generate_pdf.php" method="post">
                <input type="hidden" name="class" value="<?= htmlspecialchars($selected_class) ?>">
                <input type="submit" value="Download PDF">
            </form>
        </div>
    </div>
</body>
</html>
