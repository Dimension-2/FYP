<?php 
session_start();

// 1. Session Security Check
if (!isset($_SESSION['registration_no'])) { 
    header("Location: login.php"); 
    exit(); 
}

// Database connection
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Prepare Variables safely
$student_reg = $conn->real_escape_string($_SESSION['registration_no']);
// Use null coalescing to prevent errors if name isn't set
$student_name = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : "N/A"; 

$page_title = "Fee Portal"; 
$upload_success = isset($_GET['success']) ? true : false;

// 3. Fetch Active Voucher (Using Prepared Statement for safety)
$voucher_query = "SELECT voucher_no, tuition_fee, exam_fee, it_charges, due_date, semester_label 
                  FROM fee_vouchers 
                  WHERE registration_no = ? 
                  AND status != 'Paid' 
                  ORDER BY id DESC LIMIT 1";

$stmt = $conn->prepare($voucher_query);
$stmt->bind_param("s", $student_reg);
$stmt->execute();
$v_result = $stmt->get_result();

if ($v_result->num_rows > 0) {
    $v_data = $v_result->fetch_assoc();
    $voucher_no = $v_data['voucher_no'];
    $total_fee = $v_data['tuition_fee'] + $v_data['exam_fee'] + $v_data['it_charges'];
} else {
    $voucher_no = "N/A";
    $total_fee = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Portal - University of Wah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <link rel="stylesheet" href="assets/feevoucher.css">
</head>
<body>

<?php if($upload_success): ?>
<div id="successToast" class="toast show align-items-center text-white bg-success border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 1050;">
  <div class="d-flex">
    <div class="toast-body">
      <i class="bi bi-check-circle-fill me-2"></i> Slip uploaded successfully! Verification in progress.
    </div>
    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
  </div>
</div>
<?php endif; ?>

<div class="main-wrapper d-flex">
    <?php include('includes/navbar.php'); ?>

    <div class="content-area flex-grow-1">
        <?php include('includes/header.php'); ?>

        <div class="container-fluid px-4 mt-4">
            
            <div class="row align-items-center mb-4 no-print">
                <div class="col-md-5">
                    <h3 class="fw-bold text-dark m-0">Student Fee Voucher</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none small">Home</a></li>
                            <li class="breadcrumb-item active small" aria-current="page">Fee Voucher</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-7 text-md-end">
                    <button class="btn btn-outline-dark shadow-sm me-2" type="button" data-bs-toggle="collapse" data-bs-target="#historySection">
                        <i class="bi bi-clock-history me-1"></i> Payment History
                    </button>
                    <button class="btn btn-dark shadow-sm me-2" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print Current Voucher
                    </button>
                    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Upload Paid Slip
                    </button>
                </div>
            </div>

            <div id="currentVoucher" class="voucher-wrapper mb-5">
                <?php 
                $copies = ["Bank Copy", "University Copy", "Student Copy"];
                foreach($copies as $copy_name): 
                ?>
                <div class="voucher-copy shadow-sm">
                    <div class="voucher-header text-center border-bottom pb-2 mb-2">
                        <img src="assets/img/logo.png" alt="Logo" class="voucher-logo mb-1">
                        <h6 class="fw-bold mb-0" style="font-size: 14px;">UNIVERSITY OF WAH</h6>
                        <p class="x-small text-muted mb-1">Wah Cantt, Punjab, Pakistan</p>
                        <div class="copy-label"><?php echo $copy_name; ?></div>
                    </div>

                    <div class="voucher-body">
                        <div class="d-flex justify-content-between x-small mb-2">
                            <span><strong>Voucher No:</strong> <?php echo $voucher_no; ?></span>
<span><strong>Due Date:</strong> <?php echo isset($v_data['due_date']) ? date("d-M-y", strtotime($v_data['due_date'])) : "N/A"; ?></span>
                        </div>
                        
                        <table class="table table-sm table-borderless x-small mb-2 border-bottom">
    <tr>
        <td class="p-0">Registration No:</td>
        <td class="text-end p-0 fw-bold"><?php echo $student_reg; ?></td>
    </tr>
    <tr>
        <td class="p-0">Student Name:</td>
        <td class="text-end p-0 fw-bold"><?php echo $student_name; ?></td>
    </tr>
    <tr>
        <td class="p-0">Program / Sem:</td>
        <td class="text-end p-0">BSCS - <?php echo $v_data['semester_label'] ?? 'N/A'; ?></td>
    </tr>
</table>

                        <table class="table table-sm breakdown-table table-bordered mb-2">
                            <thead>
                                <tr><th>Fee Description</th><th class="text-end">Amount</th></tr>
                            </thead>
                            <tbody>
    <tr>
        <td>Tuition Fee</td>
        <td class="text-end"><?php echo number_format($v_data['tuition_fee'] ?? 0); ?></td>
    </tr>
    <tr>
        <td>Exam & Enrollment Fee</td>
        <td class="text-end"><?php echo number_format($v_data['exam_fee'] ?? 0); ?></td>
    </tr>
    <tr>
        <td>IT / Library Charges</td>
        <td class="text-end"><?php echo number_format($v_data['it_charges'] ?? 0); ?></td>
    </tr>
    <tr class="fw-bold bg-light">
        <td>Total Payable (within due date)</td>
        <td class="text-end text-primary"><?php echo number_format($total_fee); ?></td>
    </tr>
    <tr class="x-small text-danger">
        <td>Late Fee Fine (after due date)</td>
        <td class="text-end"><?php echo number_format($v_data['late_fine'] ?? 1000); ?></td>
    </tr>
</tbody>
                        </table>

                        <div class="bank-details">
                            <strong>Bank Details:</strong><br>
                            Bank Alfalah Ltd (Account: 0044-1003421293)<br>
                            Habib Bank Ltd (Account: 1241-7900123403)
                        </div>
                    </div>

                    <div class="voucher-footer mt-3 d-flex justify-content-between align-items-end">
                        <div class="text-center" style="width: 45%;">
                            <div class="border-top x-small pt-1">Cashier/Bank Stamp</div>
                        </div>
                        <div class="text-center" style="width: 45%;">
                            <div class="border-top x-small pt-1">Officer Signature</div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="collapse no-print" id="historySection">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="fw-bold mb-0">Detailed Payment History</h5>
                        <button class="btn btn-sm btn-outline-dark" onclick="printHistory()">
                            <i class="bi bi-printer me-1"></i> Print History
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" id="printableHistory">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Challan ID</th>
                                        <th>Semester</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Total Fee</th>
                                        <th>Paid Date</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
    <?php
    // Secure Prepared Statement for Payment History
    $hist_stmt = $conn->prepare("SELECT * FROM fee_vouchers WHERE registration_no = ? ORDER BY id DESC");
    $hist_stmt->bind_param("s", $student_reg);
    $hist_stmt->execute();
    $result = $hist_stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $status_class = ($row['status'] == 'Paid' || $row['status'] == 'Verified Paid') ? 'bg-success' : 'bg-warning text-dark';
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['voucher_no'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['semester_label'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['issue_date'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['due_date'] ?? 'N/A') . "</td>";
            echo "<td>" . number_format($row['tuition_fee'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['paid_date'] ?? 'N/A') . "</td>";
            echo "<td class='text-center'><span class='badge $status_class'>" . htmlspecialchars($row['status']) . "</span></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='7' class='text-center py-3 text-muted'>No payment records found.</td></tr>";
    }
    ?>
</tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row no-print g-4 mb-5">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm bg-light border-start border-primary border-4 p-3">
                        <div class="d-flex">
                            <i class="bi bi-shield-check fs-3 text-primary me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Fee Verification Policy</h6>
                                <p class="small text-muted mb-0">Manual slip uploads take <strong>24-48 working hours</strong> to be verified by the accounts department. Online payments via 1Bill update automatically.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Upload Fee Receipt</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="fee_upload.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="voucher_no" value="<?php echo $voucher_no; ?>">
                    <input type="hidden" name="registration_no" value="<?php echo $student_reg; ?>">
                    <input type="hidden" name="amount" value="<?php echo $total_fee; ?>">

                    <div class="alert alert-warning py-2 small">
                        Please ensure the Transaction ID and Date are clearly visible in the image.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Transaction ID / Reference No.</label>
                        <input type="text" class="form-control form-control-sm" name="tid" placeholder="Enter bank TID" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Upload Receipt (Image or PDF)</label>
                        <input type="file" class="form-control form-control-sm" name="slip" accept="image/*,.pdf" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Upload & Notify Accounts</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function printHistory() {
        const historyContent = document.getElementById('printableHistory').innerHTML;
        const printWindow = window.open('', '', 'height=600,width=900');
        printWindow.document.write('<html><head><title>UOW Payment History</title>');
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
        printWindow.document.write('</head><body class="p-5">');
        printWindow.document.write('<div class="text-center mb-4"><h2>University of Wah</h2><h4>Student Payment Ledger</h4></div>');
        printWindow.document.write(historyContent);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }

    // Toast auto-hide
    setTimeout(() => {
        let toastEl = document.getElementById('successToast');
        if (toastEl) {
            toastEl.classList.remove('show');
            toastEl.classList.add('hide');
        }
    }, 6000);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>