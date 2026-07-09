<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

if (!isset($_SESSION['registration_no'])) { header("Location: login.php"); exit(); }

$student_reg = $_SESSION['registration_no'];
$message = "";

// Check if they have an approved hostel
// 1. Verify if student has applied for a hostel
    $hostel_check = $conn->prepare("SELECT id FROM hostel_applications WHERE registration_no = ? LIMIT 1");
    $hostel_check->bind_param("s", $student_reg);
    $hostel_check->execute();
    $hostel_res = $hostel_check->get_result();
    $has_hostel = ($hostel_res && $hostel_res->num_rows > 0);
if (!$has_hostel) {
    die("Error: You must have an approved hostel application to join the mess.");
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_name = $_SESSION['student_name'] ?? 'Student';
    $month = date('F Y');
    $challan_no = "HMES-" . preg_replace("/[^A-Za-z0-9]/", "", $student_reg) . "-" . time();
    $mess_charges = 12000; // Default monthly rate
    
    $stmt = $conn->prepare("INSERT INTO hostel_mess_vouchers (registration_no, student_name, challan_no, billing_month, mess_charges, total_payable, status) VALUES (?, ?, ?, ?, ?, ?, 'Unpaid')");
    $stmt->bind_param("ssssii", $student_reg, $student_name, $challan_no, $month, $mess_charges, $mess_charges);
    
    if ($stmt->execute()) {
        header("Location: hostel_mess_voucher.php");
        exit();
    } else {
        $message = "<div class='alert alert-danger'>Error processing application.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Join Hostel Mess</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 600px;">
        <div class="card shadow-sm border-0 p-4">
            <h3 class="fw-bold mb-3">Join Hostel Mess</h3>
            <p class="text-muted">By joining the mess, a monthly voucher of PKR 12,000 will be automatically generated to your account.</p>
            <?php echo $message; ?>
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label fw-bold">Select Start Month</label>
                    <input type="text" class="form-control bg-light" value="<?php echo date('F Y'); ?>" readonly>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" required id="agree">
                    <label class="form-check-label small text-muted" for="agree">
                        I agree to pay my mess dues by the 10th of every month. I understand that late payments will incur fines.
                    </label>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold">Confirm & Generate First Voucher</button>
                <a href="hostel_mess_voucher.php" class="btn btn-light w-100 mt-2">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>