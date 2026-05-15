<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_survey'])) {
    
    // 1. Capture and match your Database Columns
    $full_name       = $_POST['full_name'];
    $father_name     = $_POST['father_name'];
    $cnic            = $_POST['cnic'];
    $reg_no          = $_POST['reg_no']; // Will save to registration_no
    $degree          = $_POST['degree'];
    $email           = $_POST['email'];
    $satisfaction    = $_POST['satisfaction'];
    $suggestions     = $_POST['suggestions'];
    // Add other fields as needed...

    // 2. Database Insertion
    $sql = "INSERT INTO student_surveys (full_name, father_name, cnic, registration_no, degree_level, email, satisfaction, suggestions) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $full_name, $father_name, $cnic, $reg_no, $degree, $email, $satisfaction, $suggestions);

    if ($stmt->execute()) {
        // 3. Redirect to success page on success
        $ref_no = "HEC-" . strtoupper(substr(md5(time()), 0, 8));
        $_SESSION['last_survey_ref'] = $ref_no;
        header("Location: survey_success.php?status=success");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();

} else {
    // Redirect back if accessed directly
    header("Location: student_survey.php");
    exit();
}
?>