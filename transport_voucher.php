<?php
session_start();
// Database connection
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$page_title = "Transport Voucher";
// Dynamic Registration No from URL (e.g., voucher.php?registration_no=UW-22-CS-BS-053)
$student_reg = $_GET['registration_no'] ?? $_SESSION['registration_no'] ?? 'N/A';

// Fetching data dynamic based on registration_no
$sql = "SELECT * FROM transport_vouchers WHERE registration_no = '$student_reg' ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $student_name = $data['student_name'] ?? 'N/A';
    $challan_no = $data['challan_no'] ?? 'N/A';
    $route_info = $data['route_info'] ?? 'N/A';
    $semester = $data['semester_name'] ?? 'N/A';
    $transport_fee = $data['transport_fee'] ?? 0;
    $maintenance_fund = $data['maintenance_fund'] ?? 0;
    $total_payable = $data['total_payable'] ?? 0;
    $status = $data['status'] ?? 'Unpaid';
}  else {
    // Generate a unique ID based on the current microsecond
    $challan_no = "TRN-" . substr(md5(microtime()), 0, 8); 
    
    $student_name = "N/A";
    $route_info   = "N/A";
    $semester     = "N/A";
    $transport_fee = 0;
    $maintenance_fund = 0;
    $total_payable = 0;
    $status = "Unpaid";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transport Voucher - University of Wah</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <link rel="stylesheet" href="assets/transport_voucher.css">

    <style>
        .voucher-container {
            display: flex;
            gap: 20px;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 20px;
        }

        .transport-voucher {
            background: #fff;
            min-width: 320px;
            padding: 20px;
            border: 1.5px dashed #198754;
            border-radius: 4px;
            position: relative;
        }

        .transport-voucher .label {
            font-size: 11px;
            color: #666;
            font-weight: 600;
        }

        .transport-voucher .value {
            font-size: 13px;
            font-weight: 700;
            color: #222;
        }

        .transport-alert {
            background-color: #f0fff4;
            border: 1px solid #c6f6d5;
            color: #22543d;
        }

        .bank-info {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 10px;
            line-height: 1.4;
        }

        .status-pill {
            font-size: 10px;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: bold;
        }

        .unpaid {
            background: #fee2e2;
            color: #dc2626;
        }

        .paid {
            background: #dcfce7;
            color: #166534;
        }

        @media print {

            .no-print,
            nav,
            .sidebar,
            header,
            .btn,
            .alert,
            .modal {
                display: none !important;
            }

            body,
            .main-wrapper,
            .content-area,
            .container-fluid {
                display: block !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .voucher-container {
                display: flex !important;
                flex-direction: row !important;
                gap: 10px !important;
                width: 100% !important;
                overflow: visible !important;
            }

            .transport-voucher {
                width: 32% !important;
                min-width: 0 !important;
                border: 1px solid #000 !important;
                box-shadow: none !important;
                padding: 10px !important;
            }

            @page {
                size: landscape;
                margin: 0.5cm;
            }
        }
    </style>
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
                        <h3 class="fw-bold text-dark m-0">Transport Voucher</h3>
                        <p class="text-muted small">Home / Transport / Fee Voucher</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <button class="btn btn-outline-primary shadow-sm me-2" data-bs-toggle="modal"
                            data-bs-target="#transportHistory">
                            <i class="bi bi-clock-history me-1"></i> Payment History
                        </button>
                        <button class="btn btn-dark shadow-sm" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Print Voucher
                        </button>
                    </div>
                </div>

                <div class="alert transport-alert border-0 shadow-sm d-flex align-items-center no-print mb-4">
                    <i class="bi bi-bus-front fs-4 me-3"></i>
                    <div>
                        <h6 class="mb-0 fw-bold">Transport Fee: <?php echo $semester; ?></h6>
                        <small>Route: <?php echo $route_info; ?>. Please pay before the due date to avoid service
                            suspension.</small>
                    </div>
                </div>

                <div class="voucher-container mb-5">
                    <?php
                    $copies = ["Bank Copy", "Transport Office Copy", "Student Copy"];
                    foreach ($copies as $copy):
                        ?>
                        <div class="transport-voucher shadow-sm">
                            <div class="text-center border-bottom pb-2 mb-3">
                                <h6 class="fw-bold mb-0" style="font-size: 14px;">UNIVERSITY OF WAH</h6>
                                <span class="status-pill <?php echo ($status == 'Paid') ? 'paid' : 'unpaid'; ?> no-print">
                                    <?php echo strtoupper($status); ?>
                                </span>
                                <div class="mt-1"><span class="badge bg-success"
                                        style="font-size: 9px;"><?php echo $copy; ?></span></div>
                            </div>

                            <div class="voucher-details">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="label">Challan No:</span>
                                    <span class="value"><?php echo $challan_no; ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="label">Reg No:</span>
                                    <span class="value"><?php echo $student_reg; ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="label">Route:</span>
                                    <span class="value text-uppercase"><?php echo $route_info; ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="label">Semester:</span>
                                    <span class="value"><?php echo $semester; ?></span>
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
                                            <td>Semester Transport Fee</td>
                                            <td class="text-end"><?php echo number_format($transport_fee); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Maintenance Fund</td>
                                            <td class="text-end"><?php echo number_format($maintenance_fund); ?></td>
                                        </tr>
                                        <tr class="fw-bold border-top">
                                            <td>Total Payable</td>
                                            <td class="text-end text-success">PKR
                                                <?php echo number_format($total_payable); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="bank-info mt-2 p-2 rounded">
                                <strong>Terms:</strong><br>
                                1. Present this voucher at any HBL branch.<br>
                                2. Transport card will be issued after verification.<br>
                                3. Fee is non-refundable after 15 days of semester start.
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <div class="border-top text-center pt-1" style="width: 45%; font-size: 9px;">Student Sign
                                </div>
                                <div class="border-top text-center pt-1" style="width: 45%; font-size: 9px;">Transport
                                    Manager</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade no-print" id="transportHistory" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Transport Payment History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Semester</th>
                                    <th>Route</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // FIX: Changed 'reg_no' to 'registration_no' to match database schema
                                $h_sql = "SELECT * FROM transport_vouchers WHERE registration_no = '$student_reg' ORDER BY id DESC";
                                $h_result = $conn->query($h_sql);

                                if ($h_result && $h_result->num_rows > 0) {
                                    while ($row = $h_result->fetch_assoc()) {
                                        $badge = ($row['status'] == 'Paid') ? 'bg-success' : 'bg-danger';
                                        echo '<tr>';
                                        echo '<td class="ps-3 fw-bold">' . $row['semester_name'] . '</td>';
                                        echo '<td>' . $row['route_info'] . '</td>';
                                        echo '<td>' . number_format($row['total_payable']) . '</td>';
                                        echo '<td><span class="badge ' . $badge . '">' . $row['status'] . '</span></td>';
                                        echo '<td class="text-end pe-3">';
                                        if ($row['status'] == 'Paid') {
                                            echo '<span class="text-success small"><i class="bi bi-check-all"></i> Verified</span>';
                                        } else {
                                            echo '<button class="btn btn-sm btn-primary py-0">Pay Now</button>';
                                        }
                                        echo '</td></tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="5" class="text-center py-4 text-muted">No transport records found.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php $conn->close(); ?>