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

// Check if studentID is provided via GET or POST
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['studentID'])) {
    $studentID = $_GET['studentID'];
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['studentID'])) {
    $studentID = $_POST['studentID'];
} else {
    // Redirect to class_details.php or appropriate page if studentID is not provided
    header("location: class_details.php");
    exit;
}

// Fetch student details based on studentID
$fetch_student_query = "
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
    studentID = ?";

$stmt = mysqli_prepare($conn, $fetch_student_query);
mysqli_stmt_bind_param($stmt, "i", $studentID);
mysqli_stmt_execute($stmt);
$student_result = mysqli_stmt_get_result($stmt);

// Check if student exists
if (mysqli_num_rows($student_result) == 0) {
    echo "Student not found.";
    exit;
}

// Fetch student details
$student = mysqli_fetch_assoc($student_result);

// Close statement
mysqli_stmt_close($stmt);

// Fetch all class years from classes table
$class_years_query = "SELECT DISTINCT classID FROM classes ORDER BY classID ASC";
$class_years_result = mysqli_query($conn, $class_years_query);

// Check for errors in the query
if (!$class_years_result) {
    die("Query failed: " . mysqli_error($conn));
}

// Fetch all class years
$class_years = mysqli_fetch_all($class_years_result, MYSQLI_ASSOC);

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Student</title>
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
                <li><a href="create_exam.php">Add Exam</a></li>
                <li><a href="exam.php">Add Test</a></li>
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
            <h2>Update Student</h2>
            <!-- Update Form -->
            <form action="process_update_student.php" method="post">
                <input type="hidden" name="studentID" value="<?= $student['studentID'] ?>">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($student['fname']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($student['lname']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="class">Class (Year of Graduation):</label>
                    <select id="class" name="class" required>
                        <?php foreach ($class_years as $year): ?>
                            <option value="<?= htmlspecialchars($year['classID']) ?>" <?= ($year['classID'] == $student['classID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year['classID']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="parent_contacts">Parent Contacts (Phone Number):</label>
                    <input type="text" id="parent_contacts" name="parent_contacts" value="<?= htmlspecialchars($student['contacts']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($student['dob']) ?>" required>
                </div>
                <button type="submit">Update Student</button>
            </form>
        </div>
    </div>
</body>
</html>
