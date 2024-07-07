<?php
// Include database connection
include 'db_connect.php';
// Start session
session_start();

// Check if user is logged in and is an administrator
if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['designation'] === 'administrator')) {
    // Redirect to login page or error page
    header("location: index.php"); // Redirect to your login page
    exit;
}

$staffID = $_SESSION['staffID'];
// Get testID and currentStatus from URL
$testID = $_GET['testID'];
$currentStatus = $_GET['currentStatus'];

// Determine the new status
$newStatus = $currentStatus == 1 ? 0 : 1;

// Update the test status in the database
$query = "UPDATE tests SET finished = ? WHERE testID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $newStatus, $testID);

if ($stmt->execute()) {
    echo "Test status updated successfully.";
} else {
    echo "Error updating test status: " . $conn->error;
}

// Close connection
$stmt->close();
$conn->close();

// Redirect back to the view_exam.php page
header("Location: view_exam.php");
exit;
?>
