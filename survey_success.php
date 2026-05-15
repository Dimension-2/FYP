<?php 
    
    session_start();
    $page_title = "Submission Successful"; 
    
    // Get the ID from the session, or show a fallback if empty
    $ref_no = $_SESSION['last_survey_ref'] ?? "HEC-PENDING";
    
    // Optional: Clear it so it doesn't show up again later
    // unset($_SESSION['last_survey_ref']); 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success - HEC Student Survey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <link rel="stylesheet" href="assets/student_surveys.css">
</head>
<body class="bg-light">

<div class="main-wrapper d-flex">
    <div class="no-print">
        <?php include('includes/navbar.php'); ?>
    </div>

    <div class="content-area flex-grow-1">
        <div class="no-print">
            <?php include('includes/header.php'); ?>
        </div>

        <div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh;">
            <div class="card border-0 shadow-lg text-center p-5" style="max-width: 600px; border-radius: 20px;">
                <div class="success-icon-wrapper mb-4">
                    <i class="bi bi-check-circle-fill text-teal" style="font-size: 5rem;"></i>
                </div>
                
                <h2 class="fw-bold text-dark mb-2">Thank You!</h2>
                <p class="text-muted mb-4">Your response for the <strong>HEC Student Survey</strong> has been successfully submitted. Your feedback helps us improve the quality of online education.</p>
                
                <div class="bg-light p-3 rounded-3 mb-4 border">
                    <span class="text-muted small d-block mb-1">REFERENCE NUMBER</span>
                    <span class="fw-bold text-primary h5"><?php echo $ref_no; ?></span>
                </div>

                <div class="d-grid gap-2 no-print">
                    <a href="profile.php" class="btn btn-teal-lg py-3">
                        <i class="bi bi-house-door me-2"></i> Back to Dashboard
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-secondary border-0 small">
                        <i class="bi bi-printer me-1"></i> Print Confirmation
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>