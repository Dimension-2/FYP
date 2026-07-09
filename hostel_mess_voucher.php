<?php 
session_start();
    // Database connection
    $conn = new mysqli("localhost", "root", "", "fyp");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
if (!isset($_SESSION['registration_no'])) { header("Location: login.php"); exit(); }
    $page_title = "Hostel Mess Voucher"; 
    $student_reg = $_SESSION['registration_no'];

    // 1. Verify if student has applied for a hostel (Option 2)
    $hostel_check = $conn->prepare("SELECT id FROM hostel_applications WHERE registration_no = ? LIMIT 1");
    $hostel_check->bind_param("s", $student_reg);
    $hostel_check->execute();
    $hostel_res = $hostel_check->get_result();
    $has_hostel = ($hostel_res && $hostel_res->num_rows > 0);

    // 2. Fetch Active Mess Voucher
    $stmt = $conn->prepare("SELECT * FROM hostel_mess_vouchers WHERE registration_no = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("s", $student_reg);
    $stmt->execute();
    $result = $stmt->get_result();

    $has_mess_voucher = false;

    if ($result && $result->num_rows > 0) {
        $has_mess_voucher = true;
        $data = $result->fetch_assoc();
        $challan_no = htmlspecialchars($data['challan_no']);
        $student_name = htmlspecialchars($data['student_name']);
        $current_month = htmlspecialchars($data['billing_month']);
        $mess_charges = $data['mess_charges'];
        $special_charges = $data['special_charges'];
        $total_payable = $data['total_payable'];
        $status = $data['status'];
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mess Voucher - University of Wah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <link rel="stylesheet" href="assets/hostel_mess.css">
</head>
<body>

<div class="main-wrapper d-flex">
    <div class="no-print">
        <?php include('includes/navbar.php'); ?>
    </div>

    <div class="content-area flex-grow-1">
        <div class="no-print">
            <?php include('includes/header.php'); ?>
        </div>

        <div class="container-fluid px-4 mt-4">
<div class="row align-items-center mb-4 no-print">
                <div class="col-md-6">
                    <h3 class="fw-bold text-dark m-0">Hostel Mess Voucher</h3>
                    <p class="text-muted small">Home / Hostel / Mess Dues</p>
                </div>
                <?php if ($has_mess_voucher): ?>
                <div class="col-md-6 text-md-end">
                    <button class="btn btn-outline-info shadow-sm me-2" data-bs-toggle="modal" data-bs-target="#messHistoryModal">
                        <i class="bi bi-calendar-check me-1"></i> Mess History
                    </button>
                    <button class="btn btn-dark shadow-sm" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print Voucher
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!$has_hostel): ?>
                <div class="card border-0 shadow-sm text-center py-5 mt-4">
                    <div class="card-body">
                        <i class="bi bi-x-circle-fill text-danger mb-3" style="font-size: 4rem;"></i>
                        <h4 class="fw-bold text-dark">Access Denied</h4>
                        <p class="text-muted mb-0">You must have an approved hostel room to access the mess facility.</p>
                    </div>
                </div>
            <?php elseif (!$has_mess_voucher): ?>
                <div class="card border-0 shadow-sm text-center py-5 mt-4">
                    <div class="card-body">
                        <i class="bi bi-cup-hot-fill text-warning mb-3" style="font-size: 4rem;"></i>
                        <h4 class="fw-bold text-dark">Mess Not Joined</h4>
                        <p class="text-muted mb-4">You have not applied for the hostel mess yet. Join the mess to generate your monthly vouchers.</p>
                        <a href="hostel_mess_apply.php" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
                            <i class="bi bi-plus-circle me-2"></i> Join Hostel Mess
                        </a>
                    </div>
                </div>
                <!-- PASTE YOUR EXISTING VOUCHER HTML HERE (The Alert and the foreach loop for the 3 copies) -->
            <?php endif; ?>
            <div class="alert mess-alert border-0 shadow-sm d-flex align-items-center no-print mb-4" style="background-color: #e3f2fd; border-left: 5px solid #0d6efd !important;">
                <i class="bi bi-info-circle-fill fs-4 me-3 text-primary"></i>
                <div>
                    <h6 class="mb-0 fw-bold">Mess Bill: <?php echo $current_month; ?></h6>
                    <small>Please clear your dues by the 10th of every month to avoid a fine of PKR 100/day.</small>
                </div>
            </div>

            <div class="voucher-container mb-5">
                <?php 
                $copies = ["Bank Copy", "Mess Office Copy", "Student Copy"];
                foreach($copies as $copy): 
                ?>
                <div class="hostel-voucher shadow-sm bg-white p-4 mb-4 border" style="border-radius: 10px;">
                    <div class="text-center border-bottom pb-2 mb-3">
                        <h6 class="fw-bold mb-0" style="font-size: 14px;">UNIVERSITY OF WAH</h6>
                        <span class="status-pill <?php echo ($status == 'Paid') ? 'text-success' : 'text-danger'; ?> fw-bold no-print">
                           • <?php echo strtoupper($status); ?>
                        </span>
                        <div class="mt-1"><span class="badge bg-primary" style="font-size: 9px;"><?php echo $copy; ?></span></div>
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
                            <span class="label small text-muted">Billing Month:</span>
                            <span class="value fw-bold"><?php echo $current_month; ?></span>
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
                                    <td>Monthly Mess Charges</td>
                                    <td class="text-end"><?php echo number_format($mess_charges); ?></td>
                                </tr>
                                <tr>
                                    <td>Special Dinner Contribution</td>
                                    <td class="text-end"><?php echo number_format($special_charges); ?></td>
                                </tr>
                                <tr class="fw-bold border-top bg-light">
                                    <td>Total Payable</td>
                                    <td class="text-end text-primary">PKR <?php echo number_format($total_payable); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="bank-info mt-2 p-2 rounded bg-light small" style="font-size: 10px;">
                        <strong>Instructions:</strong><br>
                        1. Pay via HBL Mobile App or any HBL Branch.<br>
                        2. Retain Student Copy for Mess Card activation.<br>
                        3. Fine applies after the due date.
                    </div>
                    
                    <div class="mt-4 d-flex justify-content-between">
                        <div class="border-top text-center pt-1" style="width: 45%; font-size: 9px;">Student Sign</div>
                        <div class="border-top text-center pt-1" style="width: 45%; font-size: 9px;">Authorized Sign</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade no-print" id="messHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-clock-history me-2 text-primary"></i>Mess Payment History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th class="ps-3">Month</th>
                                <th>Voucher #</th>
                                <th>Amount</th>
                                <th>Paid Date</th>
                                <th>Status</th>
                                <th class="pe-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php 
                            // FETCH ALL HISTORY FROM DB
                            $h_sql = "SELECT * FROM hostel_mess_vouchers WHERE registration_no = '{$_SESSION['registration_no']}' ORDER BY id DESC";
                            $h_result = $conn->query($h_sql);

                            if ($h_result && $h_result->num_rows > 0) {
                                while($row = $h_result->fetch_assoc()) {
                                    $badge = ($row['status'] == 'Paid') ? 'bg-success' : 'bg-danger';
                                    $p_date = ($row['paid_date']) ? date('d-M-Y', strtotime($row['paid_date'])) : '---';
                                    
                                    echo '<tr>';
                                    echo '<td class="ps-3 fw-bold">'.$row['billing_month'].'</td>';
                                    echo '<td>'.$row['challan_no'].'</td>';
                                    echo '<td>'.number_format($row['total_payable']).'</td>';
                                    echo '<td>'.$p_date.'</td>';
                                    echo '<td><span class="badge '.$badge.'">'.$row['status'].'</span></td>';
                                    echo '<td class="pe-3 text-end">';
                                    if($row['status'] == 'Paid') {
                                        echo '<i class="bi bi-file-earmark-check-fill text-success fs-5"></i>';
                                    } else {
                                        echo '<button class="btn btn-sm btn-outline-primary py-0">Pay</button>';
                                    }
                                    echo '</td></tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6" class="text-center py-4 text-muted">No mess history records found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>