<?php
// Include the database connection file
include 'db_connect.php';
session_start();

// Check if user is logged in and is an administrator
if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['designation'] === 'administrator')) {
    // Redirect to login page or error page
    header("location: index.php"); // Redirect to your login page
    exit;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $studentID = $_POST['studentID'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $class = $_POST['class'];
    $parent_contacts = $_POST['parent_contacts'];
    $dob = $_POST['dob'];

    // Prepare update statement
    $update_query = "
    UPDATE students
    SET 
        fname = ?,
        lname = ?,
        classID = ?,
        contacts = ?,
        dob = ?
    WHERE 
        studentID = ?";

    $stmt = mysqli_prepare($conn, $update_query);

    // Bind parameters
    mysqli_stmt_bind_param($stmt, "sssisi", $first_name, $last_name, $class, $parent_contacts, $dob, $studentID);

    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
        // Successful update
        echo "Student details updated successfully!";
        // Redirect to class_details.php or another appropriate page
        header("location: class_details.php");
        exit;
    } else {
        // Error in update
        echo "Error updating student details: " . mysqli_error($conn);
    }

    // Close the statement
    mysqli_stmt_close($stmt);
} else {
    // If not a POST request, redirect to class_details.php or another appropriate page
    header("location: class_details.php");
    exit;
}

// Close the database connection
mysqli_close($conn);
?>
