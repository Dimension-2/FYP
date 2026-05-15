<?php 
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// 1. Access Control
if (!isset($_SESSION['registration_no'])) { 
    header("Location: login.php"); 
    exit(); 
}

$page_title = "Hostel Security Voucher"; 
$student_reg = $_GET['reg_no'] ?? $_SESSION['registration_no'];

// 2. Fetch Active Voucher using Prepared Statement
$stmt = $conn->prepare("SELECT * FROM hostel_vouchers WHERE registration_no = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("s", $student_reg);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $challan_no    = htmlspecialchars($data['challan_no']);
    $student_name  = htmlspecialchars($data['student_name']);
    $due_date      = date('d-M-Y', strtotime($data['due_date']));
    $security_fee  = $data['security_fee'];
    $card_charges  = $data['card_charges'];
    $total_payable = $data['total_payable'];
    $is_paid       = ($data['status'] == 'Paid');
} else {
    // 3. Dynamic Fallback for new students
    $reg_suffix    = substr($student_reg, -3);
    // Use full Reg No and current timestamp for a unique, scalable ID
    $challan_no = "HSEC-" . preg_replace("/[^A-Za-z0-9]/", "", $student_reg) . "-" . time();
    
    // Fetch real name from student table if voucher doesn't exist yet
    $s_stmt = $conn->prepare("SELECT student_name, hostel_security_rate, card_rate, hostel_due_date FROM students WHERE registration_no = ?");
    $s_stmt->bind_param("s", $student_reg);
    $s_stmt->execute();
    $s_res = $s_stmt->get_result();
    $s_row = $s_res->fetch_assoc();

    $student_name  = $s_row['student_name'] ?? "N/A";
    $due_date = isset($s_row['hostel_due_date']) ? date('d-M-Y', strtotime($s_row['hostel_due_date'])) : "N/A";; // Last day of current month
    $security_fee  = $s_row['hostel_security_rate'] ?? 0; 
    $card_charges  = $s_row['card_rate'] ?? 0;
    $total_payable = $security_fee + $card_charges;
    
    // Create a display variable for the table
    $display_security = ($security_fee > 0) ? number_format($security_fee) : "N/A";
    $display_card     = ($card_charges > 0) ? number_format($card_charges) : "N/A";
    $total_payable = $security_fee + $card_charges;
    $is_paid       = false; // New voucher defaults to unpaid
}
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
            .no-print { display: none !important; }
            .main-wrapper { display: block !important; }
            .content-area { margin: 0 !important; padding: 0 !important; }
        }
    </style>
</head>
<body>

<div class="main-wrapper d-flex">
    <?php include('includes/navbar.php'); ?>

    <div class="content-area flex-grow-1">
        <?php include('includes/header.php'); ?>

        <div class="container-fluid px-4 mt-4">
            <div class="row align-items-center mb-4 no-print">
                <div class="col-md-6">
                    <h3 class="fw-bold text-dark m-0">Hostel Security & Dues</h3>
                    <p class="text-muted small">Home / Hostel / Security Voucher</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <button class="btn btn-primary shadow-sm me-2" data-bs-toggle="modal" data-bs-target="#historyModal">
                        <i class="bi bi-clock-history me-1"></i> View History
                    </button>
                    <button class="btn btn-dark shadow-sm" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print Voucher
                    </button>
                </div>
            </div>

            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center no-print mb-4">
                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                <div>
                    <h6 class="mb-0 fw-bold">Online Payment Available</h6>
                    <small>Pay via EasyPaisa/JazzCash by selecting <strong>University of Wah</strong> and using <strong>Reg No</strong> as Consumer ID.</small>
                </div>
            </div>

            <div class="voucher-container mb-5">
                <?php 
                $copies = ["Bank Copy", "University Copy", "Student Copy"];
                foreach($copies as $copy): 
                ?>
                <div class="hostel-voucher shadow-sm bg-white p-4 mb-4 border">
                    <div class="text-center border-bottom pb-2 mb-3">
                        <h6 class="fw-bold mb-0" style="font-size: 14px;">UNIVERSITY OF WAH</h6>
                        <span class="status-pill <?php echo $is_paid ? 'paid text-success' : 'unpaid text-danger'; ?> fw-bold no-print">
                            • <?php echo $is_paid ? 'PAID' : 'UNPAID'; ?>
                        </span>
                        <div class="mt-1"><span class="badge bg-dark" style="font-size: 9px;"><?php echo $copy; ?></span></div>
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
                                    <td>Hostel Card Charges</td>
                                    <td class="text-end"><?php echo number_format($card_charges); ?></td>
                                </tr>
                                <tr class="fw-bold border-top bg-light">
                                    <td>Total Payable</td>
                                    <td class="text-end text-primary">PKR <?php echo number_format($total_payable); ?></td>
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
                        <div class="border-top text-center pt-1" style="width: 40%; font-size: 9px;">Student Signature</div>
                        <div class="border-top text-center pt-1" style="width: 40%; font-size: 9px;">Bank Cashier Stamp</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
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
    while($row = $h_result->fetch_assoc()) {
        $status_badge = ($row['status'] == 'Paid') ? 'bg-success' : 'bg-danger';
        // Handle potential null dates to avoid errors
        $date_formatted = isset($row['created_at']) ? date('d-M-Y', strtotime($row['created_at'])) : "N/A";
        
        echo '<tr class="history-row">';
        echo '<td class="ps-3 fw-bold">'.htmlspecialchars($row['challan_no']).'</td>';
        echo '<td>'.$date_formatted.'</td>';
        echo '<td>'.number_format($row['total_payable']).'</td>';
        echo '<td><span class="badge '.$status_badge.'">'.htmlspecialchars($row['status']).'</span></td>';
        echo '<td class="pe-3 text-end">';
        
        if($row['status'] == 'Unpaid') {
            echo '<button class="btn btn-sm btn-primary py-0 px-3">Upload Slip</button>';
        } else {
            echo '<span class="text-success small"><i class="bi bi-check-circle-fill"></i> Verified</span>';
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>