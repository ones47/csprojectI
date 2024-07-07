<?php
// Include database connection file
require_once 'db_connect.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    
    if (!empty($username) && !empty($password)) {
        // Prepare and bind
        $stmt = $conn->prepare("SELECT staffID, username, password, designation FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        
        // Execute the statement
        $stmt->execute();
        $stmt->store_result();
        
        // Check if username exists, if yes then verify password
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $username, $stored_password, $designation);
            if ($stmt->fetch()) {
                // Verify hashed password
                if (password_verify($password, $stored_password)) {
                    // Password is correct, start a new session
                    $_SESSION["loggedin"] = true;
                    $_SESSION["staffID"] = $id;
                    $_SESSION["username"] = $username;
                    $_SESSION["designation"] = $designation; // Add designation to session

                    // Redirect based on designation
                    if ($designation == 'administrator') {
                        header("location: admin_dashboard.php");
                    } elseif ($designation == 'teacher') {
                        header("location: teacher/dashboard.php");
                    }
                    exit;
                } else {
                    // Password is not valid
                    header("location: index.php?error=Invalid password.");
                    exit;
                }
            }
        } else {
            // Username doesn't exist
            header("location: index.php?error=Invalid username");
            exit;
        }
        
        // Close statement
        $stmt->close();
    } else {
        header("location: index.php?error=Please enter username and password.");
        exit;
    }
    
    // Close connection
    $conn->close();
} else {
    // If not a POST request, redirect to the login page
    header("location: index.php");
    exit;
}
?>
