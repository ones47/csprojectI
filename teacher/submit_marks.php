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

// Get classID and testID from URL parameters
if (!isset($_GET['classID']) || !isset($_GET['testID'])) {
    die("Error: Missing classID or testID.");
}
$classID = $_GET['classID'];
$testID = $_GET['testID'];

// Process form submission if POST data is present
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $studentID = $_POST['studentID'] ?? null;
    $subjectID = $_POST['subjectID'] ?? null;
    $grade = $_POST['grade'] ?? null;

    // Validate all required fields are filled
    if ($studentID && $subjectID && $grade !== null) {
        // Insert the data into the results table
        $insertQuery = "INSERT INTO results (studentID, testID, subjectID, grade) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);

        if ($stmt === false) {
            die('Error: ' . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("iiii", $studentID, $testID, $subjectID, $grade);
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

// Function to fetch students based on classID
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

// Function to fetch subjects assigned to the logged-in teacher
function fetchSubjects($conn, $staffID) {
    $subjects = [];
    $subjectQuery = "SELECT subjectID, subjectName FROM subjects WHERE subjectID IN (SELECT subjectID FROM teacher_assignment WHERE staffID = ?)";
    $stmt = $conn->prepare($subjectQuery);
    $stmt->bind_param("i", $staffID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $subjects[$row['subjectID']] = $row['subjectName'];
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
            <form action="submit_marks.php?classID=<?= htmlspecialchars($classID) ?>&testID=<?= htmlspecialchars($testID) ?>" method="POST">
                <!-- Hidden fields for classID and staffID -->
                <input type="hidden" name="classID" value="<?= htmlspecialchars($classID) ?>">
                <input type="hidden" name="staffID" value="<?= htmlspecialchars($staffID) ?>">

                <div class="form-group">
                    <label for="studentID">Student Name:</label>
                    <select name="studentID" id="studentID" required>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= htmlspecialchars($student['studentID']) ?>"><?= htmlspecialchars($student['fname'] . ' ' . $student['lname']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subjectID">Subject:</label>
                    <select name="subjectID" id="subjectID" required>
                        <?php foreach ($subjects as $subjectID => $subject): ?>
                            <option value="<?= htmlspecialchars($subjectID) ?>"><?= htmlspecialchars($subject) ?></option>
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
