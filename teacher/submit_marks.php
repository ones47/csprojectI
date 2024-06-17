<?php
include '../db_connect.php';
session_start();

// Assume teacherID is stored in session after login
$teacherID = $_SESSION['staffID'];

// Get form data
$studentID = $_POST['studentID'];
$classID = $_POST['classID'];
$subject = $_POST['subject'];
$grade = $_POST['grade'];
$testID = $_POST['testID'];

// Insert the data into the  table
$insertQuery = "INSERT INTO test_results (studentID, classID, subject, grade, testID) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insertQuery);
$stmt->bind_param("iisii", $studentID, $classID, $subject, $grade, $testID);

if ($stmt->execute()) {
    echo "Marks added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
