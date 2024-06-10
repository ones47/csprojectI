<?php
require('fpdf/fpdf.php');
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $class_id = $_POST['class'];

    // Fetch students based on selected class year
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
    mysqli_stmt_bind_param($stmt, "s", $class_id);
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

    // Create a new PDF document
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->AddFont('Arial', '', 'arial.php'); // Add Arial font
    $pdf->SetFont('Arial', 'B', 16); // Use Arial font

    // Title
    $pdf->Cell(0, 10, "Class $class_id Students", 0, 1, 'C');

    // Header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'First Name', 1);
    $pdf->Cell(40, 10, 'Last Name', 1);
    $pdf->Cell(40, 10, 'Class', 1);
    $pdf->Cell(60, 10, 'Date of Birth', 1);
    $pdf->Ln();

    // Data
    $pdf->SetFont('Arial', '', 12);
    foreach ($students as $student) {
        $pdf->Cell(40, 10, $student['fname'], 1);
        $pdf->Cell(40, 10, $student['lname'], 1);
        $pdf->Cell(40, 10, $student['class'], 1);
        $pdf->Cell(60, 10, $student['dob'], 1);
        $pdf->Ln();
    }

    // Output the PDF
    $pdf->Output('D', "Class_{$class_id}_Students.pdf");
}
?>
