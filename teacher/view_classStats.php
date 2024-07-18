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

// Fetch all class years
function fetchClassYears($conn) {
    $classYears = [];
    $classQuery = "SELECT DISTINCT classID FROM students";
    $result = $conn->query($classQuery);
    while ($row = $result->fetch_assoc()) {
        $classYears[] = $row['classID'];
    }
    return $classYears;
}

// Fetch all exam names and IDs
function fetchExams($conn) {
    $exams = [];
    $examQuery = "SELECT examID, examName FROM exams";
    $result = $conn->query($examQuery);
    while ($row = $result->fetch_assoc()) {
        $exams[] = $row;
    }
    return $exams;
}

// Fetch student details for a specific class year
function fetchStudents($conn, $classID) {
    $students = [];
    $studentQuery = "SELECT * FROM students WHERE classID = ?";
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

// Fetch subjects from the subjects table
function fetchSubjects($conn) {
    $subjects = [];
    $subjectQuery = "SELECT * FROM subjects";
    $result = $conn->query($subjectQuery);
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
    return $subjects;
}

// Fetch subject name and grade for a specific student and exam
function fetchStudentMarks($conn, $studentID, $examID) {
    $marks = [];
    $markQuery = "
        SELECT r.subjectID, r.grade, s.subjectName 
        FROM results r
        JOIN subjects s ON r.subjectID = s.subjectID
        WHERE r.studentID = ? AND r.examID = ?";
    $stmt = $conn->prepare($markQuery);
    $stmt->bind_param("ii", $studentID, $examID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $marks[$row['subjectID']] = $row['grade'];
    }
    $stmt->close();
    return $marks;
}

// Fetch all class years and subjects
$classYears = fetchClassYears($conn);
$exams = fetchExams($conn); // Fetch exams here
$subjects = fetchSubjects($conn); // Fetch subjects here

// Handle form submission
$selectedClassID = $_POST['classID'] ?? null;
$selectedExamID = $_POST['examID'] ?? null;
$students = [];
if ($selectedClassID && $selectedExamID) {
    $students = fetchStudents($conn, $selectedClassID);
}

// Close database connection
// $conn->close(); // Remove this line to prevent premature closing of the connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Class Statistics</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
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
                <li><a href="view_studMarks.php">View Student Marks</a></li>
                <li><a href="subject_stats.php">Subject Statistics</a><li>
                <li><a href="account.php">Account</a></li>
                <li>
                    <form action="../logout.php" method="POST">
                        <button type="submit" class="logout-button">LOG OUT</button>
                    </form>
                </li>
                <!-- Add more list items if needed -->
            </ul>
        </div>
    </div>

    <div class="main" id="mainContent">
        <h2>View Class Statistics</h2>
        <form action="view_classStats.php" method="POST">
            <div class="form-group">
                <label for="classID">Select Class Year:</label>
                <select name="classID" id="classID" required>
                    <option value="">Select Class Year</option>
                    <?php foreach ($classYears as $classYear): ?>
                        <option value="<?= htmlspecialchars($classYear) ?>"><?= htmlspecialchars($classYear) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="examID">Select Exam:</label>
                <select name="examID" id="examID" required>
                    <option value="">Select Exam</option>
                    <?php foreach ($exams as $exam): ?>
                        <option value="<?= htmlspecialchars($exam['examID']) ?>"><?= htmlspecialchars($exam['examName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">View Statistics</button>
        </form>
        
        <?php if (!empty($students)): ?>
            <table>
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <?php foreach ($subjects as $subject): ?>
                            <th><?= htmlspecialchars($subject['subjectName']) ?></th>
                        <?php endforeach; ?>
                        <th>Average</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Initialize arrays to store total grades for each subject and overall grades
                    $subjectTotals = array_fill_keys(array_column($subjects, 'subjectID'), 0);
                    $totalGradesSum = 0;
                    $studentCount = count($students);
                    ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['fname']) ?></td>
                            <td><?= htmlspecialchars($student['lname']) ?></td>
                            <?php
                                $studentID = $student['studentID'];
                                $studentMarks = fetchStudentMarks($conn, $studentID, $selectedExamID);
                                $totalGradeSum = 0;
                            ?>
                            <?php foreach ($subjects as $subject): ?>
                                <?php 
                                    $subjectID = $subject['subjectID'];
                                    $grade = $studentMarks[$subjectID] ?? 0;
                                    $totalGradeSum += $grade;
                                    $subjectTotals[$subjectID] += $grade;
                                ?>
                                <td><?= htmlspecialchars($grade) ?></td>
                            <?php endforeach; ?>
                            <td><?= $averageGrade = $totalGradeSum / count($subjects) ?></td>
                            <td>
                                <?php
                                    if ($averageGrade >= 76) {
                                        echo 'EE';
                                    } elseif ($averageGrade >= 51) {
                                        echo 'ME';
                                    } elseif ($averageGrade >= 26) {
                                        echo 'AE';
                                    } else {
                                        echo 'BE';
                                    }
                                ?>
                            </td>
                        </tr>
                        <?php $totalGradesSum += $averageGrade; ?>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="2"><strong>Subject Averages</strong></td>
                        <?php foreach ($subjects as $subject): ?>
                            <td><?= $studentCount > 0 ? round($subjectTotals[$subject['subjectID']] / $studentCount, 2) : 0 ?></td>
                        <?php endforeach; ?>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="<?= 2 + count($subjects) ?>"><strong>Overall Class Average</strong></td>
                        <td colspan="2">
                            <?php
                                $classAverage = $totalGradesSum / $studentCount;
                                echo $classAverage;
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="<?= 2 + count($subjects) ?>"><strong>Overall Class Grade</strong></td>
                        <td colspan="2">
                            <?php
                                if ($classAverage >= 76) {
                                    echo 'EE';
                                } elseif ($classAverage >= 51) {
                                    echo 'ME';
                                } elseif ($classAverage >= 26) {
                                    echo 'AE';
                                } else {
                                    echo 'BE';
                                }
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>
