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

// Function to categorize grades into EE, ME, AE, BE
function categorizeGrades($grades) {
    $categories = ['EE' => 0, 'ME' => 0, 'AE' => 0, 'BE' => 0];
    foreach ($grades as $grade) {
        if ($grade >= 76) {
            $categories['EE']++;
        } elseif ($grade >= 51 && $grade <= 75) {
            $categories['ME']++;
        } elseif ($grade >= 26 && $grade <= 50) {
            $categories['AE']++;
        } elseif ($grade <= 25) {
            $categories['BE']++;
        }
    }
    return $categories;
}

// Fetch classes and subjects
$classes = fetchClasses($conn, $staffID);
$subjects = fetchSubjects($conn, $staffID);

// Handle form submission
$selectedClassID = $_POST['classID'] ?? null;
$selectedSubjectID = $_POST['subjectID'] ?? null;
$students = [];
$grades = [];

if ($selectedClassID && $selectedSubjectID) {
    $students = fetchStudentMarks($conn, $selectedClassID, $selectedSubjectID, $staffID);
    // Extract grades for pie chart
    foreach ($students as $student) {
        $grades[] = $student['grade'];
    }
    // Categorize grades
    $gradeCategories = categorizeGrades($grades);
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <form action="subject_stats.php" method="POST">
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
            <canvas id="gradeChart" width="400" height="400"></canvas>
            <script>
                var ctx = document.getElementById('gradeChart').getContext('2d');
                var myPieChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['EE', 'ME', 'AE', 'BE'],
                        datasets: [{
                            data: [<?= $gradeCategories['EE'] ?>, <?= $gradeCategories['ME'] ?>, <?= $gradeCategories['AE'] ?>, <?= $gradeCategories['BE'] ?>],
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.6)',
                                'rgba(54, 162, 235, 0.6)',
                                'rgba(255, 206, 86, 0.6)',
                                'rgba(75, 192, 192, 0.6)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'Grade Distribution'
                        }
                    }
                });
            </script>
        <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <p>No student marks found for the selected subject and class.</p>
        <?php endif; ?>
    </div>
</body>
</html>
