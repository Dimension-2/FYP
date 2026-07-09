<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['registration_no'])) {
    header("Location: login.php");
    exit();
}

$student_reg = $_SESSION['registration_no'];
$student_name = $_SESSION['student_name'] ?? $_SESSION['name'] ?? "Student";

// Auto-Schema Setup for Transport Applications
$conn->query("CREATE TABLE IF NOT EXISTS transport_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_no VARCHAR(50),
    student_name VARCHAR(100),
    route_name VARCHAR(100),
    pickup_shift VARCHAR(50),
    opt_status VARCHAR(20) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Auto-Schema Update: Add missing due_date column to transport_vouchers safely
$check_date = $conn->query("SHOW COLUMNS FROM transport_vouchers LIKE 'due_date'");
if ($check_date && $check_date->num_rows == 0) {
    $conn->query("ALTER TABLE transport_vouchers ADD due_date DATE NULL AFTER total_payable");
}

// Check user's current status
$stmt = $conn->prepare("SELECT opt_status FROM transport_applications WHERE registration_no = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("s", $student_reg);
$stmt->execute();
$res = $stmt->get_result();
$current_status = $res->num_rows > 0 ? $res->fetch_assoc()['opt_status'] : 'Pending';
$stmt->close();

// Handle Form Submissions (Opt-Out or Apply)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'opt_out') {
        $stmt = $conn->prepare("INSERT INTO transport_applications (registration_no, student_name, opt_status) VALUES (?, ?, 'Declined')");
        $stmt->bind_param("ss", $student_reg, $student_name);
        $stmt->execute();
        header("Location: transport_apply.php");
        exit();
    }

    if ($action === 'apply') {
        $route = $_POST['route'] ?? 'Local City';
        $shift = $_POST['shift'] ?? 'Morning (7:30 AM)';
        
        // Dynamic Pricing
        $route_prices = [
            'Zone 1 (Local City)' => 4500,
            'Zone 2 (Cantonment Area)' => 6000,
            'Zone 3 (Outskirts / Inter-city)' => 8500
        ];
        $transport_fee = $route_prices[$route] ?? 4500;
        $maintenance_fund = 500;
        $total_payable = $transport_fee + $maintenance_fund;

        // Insert Application
        $stmt = $conn->prepare("INSERT INTO transport_applications (registration_no, student_name, route_name, pickup_shift, opt_status) VALUES (?, ?, ?, ?, 'Active')");
        $stmt->bind_param("ssss", $student_reg, $student_name, $route, $shift);
        $stmt->execute();
        $stmt->close();

        // Generate First Voucher (Due 11th of current month)
        $challan_no = "TRN-" . substr(md5(uniqid()), 0, 8);
        $due_date = date('Y-m-11');
        if (date('d') > 11) {
            // If passed the 11th, set to 11th of next month
            $due_date = date('Y-m-11', strtotime('+1 month'));
        }
        $billing_month = date('F Y', strtotime($due_date));

        $v_stmt = $conn->prepare("INSERT INTO transport_vouchers (challan_no, registration_no, student_name, route_info, semester_name, transport_fee, maintenance_fund, total_payable, due_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Unpaid')");
        $v_stmt->bind_param("ssssssdds", $challan_no, $student_reg, $student_name, $route, $billing_month, $transport_fee, $maintenance_fund, $total_payable, $due_date);
        $v_stmt->execute();
        $v_stmt->close();

        header("Location: transport_voucher.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transport Facility - University of Wah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <style>
        .form-card { max-width: 650px; margin: 0 auto; border-radius: 12px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .route-option { border: 2px solid #eee; border-radius: 8px; padding: 15px; cursor: pointer; transition: 0.2s; }
        .route-option:hover { border-color: #0d6efd; background: #f8fbff; }
        .route-option input[type="radio"]:checked + div { font-weight: bold; color: #0d6efd; }
        .price-tag { font-size: 0.9rem; background: #e9ecef; padding: 4px 10px; border-radius: 20px; font-weight: 600; }
        
        /* Glassmorphism Intent Overlay */
        .intent-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(5px);
            z-index: 1050; display: flex; align-items: center; justify-content: center;
        }
        .intent-box { background: #fff; padding: 40px; border-radius: 16px; max-width: 450px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    </style>
</head>
<body class="bg-light">

    <div class="main-wrapper d-flex">
        <?php include('includes/navbar.php'); ?>
        <div class="content-area flex-grow-1">
            <?php include('includes/header.php'); ?>

            <?php if ($current_status === 'Pending'): ?>
            <div class="intent-overlay">
                <div class="intent-box">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-bus-front fs-1"></i>
                    </div>
                    <h3 class="fw-bold mb-3">University Transport</h3>
                    <p class="text-muted mb-4">Would you like to avail the university's pick & drop transport facility for this semester?</p>
                    <div class="d-flex gap-2">
                        <form method="POST" class="w-50">
                            <input type="hidden" name="action" value="opt_out">
                            <button type="submit" class="btn btn-light w-100 py-2 border fw-bold">No, Skip</button>
                        </form>
                        <button onclick="document.querySelector('.intent-overlay').style.display='none'" class="btn btn-primary w-50 py-2 fw-bold shadow-sm">Yes, Apply Now</button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="container-fluid px-4 mt-5">
                <?php if ($current_status === 'Declined'): ?>
                    <div class="card form-card text-center p-5">
                        <i class="bi bi-x-circle text-muted mb-3" style="font-size: 4rem;"></i>
                        <h4 class="fw-bold">No Transport Availed</h4>
                        <p class="text-muted">You have opted out of the university transport service. If you change your mind, you can re-apply below.</p>
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="action" value="reset">
                            <?php 
                            if(isset($_POST['action']) && $_POST['action'] == 'reset') {
                                $conn->query("UPDATE transport_applications SET opt_status='Pending' WHERE registration_no='$student_reg'");
                                echo "<script>window.location.href='transport_apply.php';</script>";
                            }
                            ?>
                            <button type="submit" class="btn btn-outline-primary px-4"><i class="bi bi-arrow-clockwise me-2"></i>Re-Apply for Transport</button>
                        </form>
                    </div>
                
                <?php elseif ($current_status === 'Active'): ?>
                    <div class="card form-card text-center p-5">
                        <i class="bi bi-check-circle-fill text-success mb-3" style="font-size: 4rem;"></i>
                        <h4 class="fw-bold">Transport Facility Active</h4>
                        <p class="text-muted">You are successfully enrolled in the transport program.</p>
                        <a href="transport_voucher.php" class="btn btn-success px-5 py-2 fw-bold mt-3"><i class="bi bi-receipt me-2"></i> View Monthly Vouchers</a>
                    </div>

                <?php else: ?>
                    <div class="card form-card">
                        <div class="card-header bg-dark text-white p-4 text-center" style="border-radius: 12px 12px 0 0;">
                            <h4 class="mb-0 fw-bold"><i class="bi bi-bus-front me-2"></i>Transport Application Form</h4>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <input type="hidden" name="action" value="apply">
                                
                                <h6 class="fw-bold text-dark mb-3">1. Select Your Route</h6>
                                <div class="d-flex flex-column gap-2 mb-4">
                                    <label class="route-option d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <input class="form-check-input me-3" type="radio" name="route" value="Zone 1 (Local City)" required>
                                            <div>
                                                <div class="fw-semibold">Zone 1 (Local City Routes)</div>
                                                <small class="text-muted">Taxila, Wah Cantt inner sectors</small>
                                            </div>
                                        </div>
                                        <span class="price-tag">PKR 4,500 /mon</span>
                                    </label>
                                    <label class="route-option d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <input class="form-check-input me-3" type="radio" name="route" value="Zone 2 (Cantonment Area)">
                                            <div>
                                                <div class="fw-semibold">Zone 2 (Outer Regions)</div>
                                                <small class="text-muted">Hassanabdal, Barrier 3</small>
                                            </div>
                                        </div>
                                        <span class="price-tag">PKR 6,000 /mon</span>
                                    </label>
                                    <label class="route-option d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <input class="form-check-input me-3" type="radio" name="route" value="Zone 3 (Outskirts / Inter-city)">
                                            <div>
                                                <div class="fw-semibold">Zone 3 (Inter-city)</div>
                                                <small class="text-muted">Rawalpindi, Islamabad Express</small>
                                            </div>
                                        </div>
                                        <span class="price-tag">PKR 8,500 /mon</span>
                                    </label>
                                </div>

                                <h6 class="fw-bold text-dark mb-3">2. Preferred Pickup Shift</h6>
                                <select class="form-select mb-4 py-2" name="shift" required>
                                    <option value="Morning (7:30 AM)">Morning Shift (Arrival 7:30 AM)</option>
                                    <option value="Afternoon (1:00 PM)">Afternoon Shift (Arrival 1:00 PM)</option>
                                </select>

                                <div class="alert alert-warning small border-0 mb-4">
                                    <strong>Billing Policy:</strong> Vouchers are issued on the 1st of every month and are valid until the 11th. A fine of PKR 1,000 applies to overdue bills carried to the next month.
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">Enroll & Generate Voucher <i class="bi bi-arrow-right ms-2"></i></button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>