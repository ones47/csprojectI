<?php
// Include database connection
include '../db_connect.php';
session_start();

// staffID is stored in session after login
if (!isset($_SESSION['staffID'])) {
    die("Session error: StaffID not found.");
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
