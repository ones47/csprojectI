<?php
// Include the database connection file
include 'db_connect.php';

// Fetch distinct class years from the class_students table
$class_years_query = "SELECT DISTINCT classID FROM class_students ORDER BY classID ASC";
$class_years_result = mysqli_query($conn, $class_years_query);

// Check for errors in the query
if (!$class_years_result) {
    die("Query failed: " . mysqli_error($conn));
}

// Fetch all class years
$class_years = mysqli_fetch_all($class_years_result, MYSQLI_ASSOC);

// Fetch students based on selected class year
$selected_class = $_GET['class'] ?? $class_years[0]['classID'];

$students_query = "
SELECT 
    s.student_Fname AS fname, 
    s.student_Lname AS lname, 
    c.classID AS class, 
    s.dob 
FROM 
    students s
JOIN 
    class_students c ON s.studentID = c.studentID
WHERE 
    c.classID = ?
ORDER BY 
    s.student_Fname ASC, s.student_Lname ASC";

$stmt = mysqli_prepare($conn, $students_query);
mysqli_stmt_bind_param($stmt, "s", $selected_class);
mysqli_stmt_execute($stmt);
$students_result = mysqli_stmt_get_result($stmt);

// Check for errors in the query
if (!$students_result) {
    die("Query failed: " . mysqli_error($conn));
}

// Fetch all rows
$students = mysqli_fetch_all($students_result, MYSQLI_ASSOC);

// Close the database connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Details</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <!-- Topbar Start-->
    <div class="topbar">
        <div class="topbar-left">
            <img src="img/logo.png" alt="School Logo" class="logo">
            <span class="school-name">St Charles Lwanga</span>
        </div>
    </div>
    <!-- Topbar End-->
    <div class="sidebar" id="mySidebar">
        <div id="list-container">
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="register_student.php">Add Students</a></li>
                <li><a href="register_teacher.php">Add Teachers</a></li>
                <li><a href="teacher_assignments.php">Assign Teacher</a></li>
                <li><a href="create_class.php">Create Class</a></li>
                <li><a href="exam.php">Add Exam</a></li>
                <li><a href="class_details.php">View Class</a></li>
                <!-- Add more list items if needed -->
            </ul>
        </div>
    </div>

    <div class="main" id="mainContent">
        <h2>Class List</h2>
        
        <!-- Class Year Buttons -->
        <div>
            <?php foreach ($class_years as $year): ?>
                <button onclick="window.location.href='class_details.php?class=<?= htmlspecialchars($year['classID']) ?>'">
                    <?= htmlspecialchars($year['classID']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Students List -->
        <table border="1">
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Class</th>
                <th>Date of Birth</th>
            </tr>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= htmlspecialchars($student['fname']) ?></td>
                    <td><?= htmlspecialchars($student['lname']) ?></td>
                    <td><?= htmlspecialchars($student['class']) ?></td>
                    <td><?= htmlspecialchars($student['dob']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        
        <!-- Download PDF Button -->
        <form action="generate_pdf.php" method="post">
            <input type="hidden" name="class" value="<?= htmlspecialchars($selected_class) ?>">
            <input type="submit" value="Download PDF">
        </form>
    </div>
</body>
</html>
