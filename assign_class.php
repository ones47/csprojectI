<?php
include 'db_connect.php'; // Ensure the correct file name

// Fetch students and classes from the database for the dropdown lists
$students_result = $conn->query("SELECT studentID, CONCAT(student_Fname, ' ', student_Lname) AS full_name FROM students");
$classes_result = $conn->query("SELECT classID FROM classes");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_class'])) {
    $studentID = $_POST['student'];
    $classID = $_POST['class'];

    // Insert the studentID and classID into the class_students table
    $sql = "INSERT INTO class_students (classID, studentID) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $classID, $studentID);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Student assigned to class successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Student to Class</title>
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
                <li><a href="register_teacher.php">Add Teacher</a></li>
                <li><a href="teacher_assignments.php">Assign Teacher</a></li>
                <li><a href="exam.php">Add Exam</a></li>
                <li><a href="create_class.php">Create Class</a></li>
                <li><a href="class_details.php">View Class</a></li>
                <!-- Add more list items if needed -->
            </ul>
        </div>
        <div class="logout-button-container">
            <form action="logout.php" method="POST">
                <button type="submit">LOG OUT</button>
            </form>
        </div>
    </div>

    <div class="main" id="mainContent">
        <!-- Add your main content here -->
        <div class="container">
            <h2>Assign Student to Class</h2>
            <form action="assign_class.php" method="POST">
                <input type="hidden" name="assign_class" value="1">
                <div class="form-group">
                    <label for="student">Student:</label>
                    <select id="student" name="student" required>
                        <?php while ($student = $students_result->fetch_assoc()): ?>
                            <option value="<?php echo $student['studentID']; ?>"><?php echo $student['full_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="class">Class:</label>
                    <select id="class" name="class" required>
                        <?php while ($class = $classes_result->fetch_assoc()): ?>
                            <option value="<?php echo $class['classID']; ?>"><?php echo $class['classID']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit">Assign Class</button>
            </form>
        </div>
    </div>
</body>
</html>
