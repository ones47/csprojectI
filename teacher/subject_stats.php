<?php
// Include database connection
include '../db_connect.php';
// Start session
session_start();

// Check if user is logged in and is a teacher
if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['designation'] === 'teacher')) {
    // Redirect to login page or error page
    header("location: ../index.php");
    exit;
}
$staffID = $_SESSION['staffID'];

// Fetch all test IDs that are not finished and associated with the logged-in teacher
function fetchTestIDs($conn, $staffID) {
    $testIDs = [];
    $testQuery = "SELECT testID, testName FROM tests WHERE staffID = ? AND finished = 0";
    $stmt = $conn->prepare($testQuery);
    if ($stmt === false) {
        // Handle error
        die('MySQL prepare error: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $staffID);
    if (!$stmt->execute()) {
        // Handle execution error
        die('Execute error: ' . htmlspecialchars($stmt->error));
    }
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $testIDs[$row['testID']] = $row['testName'];
    }
    $stmt->close();
    return $testIDs;
}

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Statistics</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <!-- Include Chart.js library for pie chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Topbar and Sidebar (from provided template) -->
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
                <li><a href="dashboard.php">Dashboard</a></li>
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
        <h2>Class Statistics</h2>
        <form action="subject_stats.php" method="POST">
            <div class="form-group">
                <label for="testID">Select Test:</label>
                <select name="testID" id="testID" required>
                    <option value="">Select Test</option>
                    <?php
                    // Fetch test IDs and names
                    $testIDs = fetchTestIDs($conn, $staffID);
                    foreach ($testIDs as $testID => $testName) {
                        echo '<option value="' . htmlspecialchars($testID) . '">' . htmlspecialchars($testName) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <button type="submit">View Statistics</button>
        </form>

        <!-- Display Pie Chart here -->
        <canvas id="gradePieChart" width="400" height="400"></canvas>

        <script>
            // Function to create and update the pie chart
            function updatePieChart(grades) {
                var ctx = document.getElementById('gradePieChart').getContext('2d');
                var data = {
                    labels: ['EE (>= 76)', 'ME (>= 51)', 'AE (>= 26)', 'BE (< 26)'],
                    datasets: [{
                        label: 'Grade Distribution',
                        data: grades,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                };

                var options = {
                    responsive: true,
                    maintainAspectRatio: false
                };

                var pieChart = new Chart(ctx, {
                    type: 'pie',
                    data: data,
                    options: options
                });
            }

            // Fetch grades data when a test is selected
            var testSelect = document.getElementById('testID');
            testSelect.addEventListener('change', function() {
                var selectedTestID = this.value;
                // AJAX or form submission to fetch grades for the selected test
                // Example using PHP-generated JavaScript
                <?php
                echo "var grades = " . json_encode(fetchGrades($conn, $testID)) . ";";
                ?>
                // Update the pie chart with new data
                updatePieChart(grades);
            });
        </script>
    </div>
</body>
</html>





