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

// Get form data
$studentID = $_POST['studentID'] ?? null;
$classID = $_POST['classID'] ?? null;
$subject = $_POST['subject'] ?? null;
$grade = $_POST['grade'] ?? null;
$testID = $_POST['testID'] ?? null;

// Validate all required fields are filled
if ($studentID && $classID && $subject && $grade !== null && $testID) {
    // Insert the data into the testresults table
    $insertQuery = "INSERT INTO testresults (studentID, classID, subject, grade, testID) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);

    if ($stmt === false) {
        die('Error: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("iisii", $studentID, $classID, $subject, $grade, $testID);
    if ($stmt->execute()) {
        echo "Marks added successfully!";
        header("location: dashboard.php"); //Redirect
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }

    $stmt->close();
} else {
    echo "Error: All fields are required.";
}

$conn->close();
?>
