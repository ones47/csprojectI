<?php
// Include database connection
include 'db_connect.php';
session_start();

// Start session
session_start();

// Check if user is logged in and is an administrator
if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['designation'] === 'administrator')) {
    // Redirect to login page or error page
    header("location: index.php"); // Redirect to your login page
    exit;
}

$staffID = $_SESSION['staffID'];

if (isset($_GET['username'])) {
    $username = $_GET['username'];

    // Delete the user
    $deleteQuery = "DELETE FROM users WHERE username = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("s", $username);
    if ($stmt->execute()) {
        echo "User deleted successfully.";
    } else {
        echo "Error deleting user.";
    }
    $stmt->close();
}

// Close the database connection
$conn->close();

// Redirect back to the view teachers page
header("Location: view_teachers.php");
exit();
?>
