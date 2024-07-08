<?php
// Include database connection
include '../db_connect.php';
// Start session
session_start();

// Check if user is logged in and is a teacher
if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['designation'] === 'teacher')) {
    // Redirect to login page or error page
    header("location: ../index.php"); // Redirect to your login page
    exit;
}

$staffID = $_SESSION['staffID'];

// Get testID from URL parameter
if (!isset($_GET['testID'])) {
    die("Error: Missing testID.");
}
$testID = $_GET['testID'];

// Fetch classID and subjectID using testID
$query = "SELECT classID, subjectID FROM tests WHERE testID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $testID);
$stmt->execute();
$stmt->bind_result($classID, $subjectID);
$stmt->fetch();
$stmt->close();

// Fetch students based on classID
function fetchStudents($conn, $classID) {
    $students = [];
    $studentQuery = "SELECT studentID, fname, lname FROM students WHERE classID = ?";
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

// Fetch students
$students = fetchStudents($conn, $classID);

// Process form submission if POST data is present
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $studentID = $_POST['studentID'] ?? null;
    $grade = $_POST['grade'] ?? null;

    // Validate all required fields are filled
    if ($studentID && $grade !== null) {
        // Check if there is already a record for this student and test
        $checkQuery = "SELECT COUNT(*) FROM results WHERE studentID = ? AND testID = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ii", $studentID, $testID);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            // Update existing record
            $updateQuery = "UPDATE results SET grade = ? WHERE studentID = ? AND testID = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("iii", $grade, $studentID, $testID);
        } else {
            // Insert new record
            $insertQuery = "INSERT INTO results (studentID, testID, subjectID, grade) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("iiii", $studentID, $testID, $subjectID, $grade);
        }

        // Execute the statement
        if ($stmt->execute()) {
            echo "Marks added successfully!";
            // Redirect to dashboard or any other page
            header("location: dashboard.php");
            exit();
        } else {
            echo "Error: " . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    } else {
        echo "Error: All fields are required.";
    }
}

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
                <li><a href="subject_stats.php">Subject Statistics</a></li>
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
            <form action="submit_marks.php?testID=<?= htmlspecialchars($testID) ?>" method="POST">
                <div class="form-group">
                    <label for="studentID">Student Name:</label>
                    <select name="studentID" id="studentID" required>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= htmlspecialchars($student['studentID']) ?>"><?= htmlspecialchars($student['fname'] . ' ' . $student['lname']) ?></option>
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
