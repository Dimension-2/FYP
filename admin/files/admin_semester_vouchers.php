<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Database failure: " . $conn->connect_error);
}

// ==========================================
// 1. ADMINISTRATIVE ACTIONS ENGINE
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Status Update Engine
    if (isset($_POST['action_update_status'])) {
        $v_id = intval($_POST['voucher_id']);
        $new_status = $_POST['status'];

        $stmt = $conn->prepare("UPDATE fee_vouchers SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $v_id);
        $stmt->execute();
        $stmt->close();

        header("Location: admin_semester_vouchers.php?success=status");
        exit();
    }

    // Deadline Extension Engine
    if (isset($_POST['action_extend_date'])) {
        $v_id = intval($_POST['voucher_id']);
        $ext_date = $_POST['extended_date'];

        $stmt = $conn->prepare("UPDATE fee_vouchers SET extended_date = ? WHERE id = ?");
        $stmt->bind_param("si", $ext_date, $v_id);
        $stmt->execute();
        $stmt->close();

        header("Location: admin_semester_vouchers.php?success=date");
        exit();
    }

    // Scholarship / Waiver Engine (Works globally and inside modals)
    if (isset($_POST['action_apply_scholarship'])) {
        $reg_no = $conn->real_escape_string($_POST['registration_no']);
        $amt = doubleval($_POST['amount']);

        // Ensure student exists in scholarship table
        $conn->query("INSERT INTO scholarships (registration_no, amount) VALUES ('$reg_no', $amt) ON DUPLICATE KEY UPDATE amount=$amt");

        // Auto-update their active unpaid voucher to reflect the new discount
        $conn->query("UPDATE fee_vouchers SET scholarship_amount = $amt, total_payable = (tuition_fee + allied_charges + exam_fee + security_fee) - $amt WHERE registration_no = '$reg_no' AND status != 'Paid'");
        $conn->query("UPDATE fee_vouchers SET total_payable = 0 WHERE registration_no = '$reg_no' AND total_payable < 0");

        header("Location: admin_semester_vouchers.php?success=scholarship");
        exit();
    }

    // Rollout New Semester Vouchers
    if (isset($_POST['action_trigger_semester'])) {
        $target_sem = intval($_POST['target_semester_num']);
        $label = "Semester " . $target_sem;

        $profiles = $conn->query("SELECT registration_no FROM profile WHERE semester = $target_sem");
        while ($prof = $profiles->fetch_assoc()) {
            $reg = $prof['registration_no'];

            $hours_res = $conn->query("SELECT SUM(credit_hours) as th FROM course_assignments WHERE semester='$target_sem'");
            $hours = ($hours_res) ? intval($hours_res->fetch_assoc()['th'] ?? 0) : 0;

            $s_res = $conn->query("SELECT amount FROM scholarships WHERE registration_no='$reg' LIMIT 1");
            $sch = ($s_res && $s_res->num_rows > 0) ? doubleval($s_res->fetch_assoc()['amount']) : 0;

            $tuition = $hours * 5500;
            $allied = 12000;
            $exam = 5000;
            $security = 7000;
            $payable = ($tuition + $allied + $exam + $security) - $sch;
            if ($payable < 0)
                $payable = 0;

            $v_no = "UOW-" . rand(100000, 999999);
            $i_date = date('Y-m-d');
            $d_date = date('Y-m-d', strtotime('+14 days'));

            $check = $conn->query("SELECT id FROM fee_vouchers WHERE registration_no='$reg' AND semester_label='$label'");
            if ($check->num_rows == 0) {
                $conn->query("INSERT INTO fee_vouchers (voucher_no, registration_no, semester_label, credit_hours, tuition_fee, allied_charges, exam_fee, security_fee, scholarship_amount, total_payable, issue_date, due_date, status) VALUES ('$v_no', '$reg', '$label', $hours, $tuition, $allied, $exam, $security, $sch, $payable, '$i_date', '$d_date', 'Unpaid')");
            }
        }
        header("Location: admin_semester_vouchers.php?success=semester");
        exit();
    }
}

// ==========================================
// 2. FETCH & SMART GROUP STUDENTS
// ==========================================
$folders_query = "SELECT 
                    u.registration_no, 
                    COALESCE(p.full_name, 'Unknown Student') as student_name, 
                    p.semester,
                    COUNT(v.id) as total_vouchers,
                    SUM(CASE WHEN v.status='Unpaid' THEN 1 ELSE 0 END) as unpaid_count,
                    SUM(CASE WHEN v.status='Pending Verification' THEN 1 ELSE 0 END) as pending_count
                  FROM users u 
                  LEFT JOIN profile p ON u.registration_no = p.registration_no 
                  LEFT JOIN fee_vouchers v ON u.registration_no = v.registration_no
                  WHERE u.registration_no LIKE 'UW-%'
                  GROUP BY u.registration_no, p.full_name, p.semester
                  ORDER BY u.registration_no ASC";
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
                'pending' => 0,
                'total' => 0
            ];
        }

        $grouped_students[$group_name]['students'][] = $row;
        $grouped_students[$group_name]['unpaid'] += $row['unpaid_count'];
        $grouped_students[$group_name]['pending'] += $row['pending_count'];
        $grouped_students[$group_name]['total']++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Student Financial Folders</title>
   <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
     
    <style>
        body { background-color: #f4f7f6; }
        
        .mac-folder {
            background: transparent; border: none; padding: 15px; border-radius: 12px;
            text-align: center; transition: all 0.2s ease-in-out; cursor: pointer; position: relative;
        }
        .mac-folder:hover { background: rgba(0, 0, 0, 0.05); transform: scale(1.02); }
        .folder-icon-wrapper { position: relative; display: inline-block; margin-bottom: 10px; }
        .folder-icon { font-size: 5.5rem; color: #ffd166; line-height: 1; filter: drop-shadow(0px 4px 6px rgba(0,0,0,0.1)); }
        .folder-icon.parent-icon { color: #4361ee; } /* Blue for parent folders */
        
        .folder-badge {
            position: absolute; top: 15px; right: -5px; border-radius: 50%; width: 24px; height: 24px;
            font-size: 11px; font-weight: bold; display: flex; align-items: center; justify-content: center;
            color: white; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .student-name { font-size: 0.95rem; font-weight: 700; color: #333; margin-bottom: 2px; }
        .student-reg { font-size: 0.8rem; color: #666; font-family: monospace; }
        
        .search-container {
            background: white; border-radius: 50px; padding: 5px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex; align-items: center; max-width: 600px; margin: 0 auto;
        }
        .search-container input { border: none; box-shadow: none; outline: none; width: 100%; padding: 10px; font-size: 1rem; }
        .search-container i { color: #888; font-size: 1.2rem; }
    </style>
</head>
<body>

    <div class="main-wrapper d-flex">
        <?php include('sidebar.php'); ?>
        
        <div class="content-area flex-grow-1">
            <?php include('header.php'); ?>

            <div class="container-fluid px-4 mt-4 mb-5">
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> Action completed successfully! Database updated.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card p-3 border-0 shadow-sm h-100 rounded-4">
                            <h6 class="fw-bold text-primary"><i class="bi bi-rocket-takeoff me-2"></i>Generate Semester Vouchers</h6>
                            <form method="POST" class="d-flex gap-2 mt-2">
                                <input type="number" name="target_semester_num" class="form-control" placeholder="Semester No. (e.g. 7)" required>
                                <button type="submit" name="action_trigger_semester" class="btn btn-primary text-nowrap fw-bold shadow-sm">Rollout Invoices</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-3 border-0 shadow-sm h-100 rounded-4">
                            <h6 class="fw-bold text-success"><i class="bi bi-award me-2"></i>Global Scholarship/Waiver</h6>
                            <form method="POST" class="d-flex gap-2 mt-2">
                                <input type="text" name="registration_no" class="form-control" placeholder="Reg No (e.g. UW-...)" required>
                                <input type="number" name="amount" class="form-control" placeholder="Amount (PKR)" required>
                                <button type="submit" name="action_apply_scholarship" class="btn btn-success text-nowrap fw-bold shadow-sm">Grant</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="mb-4 mt-4 text-center">
                    <h4 class="fw-bold text-dark" id="currentGroupLabel">University Academic Groups</h4>
                    <button id="backButton" class="btn btn-dark btn-sm rounded-pill px-4 mt-2 shadow-sm" style="display: none;" onclick="goBack()">
                        <i class="bi bi-arrow-left me-1"></i> Back to All Batches
                    </button>
                    <div class="search-container mt-4">
                        <i class="bi bi-search"></i>
                        <input type="text" id="folderSearch" placeholder="Search across all students globally..." onkeyup="filterFolders()">
                    </div>
                </div>

                <div class="row g-3" id="parentFolders">
                    <?php foreach ($grouped_students as $group_name => $group_data):
                        $badge_html = '';
                        if ($group_data['unpaid'] > 0) {
                            $badge_html = '<div class="folder-badge bg-danger">' . $group_data['unpaid'] . '</div>';
                        } elseif ($group_data['pending'] > 0) {
                            $badge_html = '<div class="folder-badge bg-warning text-dark">' . $group_data['pending'] . '</div>';
                        }
                        ?>
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 parent-folder-item">
                            <div class="mac-folder" onclick="openGroup('<?php echo htmlspecialchars($group_name, ENT_QUOTES); ?>')">
                                <div class="folder-icon-wrapper">
                                    <i class="bi bi-folder-fill folder-icon parent-icon"></i>
                                    <?php echo $badge_html; ?>
                                </div>
                                <div class="student-name text-truncate"><?php echo $group_name; ?></div>
                                <div class="student-reg text-muted"><?php echo $group_data['total']; ?> Students</div>
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
                                } elseif ($row['pending_count'] > 0) {
                                    $badge_html = '<div class="folder-badge bg-warning text-dark">' . $row['pending_count'] . '</div>';
                                }
                                ?>
                                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 student-folder-item" data-group="<?php echo htmlspecialchars($group_name, ENT_QUOTES); ?>" data-search="<?php echo strtolower($row['student_name'] . ' ' . $row['registration_no']); ?>">
                                    <div class="mac-folder" data-bs-toggle="modal" data-bs-target="#modal-<?php echo preg_replace('/[^A-Za-z0-9]/', '', $row['registration_no']); ?>">
                                        <div class="folder-icon-wrapper">
                                            <i class="bi bi-folder-fill folder-icon"></i>
                                            <?php echo $badge_html; ?>
                                        </div>
                                        <div class="student-name text-truncate" title="<?php echo htmlspecialchars($row['student_name']); ?>">
                                            <?php echo htmlspecialchars($row['student_name']); ?>
                                        </div>
                                        <div class="student-reg"><?php echo $row['registration_no']; ?></div>
                                    </div>
                                </div>

                                <div class="modal fade" id="modal-<?php echo preg_replace('/[^A-Za-z0-9]/', '', $row['registration_no']); ?>" tabindex="-1">
                                    <div class="modal-dialog modal-xl modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                            <div class="modal-header bg-dark text-white border-0">
                                                <h5 class="modal-title fw-bold">
                                                    <i class="bi bi-person-circle me-2 text-warning"></i> 
                                                    <?php echo htmlspecialchars($row['student_name']); ?> (<?php echo $row['registration_no']; ?>)
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-hover mb-0 align-middle">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th class="ps-4">Voucher ID</th>
                                                                <th>Term</th>
                                                                <th>Total Payable</th>
                                                                <th>Deadline</th>
                                                                <th>Status</th>
                                                                <th class="text-end pe-4">Admin Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $v_sql = "SELECT * FROM fee_vouchers WHERE registration_no = '" . $row['registration_no'] . "' ORDER BY id DESC";
                                                            $v_res = $conn->query($v_sql);

                                                            if ($v_res && $v_res->num_rows > 0):
                                                                while ($v = $v_res->fetch_assoc()):
                                                                    $b_class = $v['status'] == 'Paid' ? 'bg-success' : ($v['status'] == 'Unpaid' ? 'bg-danger' : 'bg-warning text-dark');
                                                                    ?>
                                                                    <tr>
                                                                        <td class="ps-4 fw-bold small"><?php echo $v['voucher_no']; ?></td>
                                                                        <td><span class="badge bg-secondary"><?php echo $v['semester_label']; ?></span></td>
                                                                        <td class="fw-bold text-primary">PKR <?php echo number_format($v['total_payable']); ?></td>
                                                                        <td class="small">
                                                                            <?php echo (!empty($v['extended_date'])) ? '<span class="text-danger fw-bold"><i class="bi bi-clock-history"></i> ' . $v['extended_date'] . '</span>' : $v['due_date']; ?>
                                                                        </td>
                                                                        <td><span class="badge <?php echo $b_class; ?>"><?php echo $v['status']; ?></span></td>
                                                                        <td class="text-end pe-4">
                                                            
                                                                            <div class="d-flex justify-content-end gap-2">
                                                                
                                                                                <?php if ($v['status'] == 'Paid'): ?>
                                                                                        <span class="badge bg-success px-3 py-2 fs-6 shadow-sm"><i class="bi bi-lock-fill me-1"></i> Paid (Locked)</span>
                                                                                <?php else: ?>
                                                                                        <form method="POST" class="d-flex gap-1 m-0">
                                                                                            <input type="hidden" name="voucher_id" value="<?php echo $v['id']; ?>">
                                                                                            <select name="status" class="form-select form-select-sm" style="width: 140px;" required>
                                                                                                <option value="Unpaid" <?php if ($v['status'] == 'Unpaid')
                                                                                                    echo 'selected'; ?>>Unpaid</option>
                                                                                                <option value="Paid" <?php if ($v['status'] == 'Paid')
                                                                                                    echo 'selected'; ?>>Paid</option>
                                                                                                <option value="Pending Verification" <?php if ($v['status'] == 'Pending Verification')
                                                                                                    echo 'selected'; ?>>Pending</option>
                                                                                            </select>
                                                                                            <button type="submit" name="action_update_status" class="btn btn-sm btn-dark"><i class="bi bi-check-lg"></i> Update</button>
                                                                                        </form>

                                                                                        <form method="POST" class="d-flex gap-1 m-0">
                                                                                            <input type="hidden" name="voucher_id" value="<?php echo $v['id']; ?>">
                                                                                            <input type="date" name="extended_date" class="form-control form-control-sm" required>
                                                                                            <button type="submit" name="action_extend_date" class="btn btn-sm btn-outline-danger" title="Extend Deadline"><i class="bi bi-calendar-plus"></i></button>
                                                                                        </form>
                                                                                <?php endif; ?>
                                                                            </div>

                                                                        </td>
                                                                    </tr>
                                                                <?php endwhile; else: ?>
                                                                <tr>
                                                                    <td colspan="6" class="text-center text-muted py-5">
                                                                        <i class="bi bi-inbox fs-3 d-block mb-2 text-secondary"></i> 
                                                                        No financial records generated for this student yet.
                                                                    </td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                    
                                            <div class="modal-footer bg-light d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-success"><i class="bi bi-cash-coin me-1"></i> Issue Immediate Waiver for this Student</h6>
                                                    <small class="text-muted">Reduces total fee. Applies instantly to active unpaid voucher.</small>
                                                </div>
                                                <form method="POST" class="d-flex gap-2 m-0">
                                                    <input type="hidden" name="registration_no" value="<?php echo $row['registration_no']; ?>">
                                                    <input type="number" name="amount" class="form-control form-control-sm" placeholder="Waiver Amount (PKR)" required>
                                                    <button type="submit" name="action_apply_scholarship" class="btn btn-sm btn-success fw-bold px-3">Apply Concession</button>
                                                </form>
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
            items.forEach(function(item) {
                if(item.getAttribute('data-group') === groupName) {
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
            document.getElementById('currentGroupLabel').innerText = 'University Academic Groups';
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
                items.forEach(function(item) {
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