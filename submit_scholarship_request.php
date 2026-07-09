<?php
session_start();
if (!isset($_SESSION['registration_no'])) { $_SESSION['registration_no'] = "UW-25M-CYS-MS-001"; }

$conn = new mysqli("localhost", "root", "", "fyp");
$success = false;

// Auto-Schema setup for application requests
$conn->query("CREATE TABLE IF NOT EXISTS scholarship_requests (id INT AUTO_INCREMENT PRIMARY KEY, registration_no VARCHAR(50), document_path VARCHAR(255), results_path VARCHAR(255), request_status VARCHAR(50), submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reg_no = $conn->real_escape_string($_SESSION['registration_no']);
    
    $upload_dir = "uploads/scholarships/";
    if (!file_exists($upload_dir)) { mkdir($upload_dir, 0777, true); }
    
    $doc_file = $upload_dir . "doc_" . time() . "_" . basename($_FILES["doc_file"]["name"]);
    $res_file = $upload_dir . "res_" . time() . "_" . basename($_FILES["res_file"]["name"]);
    
    if (move_uploaded_file($_FILES["doc_file"]["tmp_name"], $doc_file) && move_uploaded_file($_FILES["res_file"]["tmp_name"], $res_file)) {
        $conn->query("INSERT INTO scholarship_requests (registration_no, document_path, results_path, request_status) VALUES ('$reg_no', '$doc_file', '$res_file', 'Pending Assessment')");
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply for Fee Relief Voucher Waiver</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
</head>
<body class="bg-light">
    <div class="main-wrapper d-flex">
        <?php include('includes/navbar.php'); ?>
        <div class="content-area flex-grow-1">
            <?php include('includes/header.php'); ?>
            <div class="container mt-5" style="max-width: 600px;">
                <div class="card border-0 shadow-sm p-4">
                    <h4 class="fw-bold mb-3 text-success"><i class="bi bi-file-earmark-medical me-2"></i>Scholarship Assistance Vault</h4>
                    <p class="text-muted small">Submit your official verifying documents alongside cumulative current semester grading cards for evaluation.</p>
                    <hr>
                    <?php if($success): ?>
                        <div class="alert alert-success">Application profile vectors transmitted successfully! Management will review and update your voucher.</div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Verifiable Need/Merit Proof Files (PDF/ZIP)</label>
                            <input type="file" class="form-control form-control-sm" name="doc_file" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small">Official Semester Grade Sheet Copies (PDF/Images)</label>
                            <input type="file" class="form-control form-control-sm" name="res_file" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold">Submit Financial Review Application</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>