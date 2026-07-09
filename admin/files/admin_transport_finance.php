<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Auto-Schema Fix: Ensure column exists here too in case admin opens this first
$check_date = $conn->query("SHOW COLUMNS FROM transport_vouchers LIKE 'due_date'");
if ($check_date && $check_date->num_rows == 0) {
    $conn->query("ALTER TABLE transport_vouchers ADD due_date DATE NULL AFTER total_payable");
}

// ==========================================
// 1. UPDATE VOUCHER STATUS ENGINE
// ==========================================
if (isset($_POST['update_status'])) {
    $voucher_id = intval($_POST['voucher_id']);
    $new_status = $_POST['new_status'];

    $stmt = $conn->prepare("UPDATE transport_vouchers SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $voucher_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_transport_finance.php?status_updated=1");
    exit();
}

// ==========================================
// 2. BILLING ENGINE: Generate Next Month Dues
// ==========================================
if (isset($_POST['generate_billing'])) {
    $next_month = date('F Y', strtotime('+1 month'));
    $due_date = date('Y-m-11', strtotime('+1 month'));

    // Fetch all active transport students
    $active_students = $conn->query("SELECT * FROM transport_applications WHERE opt_status = 'Active'");

    while ($student = $active_students->fetch_assoc()) {
        $reg_no = $student['registration_no'];

        // Ensure we haven't already generated for this month
        $check = $conn->query("SELECT id FROM transport_vouchers WHERE registration_no='$reg_no' AND semester_name='$next_month'");
        if ($check->num_rows > 0)
            continue;

        // Check if previous month is unpaid to apply fine and carry over
        $prev_bill = $conn->query("SELECT * FROM transport_vouchers WHERE registration_no='$reg_no' ORDER BY id DESC LIMIT 1");

        $base_fee = 0;
        $prev_dues = 0;
        $fine = 0;
        $route = $student['route_name'];

        if ($prev_bill->num_rows > 0) {
            $last_voucher = $prev_bill->fetch_assoc();
            $base_fee = $last_voucher['transport_fee']; // Keep same route fee

            if ($last_voucher['status'] == 'Unpaid') {
                $prev_dues = $last_voucher['total_payable']; // Carry over whole amount
                $fine = 1000; // Apply Penalty

                // Mark old voucher as 'Overdue/Carried Forward' so they pay the new combined one
                $conn->query("UPDATE transport_vouchers SET status='Carried Forward' WHERE id=" . $last_voucher['id']);
            }
        }

        $maintenance = 500;
        $total = $base_fee + $maintenance + $prev_dues + $fine;
        $challan = "TRN-" . substr(md5(uniqid()), 0, 8);

        $stmt = $conn->prepare("INSERT INTO transport_vouchers (challan_no, registration_no, student_name, route_info, semester_name, transport_fee, maintenance_fund, total_payable, due_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Unpaid')");
        $stmt->bind_param("ssssssdds", $challan, $reg_no, $student['student_name'], $route, $next_month, $base_fee, $maintenance, $total, $due_date);
        $stmt->execute();
    }

    header("Location: admin_transport_finance.php?success=1");
    exit();
}

// ==========================================
// 3. DASHBOARD STATISTICS
// ==========================================
$total_enrolled = $conn->query("SELECT COUNT(*) as c FROM transport_applications WHERE opt_status='Active'")->fetch_assoc()['c'];
$paid_accounts = $conn->query("SELECT COUNT(DISTINCT registration_no) as c FROM transport_vouchers WHERE status='Paid'")->fetch_assoc()['c'];
$pending_bills = $conn->query("SELECT COUNT(*) as c FROM transport_vouchers WHERE status='Unpaid'")->fetch_assoc()['c'];

// ==========================================
// 4. FETCH & SMART GROUP STUDENTS
// ==========================================
$folders_query = "SELECT 
                    a.registration_no, a.student_name, a.route_name,
                    COUNT(v.id) as total_vouchers,
                    SUM(CASE WHEN v.status='Unpaid' THEN 1 ELSE 0 END) as unpaid_count
                  FROM transport_applications a
                  LEFT JOIN transport_vouchers v ON a.registration_no = v.registration_no
                  WHERE a.opt_status = 'Active'
                  GROUP BY a.registration_no, a.student_name, a.route_name";
$folders = $conn->query($folders_query);

$grouped_students = [];

if ($folders && $folders->num_rows > 0) {
    while ($row = $folders->fetch_assoc()) {
        $reg = $row['registration_no'];
        $parts = explode('-', $reg); // UW - 22 - CS - BS - 053

        // Extract Batch and Dept Safely
        if (count($parts) >= 3) {
            $batch_raw = $parts[1]; // e.g., '22' or '22M'
            $batch_num = preg_replace('/[^0-9]/', '', $batch_raw); // Extracts just '22'
            $dept = strtoupper($parts[2]); // e.g., 'CS'
            $group_name = "Batch $batch_num - $dept";
        } else {
            $group_name = "Uncategorized Records";
        }

        if (!isset($grouped_students[$group_name])) {
            $grouped_students[$group_name] = [
                'students' => [],
                'unpaid' => 0,
                'total' => 0
            ];
        }

        $grouped_students[$group_name]['students'][] = $row;
        $grouped_students[$group_name]['unpaid'] += $row['unpaid_count'];
        $grouped_students[$group_name]['total']++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin - Transport Finance</title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f4f7f6;
        }

        /* Stats Cards */
        .stat-card {
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .stat-card.blue {
            border-left: 4px solid #0d6efd;
        }

        .stat-card.green {
            border-left: 4px solid #198754;
        }

        .stat-card.red {
            border-left: 4px solid #dc3545;
        }

        .icon-box {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.2rem;
        }

        /* Literal Folder Design */
        .mac-folder {
            background: transparent;
            border: none;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            position: relative;
        }

        .mac-folder:hover {
            background: rgba(0, 0, 0, 0.05);
            transform: scale(1.02);
        }

        .folder-icon-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 10px;
        }

        .folder-icon {
            font-size: 5.5rem;
            color: #ffd166;
            line-height: 1;
            filter: drop-shadow(0px 4px 6px rgba(0, 0, 0, 0.1));
        }

        .folder-icon.parent-icon {
            color: #4361ee;
        }

        /* Blue for parent folders */

        /* Notification Badge on Folder */
        .folder-badge {
            position: absolute;
            top: 15px;
            right: -5px;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 11px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .student-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 2px;
        }

        .student-reg {
            font-size: 0.8rem;
            color: #666;
            font-family: monospace;
        }

        /* Search Bar */
        .search-container {
            background: white;
            border-radius: 50px;
            padding: 5px 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-container input {
            border: none;
            box-shadow: none;
            outline: none;
            width: 100%;
            padding: 10px;
            font-size: 1rem;
        }

        .search-container i {
            color: #888;
            font-size: 1.2rem;
        }
    </style>
</head>

<body>

    <div class="main-wrapper d-flex">
        <?php include('sidebar.php'); ?>

        <div class="content-area flex-grow-1">
            <?php include('header.php'); ?>

            <div class="container-fluid px-4 mt-4 mb-5">

                <?php if (isset($_GET['status_updated'])): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> Voucher status has been updated successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-primary alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i> Monthly billing successfully generated and penalties
                        applied.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold m-0"><i class="bi bi-shield-check text-primary me-2"></i>Transport Finance
                            Desk</h4>
                        <p class="text-muted small m-0">Track, search, and approve monthly student transport fees.</p>
                    </div>
                    <form method="POST">
                        <button type="submit" name="generate_billing" class="btn btn-dark fw-bold shadow-sm"
                            onclick="return confirm('Generate next month vouchers? This will apply 1000 PKR fines to all unpaid accounts.')">
                            <i class="bi bi-calendar-plus me-2"></i> Issue Next Month Billing
                        </button>
                    </form>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="stat-card blue shadow-sm">
                            <div>
                                <div class="text-muted small fw-bold mb-1">TOTAL ENROLLED BILLS</div>
                                <h3 class="fw-bold m-0"><?php echo $total_enrolled; ?></h3>
                            </div>
                            <div class="icon-box" style="background: #e9f2ff; color: #0d6efd;"><i
                                    class="bi bi-file-earmark-text"></i></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card green shadow-sm">
                            <div>
                                <div class="text-muted small fw-bold mb-1">VERIFIED PAID ACCOUNTS</div>
                                <h3 class="fw-bold m-0"><?php echo $paid_accounts; ?></h3>
                            </div>
                            <div class="icon-box" style="background: #e6f8f0; color: #198754;"><i
                                    class="bi bi-cash-stack"></i></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card red shadow-sm">
                            <div>
                                <div class="text-muted small fw-bold mb-1">PENDING / OVERDUE BILLS</div>
                                <h3 class="fw-bold m-0"><?php echo $pending_bills; ?></h3>
                            </div>
                            <div class="icon-box" style="background: #ffe9eb; color: #dc3545;"><i
                                    class="bi bi-exclamation-circle"></i></div>
                        </div>
                    </div>
                </div>

                <div class="mb-4 mt-4 text-center">
                    <h4 class="fw-bold text-dark" id="currentGroupLabel">Transport Passenger Groups</h4>
                    <button id="backButton" class="btn btn-dark btn-sm rounded-pill px-4 mt-2 shadow-sm"
                        style="display: none;" onclick="goBack()">
                        <i class="bi bi-arrow-left me-1"></i> Back to All Batches
                    </button>
                    <div class="search-container mt-4">
                        <i class="bi bi-search"></i>
                        <input type="text" id="folderSearch"
                            placeholder="Search across all transport students globally..." onkeyup="filterFolders()">
                    </div>
                </div>

                <div class="row g-3" id="parentFolders">
                    <?php foreach ($grouped_students as $group_name => $group_data):
                        $badge_html = '';
                        if ($group_data['unpaid'] > 0) {
                            $badge_html = '<div class="folder-badge bg-danger">' . $group_data['unpaid'] . '</div>';
                        }
                        ?>
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 parent-folder-item">
                            <div class="mac-folder"
                                onclick="openGroup('<?php echo htmlspecialchars($group_name, ENT_QUOTES); ?>')">
                                <div class="folder-icon-wrapper">
                                    <i class="bi bi-folder-fill folder-icon parent-icon"></i>
                                    <?php echo $badge_html; ?>
                                </div>
                                <div class="student-name text-truncate"><?php echo $group_name; ?></div>
                                <div class="student-reg text-muted"><?php echo $group_data['total']; ?> Enrolled</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="row g-3" id="studentFolders" style="display: none;">
                    <?php foreach ($grouped_students as $group_name => $group_data): ?>
                        <?php foreach ($group_data['students'] as $row):
                            $badge_html = '';
                            if ($row['unpaid_count'] > 0) {
                                $badge_html = '<div class="folder-badge bg-danger">' . $row['unpaid_count'] . '</div>';
                            }
                            ?>
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 student-folder-item"
                                data-group="<?php echo htmlspecialchars($group_name, ENT_QUOTES); ?>"
                                data-search="<?php echo strtolower($row['student_name'] . ' ' . $row['registration_no']); ?>">
                                <div class="mac-folder" data-bs-toggle="modal"
                                    data-bs-target="#modal-<?php echo preg_replace('/[^A-Za-z0-9]/', '', $row['registration_no']); ?>">
                                    <div class="folder-icon-wrapper">
                                        <i class="bi bi-folder-fill folder-icon"></i>
                                        <?php echo $badge_html; ?>
                                    </div>
                                    <div class="student-name text-truncate"
                                        title="<?php echo htmlspecialchars($row['student_name']); ?>">
                                        <?php echo htmlspecialchars($row['student_name']); ?>
                                    </div>
                                    <div class="student-reg"><?php echo $row['registration_no']; ?></div>
                                </div>
                            </div>

                            <div class="modal fade"
                                id="modal-<?php echo preg_replace('/[^A-Za-z0-9]/', '', $row['registration_no']); ?>"
                                tabindex="-1">
                                <div class="modal-dialog modal-xl modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                        <div class="modal-header bg-dark text-white border-0">
                                            <h5 class="modal-title fw-bold">
                                                <i class="bi bi-bus-front me-2 text-warning"></i>
                                                <?php echo htmlspecialchars($row['student_name']); ?>
                                                (<?php echo $row['registration_no']; ?>)
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white"
                                                data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0 align-middle">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th class="ps-4">Billing Month</th>
                                                            <th>Route Info</th>
                                                            <th>Total Amount</th>
                                                            <th>Due Date</th>
                                                            <th>Status</th>
                                                            <th class="text-end pe-4">Update Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $v_sql = "SELECT * FROM transport_vouchers WHERE registration_no = '" . $row['registration_no'] . "' ORDER BY id DESC";
                                                        $v_res = $conn->query($v_sql);

                                                        if ($v_res && $v_res->num_rows > 0):
                                                            while ($v = $v_res->fetch_assoc()):
                                                                $b_class = $v['status'] == 'Paid' ? 'bg-success' : ($v['status'] == 'Unpaid' ? 'bg-danger' : 'bg-secondary');
                                                                ?>
                                                                <tr>
                                                                    <td class="ps-4 fw-bold"><?php echo $v['semester_name']; ?></td>
                                                                    <td><small><?php echo $v['route_info']; ?></small></td>
                                                                    <td class="fw-bold text-primary">PKR
                                                                        <?php echo number_format($v['total_payable']); ?></td>
                                                                    <td class="small">
                                                                        <?php echo date('d M Y', strtotime($v['due_date'])); ?></td>
                                                                    <td><span
                                                                            class="badge <?php echo $b_class; ?>"><?php echo $v['status']; ?></span>
                                                                    </td>
                                                                    <td class="text-end pe-4">

                                                                        <?php if ($v['status'] == 'Paid'): ?>
                                                                            <span class="badge bg-success px-3 py-2 fs-6 shadow-sm"><i
                                                                                    class="bi bi-lock-fill me-1"></i> Paid (Locked)</span>
                                                                        <?php else: ?>
                                                                            <form method="POST"
                                                                                class="d-flex justify-content-end align-items-center gap-2 m-0">
                                                                                <input type="hidden" name="voucher_id"
                                                                                    value="<?php echo $v['id']; ?>">
                                                                                <select name="new_status" class="form-select form-select-sm"
                                                                                    style="width: 150px; font-size: 0.85rem;">
                                                                                    <option value="Unpaid" <?php if ($v['status'] == 'Unpaid')
                                                                                        echo 'selected'; ?>>Unpaid</option>
                                                                                    <option value="Paid" <?php if ($v['status'] == 'Paid')
                                                                                        echo 'selected'; ?>>Paid</option>
                                                                                    <option value="Carried Forward" <?php if ($v['status'] == 'Carried Forward')
                                                                                        echo 'selected'; ?>>Carried Forward</option>
                                                                                </select>
                                                                                <button type="submit" name="update_status"
                                                                                    class="btn btn-sm btn-dark py-1"><i
                                                                                        class="bi bi-save me-1"></i> Update</button>
                                                                            </form>
                                                                        <?php endif; ?>

                                                                    </td>
                                                                </tr>
                                                            <?php endwhile; else: ?>
                                                            <tr>
                                                                <td colspan="6" class="text-center text-muted py-5">
                                                                    <i class="bi bi-inbox fs-3 d-block mb-2 text-secondary"></i>
                                                                    No transport vouchers generated for this student yet.
                                                                </td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>
    </div>

    <script>
        function openGroup(groupName) {
            document.getElementById('parentFolders').style.display = 'none';
            document.getElementById('studentFolders').style.display = 'flex';
            document.getElementById('currentGroupLabel').innerText = groupName;
            document.getElementById('backButton').style.display = 'inline-block';

            let items = document.querySelectorAll('.student-folder-item');
            items.forEach(function (item) {
                if (item.getAttribute('data-group') === groupName) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function goBack() {
            document.getElementById('parentFolders').style.display = 'flex';
            document.getElementById('studentFolders').style.display = 'none';
            document.getElementById('backButton').style.display = 'none';
            document.getElementById('currentGroupLabel').innerText = 'Transport Passenger Groups';
            document.getElementById('folderSearch').value = '';
        }

        function filterFolders() {
            let input = document.getElementById('folderSearch').value.toLowerCase().trim();

            if (input !== '') {
                // Global search mode
                document.getElementById('parentFolders').style.display = 'none';
                document.getElementById('studentFolders').style.display = 'flex';
                document.getElementById('backButton').style.display = 'inline-block';
                document.getElementById('currentGroupLabel').innerText = 'Global Search Results';

                let items = document.querySelectorAll('.student-folder-item');
                items.forEach(function (item) {
                    let searchableText = item.getAttribute('data-search');
                    item.style.display = searchableText.includes(input) ? 'block' : 'none';
                });
            } else {
                goBack();
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>