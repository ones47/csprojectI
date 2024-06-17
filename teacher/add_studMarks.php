<?php
// Include database connection
include '../db_connect.php';
session_start();

// Assume staffID is stored in session after login
$staffID = $_SESSION['staffID'];

// Fetch classes assigned to the logged-in teacher
$classQuery = "SELECT DISTINCT classID FROM teacher_assignments WHERE staffID = ?";
$stmt = $conn->prepare($classQuery);
$stmt->bind_param("i", $staffID);
$stmt->execute();
$classResult = $stmt->get_result();

$classes = [];
while ($row = $classResult->fetch_assoc()) {
    $classes[] = $row['classID'];
}

$classIDs = implode(',', $classes);

// Ensure $classIDs is not empty before proceeding with the query
$students = [];
if (!empty($classIDs)) {
    // Fetch studentIDs from class_students table based on the classes assigned to the teacher
    $studentIDQuery = "SELECT studentID FROM class_students WHERE classID IN ($classIDs)";
    $studentIDResult = $conn->query($studentIDQuery);

    $studentIDs = [];
    while ($row = $studentIDResult->fetch_assoc()) {
        $studentIDs[] = $row['studentID'];
    }

    if (!empty($studentIDs)) {
        $studentIDs = implode(',', $studentIDs);
        // Fetch student details from students table using the collected studentIDs
        $studentQuery = "SELECT studentID, student_Fname, student_Lname FROM students WHERE studentID IN ($studentIDs)";
        $studentResult = $conn->query($studentQuery);

        while ($row = $studentResult->fetch_assoc()) {
            $students[] = $row;
        }
    }
}

// Fetch subjects assigned to the logged-in teacher
$subjectQuery = "SELECT DISTINCT subject FROM teacher_assignments WHERE staffID = ?";
$subjectStmt = $conn->prepare($subjectQuery);
$subjectStmt->bind_param("i", $staffID);
$subjectStmt->execute();
$subjectResult = $subjectStmt->get_result();

// Fetch test types
$testQuery = "SELECT testID, testtype FROM tests WHERE staffID = ?";
$testStmt = $conn->prepare($testQuery);
$testStmt->bind_param("i", $staffID);
$testStmt->execute();
$testResult = $testStmt->get_result();

$tests = [];
while ($row = $testResult->fetch_assoc()) {
    $tests[] = $row;
}
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
        <h2>Add Student Marks</h2>
        <form action="submit_marks.php" method="POST">
            <div class="form-group">
                <label for="studentID">Student Name:</label>
                <select name="studentID" id="studentID" required>
                    <?php foreach($students as $student): ?>
                        <option value="<?= $student['studentID'] ?>"><?= $student['student_Fname'] . ' ' . $student['student_Lname'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="classID">Student Class:</label>
                <select name="classID" id="classID" required>
                    <?php foreach ($classes as $classID): ?>
                        <option value="<?= $classID ?>"><?= $classID ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="subject">Subject:</label>
                <select name="subject" id="subject" required>
                    <?php while($row = $subjectResult->fetch_assoc()): ?>
                        <option value="<?= $row['subject'] ?>"><?= $row['subject'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="grade">Student Grade (0-100):</label>
                <input type="number" name="grade" id="grade" min="0" max="100" required>
            </div>
            <div class="form-group">
                <label for="testID">Test ID:</label>
                <select name="testID" id="testID" required>
                    <?php foreach ($tests as $test): ?>
                        <option value="<?= $test['testID'] ?>"><?= $test['testID'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
