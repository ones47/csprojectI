<?php
// Include database connection
include '../db_connect.php';
session_start();

// Check if staffID is stored in session after login
if (!isset($_SESSION['staffID'])) {
    die("Session error: StaffID not found.");
}
$staffID = $_SESSION['staffID'];

// Fetch classes assigned to the logged-in teacher
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

// Fetch subjects assigned to the logged-in teacher
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

// Fetch student marks based on classes and subjects assigned to the teacher
function fetchStudentMarks($conn, $classID, $subject) {
    $students = [];
    if (!empty($classID) && !empty($subject)) {
        $marksQuery = "
            SELECT s.student_Fname, s.student_Lname, t.subject, t.grade, c.classID 
            FROM testresults t
            JOIN students s ON t.studentID = s.studentID
            JOIN class_students cs ON s.studentID = cs.studentID
            JOIN classes c ON cs.classID = c.classID
            WHERE cs.classID = ? AND t.subject = ?";
        $stmt = $conn->prepare($marksQuery);
        $stmt->bind_param("is", $classID, $subject);
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
$selectedSubject = $_POST['subject'] ?? null;
$students = [];
if ($selectedClassID && $selectedSubject) {
    $students = fetchStudentMarks($conn, $selectedClassID, $selectedSubject);
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
                <li><a href="view_classResults.php">Class Results</a></li>
                <li><a href="view_classStats.php">Class Statistics</a></li>
            </ul>
        </div>
        <div class="logout-button-container">
            <form action="../logout.php" method="POST">
                <button type="submit">LOG OUT</button>
            </form>
        </div>
    </div>
    <div class="main" id="mainContent">
        <!-- Add your main content here -->
        <h2>View Student Marks</h2>
        <form action="view_studMarks.php" method="POST">
            <div class="form-group">
                <label for="subject">Select Subject:</label>
                <select name="subject" id="subject" required>
                    <option value="">Select Subject</option>
                    <?php foreach($subjects as $subject): ?>
                        <option value="<?= htmlspecialchars($subject) ?>" <?= ($selectedSubject == $subject) ? 'selected' : '' ?>><?= htmlspecialchars($subject) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="classID">Select Class Year:</label>
                <?php foreach($classes as $classID): ?>
                    <button type="submit" name="classID" value="<?= htmlspecialchars($classID) ?>" <?= ($selectedClassID == $classID) ? 'selected' : '' ?>><?= htmlspecialchars($classID) ?></button>
                <?php endforeach; ?>
            </div>
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
            <p>No student marks found for the selected subject and class year.</p>
        <?php endif; ?>
    </div>
</body>
</html>
