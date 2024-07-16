<?php
// Include database connection
include '../db_connect.php';
// Start session
session_start();

// Check if user is logged in and is an administrator
if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['designation'] === 'teacher')) {
    // Redirect to login page or error page
    header("location: ../index.php"); // Redirect to your login page
    exit;
}
$staffID = $_SESSION['staffID'];

// Fetch classes assigned to the logged-in teacher
function fetchClasses($conn, $staffID) {
    $classes = [];
    $classQuery = "SELECT DISTINCT classID FROM teacher_assignment WHERE staffID = ?";
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

// Fetch subjects assigned to the logged-in teacher
function fetchSubjects($conn, $staffID) {
    $subjects = [];
    $subjectQuery = "SELECT DISTINCT s.subjectID, s.subjectName FROM subjects s
                     JOIN teacher_assignment ta ON s.subjectID = ta.subjectID
                     WHERE ta.staffID = ?";
    $stmt = $conn->prepare($subjectQuery);
    $stmt->bind_param("i", $staffID);
    $stmt->execute();
    $subjectResult = $stmt->get_result();
    while ($row = $subjectResult->fetch_assoc()) {
        $subjects[$row['subjectID']] = $row['subjectName'];
    }
    $stmt->close();
    return $subjects;
}

// Fetch student marks based on classes and subjects assigned to the teacher
function fetchStudentMarks($conn, $classID, $subjectID, $staffID) {
    $students = [];
    if (!empty($classID) && !empty($subjectID)) {
        $marksQuery = "
            SELECT s.fname AS student_Fname, s.lname AS student_Lname, sub.subjectName AS subject, r.grade, c.classID 
            FROM results r
            JOIN students s ON r.studentID = s.studentID
            JOIN classes c ON s.classID = c.classID
            JOIN subjects sub ON r.subjectID = sub.subjectID
            JOIN tests t ON r.testID = t.testID
            WHERE s.classID = ? AND r.subjectID = ? AND t.staffID = ? AND t.finished = 0";
        $stmt = $conn->prepare($marksQuery);
        $stmt->bind_param("iii", $classID, $subjectID, $staffID);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        $stmt->close();
    }
    return $students;
}

// Fetch classes and subjects
$classes = fetchClasses($conn, $staffID);
$subjects = fetchSubjects($conn, $staffID);

// Handle form submission
$selectedClassID = $_POST['classID'] ?? null;
$selectedSubjectID = $_POST['subjectID'] ?? null;
$students = [];
if ($selectedClassID && $selectedSubjectID) {
    $students = fetchStudentMarks($conn, $selectedClassID, $selectedSubjectID, $staffID);
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Marks</title>
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
                <li><a href="add_studMarks.php">Add Student Grades</a></li>
                <li><a href="subject_stats.php">Subject Statistics</a></li>
                <li><a href="view_classStats.php">Class Statistics</a></li>
                <li>
                    <form action="../logout.php" method="POST">
                        <button type="submit" class="logout-button">LOG OUT</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
    <div class="main" id="mainContent">
        <!-- Add your main content here -->
        <h2>View Student Marks</h2>
        <form action="view_studMarks.php" method="POST">
            <div class="form-group">
                <label for="subjectID">Select Subject:</label>
                <select name="subjectID" id="subjectID" required>
                    <option value="">Select Subject</option>
                    <?php foreach($subjects as $subjectID => $subjectName): ?>
                        <option value="<?= htmlspecialchars($subjectID) ?>" <?= ($selectedSubjectID == $subjectID) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($subjectName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="classID">Select Class:</label>
                <select name="classID" id="classID" required>
                    <option value="">Select Class</option>
                    <?php foreach($classes as $classID): ?>
                        <option value="<?= htmlspecialchars($classID) ?>" <?= ($selectedClassID == $classID) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($classID) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">View Marks</button>
        </form>
        
        <?php if (!empty($students)): ?>
            <table border="1" cellspacing="0" cellpadding="10">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Subject</th>
                        <th>Grade</th>
                        <th>Class</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['student_Fname']) ?></td>
                            <td><?= htmlspecialchars($student['student_Lname']) ?></td>
                            <td><?= htmlspecialchars($student['subject']) ?></td>
                            <td><?= htmlspecialchars($student['grade']) ?></td>
                            <td><?= htmlspecialchars($student['classID']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <p>No student marks found for the selected subject and class.</p>
        <?php endif; ?>
    </div>
</body>
</html>
