<?php
session_start();

if (!isset($_SESSION['registration_no'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ==========================================
// 1. AUTO-SCHEMA FIX (Added total_payable)
// ==========================================
$conn->query("ALTER TABLE fee_vouchers ADD COLUMN IF NOT EXISTS credit_hours INT DEFAULT 0 AFTER semester_label");
$conn->query("ALTER TABLE fee_vouchers ADD COLUMN IF NOT EXISTS tuition_fee DOUBLE DEFAULT 0 AFTER credit_hours");
$conn->query("ALTER TABLE fee_vouchers ADD COLUMN IF NOT EXISTS allied_charges DOUBLE DEFAULT 12000 AFTER tuition_fee");
$conn->query("ALTER TABLE fee_vouchers ADD COLUMN IF NOT EXISTS exam_fee DOUBLE DEFAULT 5000 AFTER allied_charges");
$conn->query("ALTER TABLE fee_vouchers ADD COLUMN IF NOT EXISTS security_fee DOUBLE DEFAULT 7000 AFTER exam_fee");
$conn->query("ALTER TABLE fee_vouchers ADD COLUMN IF NOT EXISTS scholarship_amount DOUBLE DEFAULT 0 AFTER security_fee");
$conn->query("ALTER TABLE fee_vouchers ADD COLUMN IF NOT EXISTS total_payable DOUBLE DEFAULT 0 AFTER scholarship_amount");
$conn->query("ALTER TABLE fee_vouchers ADD COLUMN IF NOT EXISTS extended_date DATE NULL AFTER due_date");

$student_reg = $conn->real_escape_string($_SESSION['registration_no']);

// ==========================================
// 2. FETCH REAL DATA FROM 'profile' TABLE
// ==========================================
$profile_stmt = $conn->query("SELECT * FROM profile WHERE registration_no = '$student_reg' LIMIT 1");
if ($profile_stmt && $profile_stmt->num_rows > 0) {
    $student_profile = $profile_stmt->fetch_assoc();
    $student_name = $student_profile['full_name'] ?? $student_profile['name'] ?? "Student";
    $current_semester = $student_profile['semester'] ?? '1';
} else {
    $student_name = "Unknown Student";
    $current_semester = '1';
}
$semester_label = "Semester " . $current_semester;

// ==========================================
// 3. FETCH COURSE CREDIT HOURS
// ==========================================
$courses_sql = "SELECT SUM(credit_hours) as total_hours FROM course_assignments WHERE semester = '$current_semester'";
$courses_res = $conn->query($courses_sql);
$total_credit_hours = 0;
if ($courses_res) {
    $row = $courses_res->fetch_assoc();
    $total_credit_hours = intval($row['total_hours'] ?? 0);
}

// ==========================================
// 4. CHECK FOR SCHOLARSHIPS
// ==========================================
$scholarship_sql = "SELECT amount FROM scholarships WHERE registration_no = '$student_reg' LIMIT 1";
$scholarship_res = $conn->query($scholarship_sql);
$allocated_scholarship = 0;
if ($scholarship_res && $scholarship_res->num_rows > 0) {
    $allocated_scholarship = doubleval($scholarship_res->fetch_assoc()['amount']);
}

// ==========================================
// 5. FETCH OR GENERATE VOUCHER WITH NEW FEES
// ==========================================
$v_query = "SELECT * FROM fee_vouchers WHERE registration_no = '$student_reg' ORDER BY id DESC LIMIT 1";
$v_res = $conn->query($v_query);

if ($v_res && $v_res->num_rows > 0) {
    $v_data = $v_res->fetch_assoc();
    $voucher_no = $v_data['voucher_no'];

    // Dynamically Sync calculations to active record structure
    $tuition_fee = $total_credit_hours * 5500;
    $allied_charges = 12000;
    $exam_fee = 5000;
    $security_fee = 7000;
    $total_fee = ($tuition_fee + $allied_charges + $exam_fee + $security_fee) - $allocated_scholarship;
    if ($total_fee < 0)
        $total_fee = 0;

    $conn->query("UPDATE fee_vouchers SET credit_hours=$total_credit_hours, tuition_fee=$tuition_fee, allied_charges=$allied_charges, exam_fee=$exam_fee, security_fee=$security_fee, scholarship_amount=$allocated_scholarship, total_payable=$total_fee WHERE id=" . $v_data['id']);
} else {
    // Generate base record on the fly for visualization
    $voucher_no = "UOW-" . rand(100000, 999999);
    $tuition_fee = $total_credit_hours * 5500;
    $allied_charges = 12000;
    $exam_fee = 5000;
    $security_fee = 7000;
    $total_fee = ($tuition_fee + $allied_charges + $exam_fee + $security_fee) - $allocated_scholarship;
    if ($total_fee < 0)
        $total_fee = 0;

    $issue_date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime('+14 days'));

    $conn->query("INSERT INTO fee_vouchers (voucher_no, registration_no, semester_label, credit_hours, tuition_fee, allied_charges, exam_fee, security_fee, scholarship_amount, total_payable, issue_date, due_date, status) VALUES ('$voucher_no', '$student_reg', '$semester_label', $total_credit_hours, $tuition_fee, $allied_charges, $exam_fee, $security_fee, $allocated_scholarship, $total_fee, '$issue_date', '$due_date', 'Unpaid')");

    $v_data = ['voucher_no' => $voucher_no, 'due_date' => $due_date, 'extended_date' => null, 'semester_label' => $semester_label, 'status' => 'Unpaid'];
}

$display_due_date = (!empty($v_data['extended_date'])) ? $v_data['extended_date'] : $v_data['due_date'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Semester Voucher Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <style>
        .voucher-wrapper {
            display: flex;
            flex-direction: row;
            gap: 15px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
        }

        .voucher-copy {
            flex: 1;
            min-width: 300px;
            min-height: 700px;
            display: flex;
            flex-direction: column;
            border: 2px dashed #000;
            padding: 15px;
            background: #fff;
            position: relative;
        }

        .copy-label {
            position: absolute;
            top: 10px;
            right: 15px;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            background: #eee;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .voucher-logo {
            height: 60px;
            object-fit: contain;
        }

        .x-small {
            font-size: 11px;
        }

        /* Forces the signatures to stay at the very bottom */
        .spacer {
            flex-grow: 1;
        }

        .signature-box {
            border-top: 1px solid #000;
            width: 45%;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            padding-top: 5px;
        }

        /* Optimized for Landscape Printing */
        @media print {
            @page {
                size: landscape;
                margin: 5mm;
            }

            .no-print {
                display: none !important;
            }

            .voucher-wrapper {
                flex-direction: row;
                gap: 10px;
                padding: 0;
                box-shadow: none !important;
                background: transparent;
            }

            .voucher-copy {
                page-break-inside: avoid;
                min-width: auto;
                min-height: 95vh;
            }

            body,
            .main-wrapper,
            .content-area {
                background: white !important;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>

<body class="bg-light">
    <div class="main-wrapper d-flex">
        <?php include('includes/navbar.php'); ?>
        <div class="content-area flex-grow-1">
            <?php include('includes/header.php'); ?>
            <div class="container-fluid px-4 mt-4">

                <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                    <div>
                        <h3 class="fw-bold m-0">Official Semester Voucher Desk</h3>
                        <p class="text-muted small mb-0">Dynamic calculations based on current course catalog mappings.
                        </p>
                    </div>
                    <div>
                        <a href="submit_scholarship_request.php" class="btn btn-outline-primary me-2"><i
                                class="bi bi-file-earmark-arrow-up me-1"></i> Apply for Scholarship</a>
                        <button class="btn btn-dark shadow-sm" onclick="window.print()"><i
                                class="bi bi-printer me-1"></i> Print Stack Copy</button>
                    </div>
                </div>

                <div class="voucher-wrapper shadow-sm mb-5">
                    <?php foreach (["Bank Copy", "University Copy", "Student Copy"] as $copy_title): ?>
                        <div class="voucher-copy">
                            <div class="copy-label"><?php echo $copy_title; ?></div>
                            <div class="text-center border-bottom pb-2 mb-3">
                                <img src="https://uow.edu.pk/assets/images/logo.png" class="voucher-logo mb-2"
                                    alt="UOW Logo">
                                <h5 class="fw-bold m-0">UNIVERSITY OF WAH</h5>
                                <small class="text-muted">Dynamic Semester Academic Challan Stack</small>
                            </div>

                            <?php 
                                // Determine the dynamic badge color based on admin's status
                                $status_badge = 'bg-danger'; 
                                if ($v_data['status'] == 'Paid') $status_badge = 'bg-success';
                                elseif ($v_data['status'] == 'Pending Verification') $status_badge = 'bg-warning text-dark';
                            ?>
                            <div class="row x-small mb-3">
                                <div class="col-6">
                                    <strong>Voucher ID:</strong> <?php echo $voucher_no; ?><br>
                                    <strong>Reg No:</strong> <?php echo htmlspecialchars($student_reg); ?><br>
                                    <strong>Student Name:</strong> <?php echo htmlspecialchars($student_name); ?><br>
                                    <strong class="mt-1 d-inline-block">Payment Status:</strong> 
                                    <span class="badge <?php echo $status_badge; ?> border text-uppercase" style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
                                        <?php echo $v_data['status']; ?>
                                    </span>
                                </div>
                                <div class="col-6 text-end">
                                    <strong>Target Group:</strong> <?php echo $v_data['semester_label']; ?><br>
                                    <strong>Total Enrolled Credit Hours:</strong> <?php echo $total_credit_hours; ?><br>
                                    <strong class="text-danger">Valid Date Due:
                                        <?php echo date('d-M-Y', strtotime($display_due_date)); ?></strong>
                                    <?php if (!empty($v_data['extended_date'])): ?>
                                        <span class="badge bg-warning text-dark d-block mt-1">Admin Extension Applied</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <table class="table table-sm table-bordered x-small mb-3">
                                <thead class="table-light">
                                    <tr>
                                        <th>Description Base</th>
                                        <th>Rate Metric</th>
                                        <th class="text-end">Computed Gross Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Tuition Base Fee Component</td>
                                        <td><?php echo $total_credit_hours; ?> Cr. Hrs × 5,500 PKR</td>
                                        <td class="text-end"><?php echo number_format($tuition_fee); ?> PKR</td>
                                    </tr>
                                    <tr>
                                        <td>Bank Allied Mandatory Operations Charges</td>
                                        <td>Flat Rate Fixed Component</td>
                                        <td class="text-end"><?php echo number_format($allied_charges); ?> PKR</td>
                                    </tr>
                                    <tr>
                                        <td>Examination & Evaluation Fee</td>
                                        <td>Standard Fixed Term Fee</td>
                                        <td class="text-end"><?php echo number_format($exam_fee); ?> PKR</td>
                                    </tr>
                                    <tr>
                                        <td>University Security & Maintenance</td>
                                        <td>Standard Fixed Term Fee</td>
                                        <td class="text-end"><?php echo number_format($security_fee); ?> PKR</td>
                                    </tr>
                                    <?php if ($allocated_scholarship > 0): ?>
                                        <tr class="table-success text-success fw-bold">
                                            <td>Approved Institutional Scholarship Waiver Deduction</td>
                                            <td>Assigned Award Remittance Deduction</td>
                                            <td class="text-end">- <?php echo number_format($allocated_scholarship); ?> PKR</td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr class="fw-bold table-dark text-white">
                                        <td colspan="2">Net Total Payable Liquid Asset Value</td>
                                        <td class="text-end"><?php echo number_format($total_fee); ?> PKR</td>
                                    </tr>
                                </tbody>
                            </table>
                            <p class="x-small text-muted m-0">Note: Payments are accepted across all collection lines of
                                Allied Bank and Bank Alfalah networks.</p>

                            <div class="spacer"></div>
                            <div class="d-flex justify-content-between align-items-end mt-5 pb-2">
                                <div class="signature-box">Bank Officer Sign / Stamp</div>
                                <div class="signature-box">Cashier Sign</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>
    </div>
</body>

</html>