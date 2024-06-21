<?php
// Include database connection
include '../db_connect.php';
session_start();

// Assume staffID is stored in session after login
if (!isset($_SESSION['staffID'])) {
    die("Session error: StaffID not found.");
}
$staffID = $_SESSION['staffID'];

// Get testID and classID from URL parameters
if (!isset($_GET['testID']) || !isset($_GET['classID'])) {
    die("Error: Missing testID or classID.");
}
$testID = $_GET['testID'];
$classID = $_GET['classID'];

// Function to fetch students based on classID
function fetchStudents($conn, $classID) {
    $students = [];
    $studentQuery = "SELECT s.studentID, s.student_Fname, s.student_Lname 
                     FROM students s
                     JOIN class_students cs ON s.studentID = cs.studentID
                     WHERE cs.classID = ?";
    $stmt = $conn->prepare($studentQuery);
    $stmt->bind_param("i", $classID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
    return $students;
}

// Function to fetch subjects assigned to the logged-in teacher
function fetchSubjects($conn, $staffID) {
    $subjects = [];
    $subjectQuery = "SELECT DISTINCT subject FROM teacher_assignments WHERE staffID = ?";
    $stmt = $conn->prepare($subjectQuery);
    $stmt->bind_param("i", $staffID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row['subject'];
    }
    $stmt->close();
    return $subjects;
}

// Fetch students and subjects
$students = fetchStudents($conn, $classID);
$subjects = fetchSubjects($conn, $staffID);

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Student Marks</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <!-- Topbar Start-->
    <div class="topbar">
        <div class="topbar-left">
            <img src="../img/logo.png" alt="School Logo" class="logo">
            <span class="school-name">St Charles Lwanga</span>
        </div>
    </div>
    <!-- Topbar End-->

    <div class="sidebar" id="mySidebar">
        <div id="list-container">
            <ul>
                <li><a href="add_studMarks.php">Add Students Marks</a></li>
                <li><a href="view_studMarks.php">View Student Marks</a></li>
                <li><a href="view_classResults.php">Class Results</a></li>
                <li><a href="view_classStats.php">Class Statistics</a></li>
                <!-- Add more list items if needed -->
            </ul>
        </div>
        <div class="logout-button-container">
            <form action="../logout.php" method="POST">
                <button type="submit">LOG OUT</button>
            </form>
        </div>
    </div>
    <div class="main" id="mainContent">
        <div class="container">
            <h2>Add Student Marks</h2>
            <form action="process_marks.php" method="POST">
                <!-- Hidden fields for testID and classID -->
                <input type="hidden" name="testID" value="<?= htmlspecialchars($testID) ?>">
                <input type="hidden" name="classID" value="<?= htmlspecialchars($classID) ?>">

                <div class="form-group">
                    <label for="studentID">Student Name:</label>
                    <select name="studentID" id="studentID" required>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= htmlspecialchars($student['studentID']) ?>"><?= htmlspecialchars($student['student_Fname'] . ' ' . $student['student_Lname']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subject">Subject:</label>
                    <select name="subject" id="subject" required>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= htmlspecialchars($subject) ?>"><?= htmlspecialchars($subject) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="grade">Student Grade (0-100):</label>
                    <input type="number" name="grade" id="grade" min="0" max="100" required>
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>
</body>
</html>
