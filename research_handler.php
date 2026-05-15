<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "fyp");

// 1. Access Control
if (!isset($_SESSION['registration_no'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 2. Use Session for Identity
    $student_reg = $_SESSION['registration_no'];
    $title = mysqli_real_escape_string($conn, $_POST['research_title']);
    $abstract = mysqli_real_escape_string($conn, $_POST['abstract']);
    $submission_date = date('Y-m-d');
    
    // File upload logic
    $target_dir = "uploads/research/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // 3. Rename file using Reg No to prevent overwriting
    $file_extension = strtolower(pathinfo($_FILES["research_file"]["name"], PATHINFO_EXTENSION));
    $file_name = "RES_" . str_replace('-', '_', $student_reg) . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $file_name;

    // Check if file is a PDF
    if($file_extension != "pdf") {
        echo "<script>alert('Only PDF files are allowed.'); window.location='research_review.php';</script>";
        exit();
    }

    if (move_uploaded_file($_FILES["research_file"]["tmp_name"], $target_file)) {
        // 4. Dynamic SQL (Including student_reg)
        // Ensure your 'research_submissions' table has a 'registration_no' column
        $stmt = $conn->prepare("INSERT INTO research_submissions (registration_no, title, abstract, file_path, status, submission_date) VALUES (?, ?, ?, ?, 'Under Review', ?)");
        $stmt->bind_param("sssss", $student_reg, $title, $abstract, $target_file, $submission_date);

        if ($stmt->execute()) {
            echo "<script>alert('Research submitted successfully!'); window.location='research_review.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>