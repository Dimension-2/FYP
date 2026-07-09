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

$page_title = "Hostel Security Voucher";
$student_reg = $_GET['reg_no'] ?? $_SESSION['registration_no'];
$upload_message = "";

// OPTION A: Handle Form Submission for File Uploads
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['receipt_slip'])) {
    $voucher_id = intval($_POST['upload_voucher_id']);
    $target_dir = "uploads/";

    // Auto-create folder if missing
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_ext = strtolower(pathinfo($_FILES["receipt_slip"]["name"], PATHINFO_EXTENSION));
    $new_filename = "slip_" . $voucher_id . "_" . time() . "." . $file_ext;
    $target_file = $target_dir . $new_filename;

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
    if (in_array($file_ext, $allowed_extensions)) {
        if (move_uploaded_file($_FILES["receipt_slip"]["tmp_name"], $target_file)) {
            // Update database records
            $u_stmt = $conn->prepare("UPDATE hostel_vouchers SET receipt_image = ?, uploaded_at = NOW() WHERE id = ?");
            $u_stmt->bind_param("si", $new_filename, $voucher_id);
            $u_stmt->execute();
            $u_stmt->close();

            $upload_message = "<div class='alert alert-success alert-dismissible fade show no-print mb-4 border-0 shadow-sm' role='alert'>
                                <i class='bi bi-check-circle-fill me-2'></i> Receipt slip uploaded successfully! Sent to accounts for administrative verification.
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                               </div>";
        } else {
            $upload_message = "<div class='alert alert-danger no-print mb-4 border-0 shadow-sm'><i class='bi bi-exclamation-triangle-fill me-2'></i> System failed to save attachment file. Please verify directory permissions.</div>";
        }
    } else {
        $upload_message = "<div class='alert alert-danger no-print mb-4 border-0 shadow-sm'><i class='bi bi-exclamation-triangle-fill me-2'></i> Invalid format type! Please use: JPG, JPEG, PNG, or PDF formats.</div>";
    }
}

// 2. Fetch Active Voucher using Prepared Statement
$stmt = $conn->prepare("SELECT * FROM hostel_vouchers WHERE registration_no = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("s", $student_reg);
$stmt->execute();
$result = $stmt->get_result();

$has_applied = false; // Add our tracking variable here

if ($result && $result->num_rows > 0) {
    $has_applied = true; // Mark as applied since a voucher exists
    $data = $result->fetch_assoc();
    $active_id = $data['id'];
    $challan_no = htmlspecialchars($data['challan_no']);
    $student_name = htmlspecialchars($data['student_name']);
    $due_date = date('d-M-Y', strtotime($data['due_date']));
    $security_fee = $data['security_fee'];
    $card_charges = $data['card_charges'];
    $total_payable = $data['total_payable'];
    $is_paid = ($data['status'] == 'Paid');
    $receipt_image = $data['receipt_image'];

    $card_charges = $data['card_charges'];
    $mess_fee = isset($data['mess_fee']) ? $data['mess_fee'] : 0;
    $total_payable = $data['total_payable'];
    $is_paid = ($data['status'] == 'Paid');
    // AUTOMATIC SYSTEM CALCULATION FOR 11th DAY LOCK & FINE RULES
    $current_day = (int) date('d');
    $is_locked_overdue = (!$is_paid && $current_day > 11);

    if ($is_locked_overdue) {
        $total_payable += 1000;
    }
}
// Notice we completely removed the "else" block that was generating fake data!
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Security - University of Wah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <link rel="stylesheet" href="assets/hostel_voucher.css">
    <style>
        /* Ensuring print looks clean */
        @media print {
            .no-print {
                display: none !important;
            }

            .main-wrapper {
                display: block !important;
            }

            .content-area {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>

<body>

    <div class="main-wrapper d-flex">
        <?php include('includes/navbar.php'); ?>

        <div class="content-area flex-grow-1">
            <?php include('includes/header.php'); ?>

            <div class="container-fluid px-4 mt-4">
                <?php if (!empty($upload_message))
                    echo $upload_message; ?>

                <div class="row align-items-center mb-4 no-print">
                    <div class="col-md-6">
                        <h3 class="fw-bold text-dark m-0">Hostel Security & Dues</h3>
                        <p class="text-muted small">Home / Hostel / Security Voucher</p>
                    </div>
                    <!-- ONLY SHOW BUTTONS IF APPLIED -->
                    <?php if ($has_applied): ?>
                        <div class="col-md-6 text-md-end">
                            <button class="btn btn-primary shadow-sm me-2" data-bs-toggle="modal"
                                data-bs-target="#historyModal">
                                <i class="bi bi-clock-history me-1"></i> View History
                            </button>
                            <button class="btn btn-dark shadow-sm" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i> Print Voucher
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- CHECK IF APPLIED -->
                <?php if (!$has_applied): ?>
                    <!-- THIS IS THE NEW LOCKED SCREEN -->
                    <div class="card border-0 shadow-sm text-center py-5 mt-4">
                        <div class="card-body">
                            <i class="bi bi-lock-fill text-danger mb-3" style="font-size: 4rem;"></i>
                            <h4 class="fw-bold text-dark">Voucher Locked</h4>
                            <p class="text-muted mb-4">You have not applied for a hostel yet. Submit your application to
                                generate and unlock your security voucher.</p>
                            <a href="hostel_apply.php" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
                                <i class="bi bi-building-add me-2"></i> Apply for Hostel
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- THIS IS THE REGULAR VOUCHER SCREEN -->
                    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center no-print mb-4">
                        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                        <div>
                            <h6 class="mb-0 fw-bold">Online Payment Available</h6>
                            <small>Pay via EasyPaisa/JazzCash by selecting <strong>University of Wah</strong> and using
                                <strong>Reg No</strong> as Consumer ID.</small>
                        </div>
                    </div>

                    <div class="voucher-container mb-5">
                        <?php
                        $copies = ["Bank Copy", "University Copy", "Student Copy"];
                        foreach ($copies as $copy):
                            ?>
                            <div class="hostel-voucher shadow-sm bg-white p-4 mb-4 border">
                                <div class="text-center border-bottom pb-2 mb-3">
                                    <h6 class="fw-bold mb-0" style="font-size: 14px;">UNIVERSITY OF WAH</h6>
                                    <span
                                        class="status-pill <?php echo $is_paid ? 'paid text-success' : 'unpaid text-danger'; ?> fw-bold no-print">
                                        • <?php echo $is_paid ? 'PAID' : 'UNPAID'; ?>
                                    </span>
                                    <div class="mt-1"><span class="badge bg-dark"
                                            style="font-size: 9px;"><?php echo $copy; ?></span></div>
                                </div>

                                <div class="voucher-details">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="label small text-muted">Challan No:</span>
                                        <span class="value fw-bold"><?php echo $challan_no; ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="label small text-muted">Reg No:</span>
                                        <span class="value fw-bold"><?php echo $student_reg; ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="label small text-muted">Name:</span>
                                        <span class="value text-uppercase fw-bold"><?php echo $student_name; ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="label small text-muted">Due Date:</span>
                                        <span class="value fw-bold"><?php echo $due_date; ?></span>
                                    </div>

                                    <table class="table table-sm border" style="font-size: 11px;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Description</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Hostel Security (Refundable)</td>
                                                <td class="text-end"><?php echo number_format($security_fee); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-secondary">Hostel Card Charges</td>
                                                <td class="text-end fw-bold">PKR <?php echo number_format($card_charges); ?>
                                                </td>
                                            </tr>
                                            <?php if ($mess_fee > 0): ?>
                                                <tr>
                                                    <td class="text-secondary">Monthly Mess Subscription</td>
                                                    <td class="text-end fw-bold">PKR <?php echo number_format($mess_fee); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            <tr class="table-dark"> 
                                                <?php if ($is_locked_overdue): ?>
                                                <tr class="text-danger fw-bold">
                                                    <td>Late Submission Fine (Carried Forward)</td>
                                                    <td class="text-end">1,000</td>
                                                </tr>
                                            <?php endif; ?>
                                            <tr class="fw-bold border-top bg-light">
                                                <td>Total Payable</td>
                                                <td class="text-end text-primary">PKR
                                                    <?php echo number_format($total_payable); ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="bank-info mt-2 p-2 rounded bg-light small" style="font-size: 10px;">
                                    <strong>Payment Instructions:</strong><br>
                                    1. Payable at any HBL Branch (A/C: 1234-567890-01-2)<br>
                                    2. Non-refundable if hostel is vacated without notice.
                                </div>

                                <div class="mt-4 d-flex justify-content-between">
                                    <div class="border-top text-center pt-1" style="width: 40%; font-size: 9px;">Student
                                        Signature</div>
                                    <div class="border-top text-center pt-1" style="width: 40%; font-size: 9px;">Bank Cashier
                                        Stamp</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?> <!-- END OF CHECK IF APPLIED -->
            </div>
        </div>
    </div>

    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-clock-history me-2 text-primary"></i>Hostel Payment Ledger
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-3">Voucher #</th>
                                    <th>Issue Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th class="pe-3 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <?php
                                // 1. Use Prepared Statement for security
                                $h_stmt = $conn->prepare("SELECT * FROM hostel_vouchers WHERE registration_no = ? ORDER BY id DESC");
                                $h_stmt->bind_param("s", $student_reg);
                                $h_stmt->execute();
                                $h_result = $h_stmt->get_result();

                                if ($h_result && $h_result->num_rows > 0) {
                                    while ($row = $h_result->fetch_assoc()) {
                                        $status_badge = ($row['status'] == 'Paid') ? 'bg-success' : 'bg-danger';
                                        // Handle potential null dates to avoid errors
                                        $date_formatted = isset($row['created_at']) ? date('d-M-Y', strtotime($row['created_at'])) : "N/A";

                                        echo '<tr class="history-row">';
                                        echo '<td class="ps-3 fw-bold">' . htmlspecialchars($row['challan_no']) . '</td>';
                                        echo '<td>' . $date_formatted . '</td>';
                                        echo '<td>' . number_format($row['total_payable']) . '</td>';
                                        echo '<td><span class="badge ' . $status_badge . '">' . htmlspecialchars($row['status']) . '</span></td>';
                                        echo '<td class="pe-3 text-end">';

                                        if ($row['status'] == 'Unpaid') {
                                            if ((int) date('d') > 11) {
                                                echo '<span class="badge bg-dark text-white rounded-pill px-2 py-1 small"><i class="bi bi-lock-fill"></i> Locked (+1K Dues)</span>';
                                            } else {
                                                if (!empty($row['receipt_image'])) {
                                                    echo '<span class="badge bg-warning text-dark rounded-pill px-2 py-1 small"><i class="bi bi-hourglass-split"></i> Awaiting Verification</span>';
                                                } else {
                                                    echo '<button type="button" class="btn btn-sm btn-primary py-0 px-3 fw-bold shadow-sm" onclick="openUploadForm(' . $row['id'] . ')"><i class="bi bi-cloud-arrow-up"></i> Upload Slip</button>';
                                                }
                                            }
                                        } else {
                                            echo '<span class="text-success small fw-bold"><i class="bi bi-check-circle-fill"></i> Verified</span>';
                                        }

                                        echo '</td></tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="5" class="text-center py-4 text-muted">No records found in database.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadSlipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-dark text-white py-3">
                        <h5 class="modal-title fw-bold"><i
                                class="bi bi-file-earmark-arrow-up text-primary me-2"></i>Upload Receipt Slip</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="upload_voucher_id" id="upload_voucher_id">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Select Paid Bank Voucher Copy</label>
                            <input type="file" name="receipt_slip" class="form-control form-control-lg border-2"
                                required accept="image/*,application/pdf">
                            <div class="form-text text-muted small mt-2"><i class="bi bi-info-circle"></i> Allowed
                                formats: JPG, JPEG, PNG, or PDF file documents only. Maximum file size: 2MB.</div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0 py-3">
                        <button type="button" class="btn btn-secondary px-3 fw-bold btn-sm"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold btn-sm shadow-sm"><i
                                class="bi bi-check2"></i> Submit to Finance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openUploadForm(voucherId) {
            // Assign targeting identifier
            document.getElementById('upload_voucher_id').value = voucherId;

            // Smooth transition from ledger list to upload overlay forms
            var historyElement = document.getElementById('historyModal');
            var historyInstance = bootstrap.Modal.getInstance(historyElement);
            if (historyInstance) { historyInstance.hide(); }

            var uploadModal = new bootstrap.Modal(document.getElementById('uploadSlipModal'));
            uploadModal.show();
        }
    </script>
</body>

</html>
<?php $conn->close(); ?>