<?php
$current_page = 'faculty.php';
include '../includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/navbar.css">
    <link rel="stylesheet" href="../assets/faculty.css">
    <style>
        .success-card {
            text-align: center;
            padding: 50px;
        }
        /* Animated Checkmark */
        .checkmark-wrapper {
            width: 100px;
            height: 100px;
            background: #e0f7f4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        .checkmark-icon {
            font-size: 50px;
            color: #00cba9;
            animation: scaleUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes scaleUp {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }
        .btn-return {
            background-color: #00cba9;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .btn-return:hover {
            background-color: #009e84;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 203, 169, 0.3);
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="content-container d-flex align-items-center justify-content-center">
        <div class="content-card success-card" style="max-width: 500px;">
            <div class="checkmark-wrapper">
                <i class="bi bi-check-lg checkmark-icon"></i>
            </div>
            <h2 class="fw-bold mb-3">Thank You!</h2>
            <p class="text-muted mb-4">Your feedback has been successfully submitted. Your honest input helps us improve the academic experience.</p>
            <a href="../faculty.php" class="btn-return">Back to Dashboard</a>
        </div>
    </div>
</div>

</body>
</html>