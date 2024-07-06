<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
                <li><a href="create_class.php">Create Class</a></li>
                <li><a href="exam.php">Add Exam</a></li>
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
            <h2>Assign Teacher</h2>
            <form action="teacher_assignments.php" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <select id="username" name="username" required>
                        <?php
                        // Include the database connection file
                        include 'db_connect.php';

                        // Fetch the list of teachers
                        $result = mysqli_query($conn, "SELECT username FROM users WHERE designation = 'teacher'");
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='" . $row['username'] . "'>" . $row['username'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="classID">Class ID:</label>
                    <select id="classID" name="classID" required>
                        <?php
                        // Fetch the list of class IDs
                        $result = mysqli_query($conn, "SELECT classID FROM classes");
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='" . $row['classID'] . "'>" . $row['classID'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subject">Subject:</label>
                    <select id="subject" name="subject" required>
                        <?php
                        // Fetch the list of subjects
                        $result = mysqli_query($conn, "SELECT subjectID, subjectName FROM subjects");
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='" . $row['subjectID'] . "'>" . $row['subjectName'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="class_teacher">Class Teacher:</label>
                    <select id="class_teacher" name="class_teacher" required>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>

                <button type="submit">Assign Teacher</button>
            </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Retrieve form data
            $username = $_POST['username'];
            $classID = $_POST['classID'];
            $subject = $_POST['subject'];
            $class_teacher = $_POST['class_teacher'];

                // Find the staffID of the selected username
                $result = mysqli_query($conn, "SELECT staffID FROM users WHERE username = '$username'");
                $row = mysqli_fetch_assoc($result);
                $staffID = $row['staffID'];

            // SQL query to insert data into the teacher_assignments table
            $sql = "INSERT INTO teacher_assignments (staffID, classID, subject, class_teacher) VALUES ('$staffID', '$classID', '$subject', '$class_teacher')";

                // Execute the query
                if (mysqli_query($conn, $sql)) {
                    echo "Teacher assigned successfully!";
                } else {
                    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
                }

                // Close the database connection
                mysqli_close($conn);
            }
            ?>
        </div>
    </div>
</body>
</html>
