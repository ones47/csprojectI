<?php
// Include database connection
include '../db_connect.php';
session_start();

// Assume staffID is stored in session after login
if (!isset($_SESSION['staffID'])) {
    die("Session error: StaffID not found.");
}
$staffID = $_SESSION['staffID'];

// Initialize arrays to store fetched data
$classes = [];
$students = [];
$tests = [];
$subjects = [];

// Function to fetch classes assigned to the logged-in teacher
function fetchClasses($conn, $staffID) {
    $classes = [];
    $classQuery = "SELECT DISTINCT classID FROM teacher_assignments WHERE staffID = ?";
    $stmt = $conn->prepare($classQuery);
    $stmt->bind_param("i", $staffID);
    $stmt->execute();
    $classResult = $stmt->get_result();
    while ($row = $classResult->fetch_assoc()) {
        $classes[] = $row['classID'];
    }
    $stmt->close();
    return $classes;
}

// Function to fetch students based on classes assigned to the teacher
function fetchStudents($conn, $classIDs) {
    $students = [];
    if (!empty($classIDs)) {
        $studentIDQuery = "SELECT studentID FROM class_students WHERE classID IN ($classIDs)";
        $studentIDResult = $conn->query($studentIDQuery);
        if ($studentIDResult) {
            $studentIDs = [];
            while ($row = $studentIDResult->fetch_assoc()) {
                $studentIDs[] = $row['studentID'];
            }
            $studentIDs = implode(',', $studentIDs);
            $studentQuery = "SELECT studentID, student_Fname, student_Lname FROM students WHERE studentID IN ($studentIDs)";
            $studentResult = $conn->query($studentQuery);
            if ($studentResult) {
                while ($row = $studentResult->fetch_assoc()) {
                    $students[] = $row;
                }
            }
        }
    }
    return $students;
}

// Function to fetch subjects assigned to the logged-in teacher
function fetchSubjects($conn, $staffID) {
    $subjects = [];
    $subjectQuery = "SELECT DISTINCT subject FROM teacher_assignments WHERE staffID = ?";
    $stmt = $conn->prepare($subjectQuery);
    $stmt->bind_param("i", $staffID);
    $stmt->execute();
    $subjectResult = $stmt->get_result();
    while ($row = $subjectResult->fetch_assoc()) {
        $subjects[] = $row['subject'];
    }
    $stmt->close();
    return $subjects;
}

// Function to fetch test types assigned to the logged-in teacher
function fetchTests($conn, $staffID) {
    $testQuery = "SELECT testID, testName FROM tests WHERE staffID = ?";
    $testStmt = $conn->prepare($testQuery);
    $testStmt->bind_param("i", $staffID);
    $testStmt->execute();
    $testResult = $testStmt->get_result();

    $tests = [];
    while ($row = $testResult->fetch_assoc()) {
        $tests[] = $row;
    }

    // Close the statement
    $testStmt->close();

    return $tests;
}

// Fetch data
$classes = fetchClasses($conn, $staffID);
$classIDs = implode(',', $classes); // For use in student query

$students = fetchStudents($conn, $classIDs);
$subjects = fetchSubjects($conn, $staffID);
$tests = fetchTests($conn, $staffID);

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="view_studMarks.php">View Student Grades</a></li>
                <li><a href="view_classResults.php">Class Results</a></li>
                <li><a href="view_classStats.php">Class Statistics</a></li>
                <!-- Add more list items if needed -->
            </ul>
        </div>
    </div>

    <div class="main" id="mainContent">
        <!-- Add your main content here -->
        <div class="container">
            <h2>Add Student Marks</h2>
            <form action="submit_marks.php" method="POST">
                <div class="form-group">
                    <label for="studentID">Student Name:</label>
                    <select name="studentID" id="studentID" required>
                        <?php foreach($students as $student): ?>
                            <option value="<?= htmlspecialchars($student['studentID']) ?>"><?= htmlspecialchars($student['student_Fname'] . ' ' . $student['student_Lname']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="classID">Student Class:</label>
                    <select name="classID" id="classID" required>
                        <?php foreach ($classes as $classID): ?>
                            <option value="<?= htmlspecialchars($classID) ?>"><?= htmlspecialchars($classID) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subject">Subject:</label>
                    <select name="subject" id="subject" required>
                        <?php foreach($subjects as $subject): ?>
                            <option value="<?= htmlspecialchars($subject) ?>"><?= htmlspecialchars($subject) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="grade">Student Grade (0-100):</label>
                    <input type="number" name="grade" id="grade" min="0" max="100" required>
                </div>
                <div class="form-group">
                    <label for="testID">Test Name:</label>
                    <select name="testID" id="testID" required>
                        <?php foreach ($tests as $test): ?>
                            <option value="<?= $test['testID'] ?>"><?= $test['testName'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit">Submit</button>
            </form>
        </div>
    </div>
</body>
</html>
