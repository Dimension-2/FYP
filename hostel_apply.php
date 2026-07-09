<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Access Control
if (!isset($_SESSION['registration_no'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Apply for Hostel";
$student_reg = $_SESSION['registration_no'];
$message = "";

// Auto-Schema Migration: Verify and append required mess tracking columns safely
$check_col1 = $conn->query("SHOW COLUMNS FROM hostel_applications LIKE 'include_mess'");
if ($check_col1->num_rows == 0) {
    $conn->query("ALTER TABLE hostel_applications ADD include_mess TINYINT(1) DEFAULT 0 AFTER location_zone");
}
$check_col2 = $conn->query("SHOW COLUMNS FROM hostel_vouchers LIKE 'mess_fee'");
if ($check_col2->num_rows == 0) {
    $conn->query("ALTER TABLE hostel_vouchers ADD mess_fee INT DEFAULT 0 AFTER card_charges");
}

// Check if user has already applied
$check_stmt = $conn->prepare("SELECT id FROM hostel_applications WHERE registration_no = ? ORDER BY id DESC LIMIT 1");
$check_stmt->bind_param("s", $student_reg);
$check_stmt->execute();
$has_applied = $check_stmt->get_result()->num_rows > 0;
$check_stmt->close();

// Fetch the payment status of their latest hostel voucher
$voucher_status = "Unpaid";
if ($has_applied) {
    $v_stmt = $conn->prepare("SELECT status FROM hostel_vouchers WHERE registration_no = ? ORDER BY id DESC LIMIT 1");
    $v_stmt->bind_param("s", $student_reg);
    $v_stmt->execute();
    $v_res = $v_stmt->get_result();
    if ($v_res && $v_res->num_rows > 0) {
        $voucher_status = $v_res->fetch_assoc()['status'];
    }
    $v_stmt->close();
}

// Allow re-applying regardless of whether they are Paid or Unpaid
$is_reapplying = isset($_GET['reapply']) && $_GET['reapply'] == 'true';

// 2. Handle Form Submission (With Proper Re-Apply Logic)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check their latest voucher status
    $check_paid_stmt = $conn->prepare("SELECT status FROM hostel_vouchers WHERE registration_no = ? ORDER BY id DESC LIMIT 1");
    $check_paid_stmt->bind_param("s", $student_reg);
    $check_paid_stmt->execute();
    $paid_res = $check_paid_stmt->get_result();

    if ($paid_res->num_rows > 0) {
        $current_status = $paid_res->fetch_assoc()['status'];

        // If they had an UNPAID voucher, delete the old unpaid app/voucher to keep DB clean
        // If they were PAID, we DO NOT delete the old ones (financial records), just insert new ones.
        if ($current_status !== 'Paid') {
            $clean_v = $conn->prepare("DELETE FROM hostel_vouchers WHERE registration_no = ? AND status = 'Unpaid'");
            $clean_v->bind_param("s", $student_reg);
            $clean_v->execute();
            $clean_v->close();

            $clean_a = $conn->prepare("DELETE FROM hostel_applications WHERE registration_no = ? ORDER BY id DESC LIMIT 1");
            $clean_a->bind_param("s", $student_reg);
            $clean_a->execute();
            $clean_a->close();
        }
    }
    $check_paid_stmt->close();

    // Fetch new preferences from form
    $room_type = $_POST['room_type'] ?? 'Double';
    $location_zone = $_POST['location_zone'] ?? 'None';
    $include_mess = isset($_POST['include_mess']) ? 1 : 0;

    // 1. Insert newly updated application
    $stmt = $conn->prepare("INSERT INTO hostel_applications (registration_no, room_type, location_zone, include_mess) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $student_reg, $room_type, $location_zone, $include_mess);
    $stmt->execute();
    $stmt->close();

    // 2. Calculate Perfect Tier Amounts dynamically
    $room_rates = ['Single' => 15000, 'Double' => 10000, 'Triple' => 7500];
    $route_rates = ['Zone A' => 3000, 'Zone B' => 5000, 'None' => 0];
    $security_fee = ($room_rates[$room_type] ?? 10000) + ($route_rates[$location_zone] ?? 0);
    $card_charges = 500;
    $mess_fee = $include_mess ? 12000 : 0;
    $total_payable = $security_fee + $card_charges + $mess_fee;

    // 3. Generate Voucher Data
    $challan_no = "HSEC-" . preg_replace("/[^A-Za-z0-9]/", "", $student_reg) . "-" . time();
    $student_name = $_SESSION['student_name'] ?? $_SESSION['name'] ?? "Student";
    $due_date = date('Y-m-d', strtotime('last day of this month'));

    // 4. Save newly updated voucher
    $v_stmt = $conn->prepare("INSERT INTO hostel_vouchers (challan_no, registration_no, student_name, due_date, security_fee, card_charges, mess_fee, total_payable, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Unpaid')");
    $v_stmt->bind_param("ssssdddd", $challan_no, $student_reg, $student_name, $due_date, $security_fee, $card_charges, $mess_fee, $total_payable);
    $v_stmt->execute();
    $v_stmt->close();

    // Redirect to the newly generated and saved voucher
    header("Location: hostel_sec_voucher.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Application - University of Wah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <style>
        .application-card {
            max-width: 600px;
            margin: 0 auto;
            border-radius: 12px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .price-badge {
            font-size: 0.85rem;
            background-color: #f8f9fa;
            color: #198754;
            border: 1px solid #198754;
            padding: 3px 8px;
            border-radius: 20px;
            font-weight: bold;
        }

        .cursor-pointer {
            cursor: pointer;
        }
    </style>
</head>

<body class="bg-light">

    <div class="main-wrapper d-flex">
        <?php include('includes/navbar.php'); ?>

        <div class="content-area flex-grow-1">
            <?php include('includes/header.php'); ?>

            <div class="container-fluid px-4 mt-5">
                <?php echo $message; ?>

                <div class="card application-card shadow-sm bg-white">
                    <div class="card-header bg-dark text-white p-4 text-center border-0"
                        style="border-radius: 12px 12px 0 0;">
                        <i class="bi bi-building fs-1 mb-2 d-block"></i>
                        <h4 class="mb-0 fw-bold">Hostel Allotment Form</h4>
                        <p class="text-light small mb-0 mt-1">Select your accommodation preferences</p>
                    </div>

                    <div class="card-body p-4 border-0">
                        <?php if ($has_applied && !$is_reapplying): ?>
                            <div class="text-center py-4">
                                <?php if ($voucher_status == 'Paid'): ?>
                                    <i class="bi bi-check-circle-fill text-success mb-3" style="font-size: 4rem;"></i>
                                    <h4 class="fw-bold text-dark">Allotment Confirmed</h4>
                                    <p class="text-muted mb-2">Your hostel application has been approved and payment is
                                        verified.</p>
                                    <span class="badge bg-success px-3 py-2 rounded-pill fw-bold mb-4"><i
                                            class="bi bi-shield-check me-1"></i> PAID & VERIFIED</span>

                                    <div class="mt-2">
                                        <a href="hostel_sec_voucher.php"
                                            class="btn btn-success w-100 mb-3 py-2 fw-bold shadow-sm">
                                            <i class="bi bi-receipt me-2"></i> View Payment Receipt
                                        </a>
                                        <!-- ADDED RE-APPLY BUTTON FOR PAID USERS -->
                                        <a href="hostel_apply.php?reapply=true"
                                            class="btn btn-outline-primary w-100 py-2 fw-bold small">
                                            <i class="bi bi-arrow-clockwise me-2"></i> Re-Apply (Change Preferences / New Term)
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <i class="bi bi-lock-fill text-warning mb-3" style="font-size: 4rem;"></i>
                                    <h4 class="fw-bold text-dark">Application Pending Payment</h4>
                                    <p class="text-muted mb-2">You have submitted your hostel application. Your active voucher
                                        is awaiting verification.</p>
                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-4"><i
                                            class="bi bi-clock me-1"></i> AWAITING PAYMENT</span>

                                    <div class="mt-2">
                                        <a href="hostel_sec_voucher.php"
                                            class="btn btn-primary w-100 mb-3 py-2 fw-bold shadow-sm">
                                            <i class="bi bi-receipt me-2"></i> View & Upload Voucher Slip
                                        </a>
                                        <!-- EXISTING RE-APPLY BUTTON FOR UNPAID USERS -->
                                        <a href="hostel_apply.php?reapply=true"
                                            class="btn btn-outline-danger w-100 py-2 fw-bold small">
                                            <i class="bi bi-arrow-clockwise me-2"></i> Re-Apply (Change Package Preferences)
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <form action="" method="POST">

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark"><i class="bi bi-door-open me-2"></i>Select
                                        Room Type</label>
                                    <div class="list-group">
                                        <label
                                            class="list-group-item d-flex justify-content-between align-items-center p-3 cursor-pointer">
                                            <div>
                                                <input class="form-check-input me-2" type="radio" name="room_type"
                                                    value="Single" required>
                                                Single Occupancy (Private)
                                            </div>
                                            <span class="price-badge">+ PKR 15,000</span>
                                        </label>
                                        <label
                                            class="list-group-item d-flex justify-content-between align-items-center p-3 cursor-pointer bg-light">
                                            <div>
                                                <input class="form-check-input me-2" type="radio" name="room_type"
                                                    value="Double" checked>
                                                Double Occupancy (Shared)
                                            </div>
                                            <span class="price-badge">+ PKR 10,000</span>
                                        </label>
                                        <label
                                            class="list-group-item d-flex justify-content-between align-items-center p-3 cursor-pointer">
                                            <div>
                                                <input class="form-check-input me-2" type="radio" name="room_type"
                                                    value="Triple">
                                                Triple Occupancy (Shared)
                                            </div>
                                            <span class="price-badge">+ PKR 7,500</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark"><i
                                            class="bi bi-bus-front me-2"></i>University Transport (Optional)</label>
                                    <div class="list-group">
                                        <label
                                            class="list-group-item d-flex justify-content-between align-items-center p-3 cursor-pointer bg-light">
                                            <div>
                                                <input class="form-check-input me-2" type="radio" name="location_zone"
                                                    value="None" checked>
                                                No Transport Required
                                            </div>
                                            <span class="badge bg-secondary rounded-pill">PKR 0</span>
                                        </label>
                                        <label
                                            class="list-group-item d-flex justify-content-between align-items-center p-3 cursor-pointer">
                                            <div>
                                                <input class="form-check-input me-2" type="radio" name="location_zone"
                                                    value="Zone A">
                                                Zone A (Local City Routes)
                                            </div>
                                            <span class="price-badge">+ PKR 3,000</span>
                                        </label>
                                        <label
                                            class="list-group-item d-flex justify-content-between align-items-center p-3 cursor-pointer">
                                            <div>
                                                <input class="form-check-input me-2" type="radio" name="location_zone"
                                                    value="Zone B">
                                                Zone B (Outside City Routes)
                                            </div>
                                            <span class="price-badge">+ PKR 5,000</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark"><i class="bi bi-cup-hot me-2"></i>Hostel
                                        Mess Facility</label>
                                    <div class="card p-3 border cursor-pointer bg-light">
                                        <div
                                            class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                            <div class="ms-5">
                                                <input class="form-check-input ms-n5" type="checkbox" name="include_mess"
                                                    value="1" id="messSwitch" checked>
                                                <label class="form-check-label fw-bold" for="messSwitch">Subscribe to
                                                    Monthly Mess Plan</label>
                                                <div class="text-muted small">Includes 3 daily healthy meals prepared inside
                                                    the university hostel mess.</div>
                                            </div>
                                            <span class="price-badge">+ PKR 12,000 / mon</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-warning small border-0 d-flex mb-4">
                                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                    <div>
                                        <strong>Note:</strong> A standard Hostel Card administration fee of PKR 500 will be
                                        added to your final voucher. Submitting this form will automatically generate your
                                        payment challan.
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm">
                                    <?php echo $is_reapplying ? 'Update Preferences & Generate New Voucher' : 'Submit Application & Generate Voucher'; ?>
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="pb-5"></div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </div>
</body>

</html>