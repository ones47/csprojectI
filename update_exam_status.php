<?php
// Include database connection
include 'db_connect.php';

// Check if examID and status are set
if (isset($_GET['examID']) && isset($_GET['status'])) {
    $examID = intval($_GET['examID']);
    $status = intval($_GET['status']);

    // Update the status of all tests associated with the examID
    $updateQuery = "UPDATE tests SET finished = ? WHERE examID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ii", $status, $examID);

    if ($stmt->execute()) {
        echo "Status updated successfully.";
    } else {
        echo "Error updating status: " . $conn->error;
    }

    $stmt->close();
}

// Redirect back to the view_exam.php page
header("Location: adjust_exam.php");
exit;
?>
