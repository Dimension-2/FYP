<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "fyp");
if (!isset($_SESSION['registration_no'])) { header("Location: login.php"); exit(); }

// CHANGE: Changed $_GET to $_POST to match your table form
if (isset($_POST['id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $student_reg = $_SESSION['registration_no'];
    
    // 1. Fetch file path to delete file from folder
    $file_query = "SELECT file_path FROM research_submissions WHERE id = '$id' AND registration_no = '$student_reg'";
    $file_result = mysqli_query($conn, $file_query);
    $file_data = mysqli_fetch_assoc($file_result);
    
    if ($file_data) {
        $path = $file_data['file_path'];
        if (file_exists($path)) {
            unlink($path); 
        }
        
        // 2. Delete record from database
        $delete_sql = "DELETE FROM research_submissions WHERE id = '$id' AND registration_no = '$student_reg'";
        if (mysqli_query($conn, $delete_sql)) {
            echo "<script>alert('Submission deleted successfully'); window.location='research_review.php';</script>";
        } else {
            echo "Error deleting record: " . mysqli_error($conn);
        }
    } else {
        echo "<script>alert('Error: Submission not found or unauthorized.'); window.location='research_review.php';</script>";
    }
}
?>