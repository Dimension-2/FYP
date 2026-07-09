<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// ==========================================
// 1. AUTO-SCHEMA UPDATER FOR HOSTEL FEATURES
// ==========================================
$conn->query("ALTER TABLE hostel_vouchers ADD COLUMN IF NOT EXISTS is_locked TINYINT(1) DEFAULT 0");
$conn->query("ALTER TABLE hostel_vouchers ADD COLUMN IF NOT EXISTS billing_month VARCHAR(50) NULL AFTER due_date");
$conn->query("ALTER TABLE hostel_vouchers ADD COLUMN IF NOT EXISTS fine_amount INT DEFAULT 0 AFTER mess_fee");
$conn->query("ALTER TABLE hostel_vouchers ADD COLUMN IF NOT EXISTS arrears INT DEFAULT 0 AFTER fine_amount");

// ==========================================
// 2. STATUS & LOCK/UNLOCK ENGINE
// ==========================================
if (isset($_POST['update_status'])) {
    $voucher_id = intval($_POST['voucher_id']);
    $new_status = $_POST['new_status'];
    $conn->query("UPDATE hostel_vouchers SET status = '$new_status' WHERE id = $voucher_id");
    header("Location: admin_hostel_finance.php?success=Status Updated");
    exit();
}

if (isset($_POST['toggle_lock'])) {
    $voucher_id = intval($_POST['voucher_id']);
    $conn->query("UPDATE hostel_vouchers SET is_locked = NOT is_locked WHERE id = $voucher_id");
    header("Location: admin_hostel_finance.php?success=Lock Toggled");
    exit();
}

// ==========================================
// 3. HOSTEL BILLING ENGINE (Double Verified)
// ==========================================
if (isset($_POST['generate_billing'])) {
    $next_month = date('F Y', strtotime('+1 month'));
    $due_date = date('Y-m-11', strtotime('+1 month'));

    $active_students = $conn->query("SELECT a.*, p.full_name FROM hostel_applications a LEFT JOIN profile p ON a.registration_no = p.registration_no");
    
    while ($student = $active_students->fetch_assoc()) {
        $reg_no = $student['registration_no'];
        $student_name = $student['full_name'] ?? 'Student';
        
        // Ensure we haven't already generated for this month
        $check = $conn->query("SELECT id FROM hostel_vouchers WHERE registration_no='$reg_no' AND billing_month='$next_month'");
        if ($check->num_rows > 0) continue; 

        // Check Previous Dues
        $prev_bill = $conn->query("SELECT * FROM hostel_vouchers WHERE registration_no='$reg_no' ORDER BY id DESC LIMIT 1");
        $prev_dues = 0; $fine = 0;

        if ($prev_bill->num_rows > 0) {
            $last = $prev_bill->fetch_assoc();
            if ($last['status'] == 'Unpaid') {
                $prev_dues = $last['total_payable'];
                $fine = 1000; // Apply Penalty
                // Carry forward old voucher and auto-lock it
                $conn->query("UPDATE hostel_vouchers SET status='Carried Forward', is_locked=1 WHERE id=".$last['id']);
            }
        }

        // Calculate DYNAMIC Hostel Dues based on Application Preferences
        $room_rates = ['Single' => 15000, 'Double' => 10000, 'Triple' => 7500];
        $route_rates = ['Zone A' => 3000, 'Zone B' => 5000, 'None' => 0];
        
        $room_type = $student['room_type'] ?? 'Double';
        $loc_zone = $student['location_zone'] ?? 'None';
        $inc_mess = $student['include_mess'] ?? 0;

        $security_fee = ($room_rates[$room_type] ?? 10000) + ($route_rates[$loc_zone] ?? 0); // Acts as base rent
        $mess_fee = $inc_mess ? 12000 : 0;
        
        $total = $security_fee + $mess_fee + $prev_dues + $fine;
        $challan = "HST-" . substr(md5(uniqid()), 0, 8);

        $stmt = $conn->prepare("INSERT INTO hostel_vouchers (challan_no, registration_no, student_name, due_date, billing_month, security_fee, card_charges, mess_fee, fine_amount, arrears, total_payable, status) VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, 'Unpaid')");
        $stmt->bind_param("sssssiiiii", $challan, $reg_no, $student_name, $due_date, $next_month, $security_fee, $mess_fee, $fine, $prev_dues, $total);
        $stmt->execute();
    }
    header("Location: admin_hostel_finance.php?success=Billing Generated");
    exit();
}

// ==========================================
// 4. FETCH & SMART GROUP STUDENTS (Drill-Down)
// ==========================================
$folders_query = "SELECT 
                    a.registration_no, 
                    COALESCE(p.full_name, 'Unknown Student') as student_name, 
                    COUNT(v.id) as total_vouchers,
                    SUM(CASE WHEN v.status='Unpaid' THEN 1 ELSE 0 END) as unpaid_count
                  FROM hostel_applications a
                  LEFT JOIN profile p ON a.registration_no = p.registration_no
                  LEFT JOIN hostel_vouchers v ON a.registration_no = v.registration_no
                  GROUP BY a.registration_no, p.full_name";
$folders = $conn->query($folders_query);

$grouped_students = [];

if ($folders && $folders->num_rows > 0) {
    while ($row = $folders->fetch_assoc()) {
        $reg = $row['registration_no'];
        $parts = explode('-', $reg); 
        
        // Extract Batch and Dept Safely
        if (count($parts) >= 3) {
            $batch_raw = $parts[1]; 
            $batch_num = preg_replace('/[^0-9]/', '', $batch_raw); 
            $dept = strtoupper($parts[2]); 
            $group_name = "Batch $batch_num - $dept";
        } else {
            $group_name = "Uncategorized Records";
        }

        if (!isset($grouped_students[$group_name])) {
            $grouped_students[$group_name] = ['students' => [], 'unpaid' => 0, 'total' => 0];
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
    <title>Admin - Hostel Finance</title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f4f7f6; }

        /* Literal Folder Design */
        .mac-folder { background: transparent; border: none; padding: 15px; border-radius: 12px; text-align: center; transition: all 0.2s ease-in-out; cursor: pointer; position: relative; }
        .mac-folder:hover { background: rgba(0, 0, 0, 0.05); transform: scale(1.02); }
        .folder-icon-wrapper { position: relative; display: inline-block; margin-bottom: 10px; }
        .folder-icon { font-size: 5.5rem; color: #ffd166; line-height: 1; filter: drop-shadow(0px 4px 6px rgba(0,0,0,0.1)); }
        .folder-icon.parent-icon { color: #d946ef; } /* Purple for Parent Hostel Folders */
        
        /* Notification Badge on Folder */
        .folder-badge { position: absolute; top: 15px; right: -5px; border-radius: 50%; width: 24px; height: 24px; font-size: 11px; font-weight: bold; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .student-name { font-size: 0.95rem; font-weight: 700; color: #333; margin-bottom: 2px; }
        .student-reg { font-size: 0.8rem; color: #666; font-family: monospace; }
        
        /* Search Bar */
        .search-container { background: white; border-radius: 50px; padding: 5px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; align-items: center; max-width: 600px; margin: 0 auto; }
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
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_GET['success']); ?>!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold m-0"><i class="bi bi-building-fill-check text-primary me-2"></i>Hostel Finance Desk</h4>
                        <p class="text-muted small m-0">Track, manage, and update residential dues and penalties.</p>
                    </div>
                    <button type="button" class="btn btn-danger fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#confirmGenerateModal">
                        <i class="bi bi-calendar-plus me-2"></i> Issue Next Month Billing
                    </button>
                </div>

                <div class="mb-4 mt-4 text-center">
                    <h4 class="fw-bold text-dark" id="currentGroupLabel">Hostel Residents Groups</h4>
                    <button id="backButton" class="btn btn-dark btn-sm rounded-pill px-4 mt-2 shadow-sm" style="display: none;" onclick="goBack()">
                        <i class="bi bi-arrow-left me-1"></i> Back to All Batches
                    </button>
                    <div class="search-container mt-4">
                        <i class="bi bi-search"></i>
                        <input type="text" id="folderSearch" placeholder="Search across all hostel students globally..." onkeyup="filterFolders()">
                    </div>
                </div>

                <div class="row g-3" id="parentFolders">
                    <?php foreach($grouped_students as $group_name => $group_data): 
                        $badge_html = '';
                        if ($group_data['unpaid'] > 0) {
                            $badge_html = '<div class="folder-badge bg-danger">'.$group_data['unpaid'].'</div>';
                        }
                    ?>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 parent-folder-item">
                        <div class="mac-folder" onclick="openGroup('<?php echo htmlspecialchars($group_name, ENT_QUOTES); ?>')">
                            <div class="folder-icon-wrapper">
                                <i class="bi bi-folder-fill folder-icon parent-icon"></i>
                                <?php echo $badge_html; ?>
                            </div>
                            <div class="student-name text-truncate"><?php echo $group_name; ?></div>
                            <div class="student-reg text-muted"><?php echo $group_data['total']; ?> Residents</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="row g-3" id="studentFolders" style="display: none;">
                    <?php foreach($grouped_students as $group_name => $group_data): ?>
                        <?php foreach($group_data['students'] as $row): 
                            $badge_html = '';
                            if ($row['unpaid_count'] > 0) {
                                $badge_html = '<div class="folder-badge bg-danger">'.$row['unpaid_count'].'</div>';
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
                                            <i class="bi bi-building me-2 text-warning"></i> 
                                            <?php echo htmlspecialchars($row['student_name']); ?> (<?php echo $row['registration_no']; ?>)
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0 align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="ps-4">Month/Info</th>
                                                        <th>Breakdown</th>
                                                        <th>Total Dues</th>
                                                        <th>Current Status</th>
                                                        <th class="text-end pe-4">Manual Control Panel</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $v_sql = "SELECT * FROM hostel_vouchers WHERE registration_no = '".$row['registration_no']."' ORDER BY id DESC";
                                                    $v_res = $conn->query($v_sql);
                                                    
                                                    if($v_res && $v_res->num_rows > 0):
                                                    while ($v = $v_res->fetch_assoc()):
                                                        $b_class = $v['status']=='Paid'?'bg-success':($v['status']=='Unpaid'?'bg-danger':'bg-secondary');
                                                    ?>
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div class="fw-bold text-dark"><?php echo $v['billing_month'] ?: 'Initial Term'; ?></div>
                                                            <small class="text-muted">Due: <?php echo date('d M Y', strtotime($v['due_date'])); ?></small>
                                                        </td>
                                                        <td style="font-size: 11px;">
                                                            Base/Room: <?php echo number_format($v['security_fee']); ?><br>
                                                            Mess: <?php echo number_format($v['mess_fee']); ?><br>
                                                            <span class="text-danger">Arrears & Fine: <?php echo number_format($v['arrears'] + $v['fine_amount']); ?></span>
                                                        </td>
                                                        <td class="fw-bold text-primary fs-6">PKR <?php echo number_format($v['total_payable']); ?></td>
                                                        <td><span class="badge <?php echo $b_class; ?>"><?php echo $v['status']; ?></span></td>
                                                        
                                                        <td class="text-end pe-4">
                                                            <div class="d-flex justify-content-end align-items-center gap-2 m-0">
                                                                
                                                                <?php if($v['is_locked'] == 1): ?>
                                                                    <span class="badge bg-dark d-flex align-items-center px-3 py-2"><i class="bi bi-lock-fill me-1"></i> Locked</span>
                                                                    <form method="POST" class="m-0">
                                                                        <input type="hidden" name="voucher_id" value="<?php echo $v['id']; ?>">
                                                                        <button type="submit" name="toggle_lock" class="btn btn-sm btn-outline-secondary" title="Unlock for Editing"><i class="bi bi-unlock-fill"></i> Unlock</button>
                                                                    </form>
                                                                <?php else: ?>
                                                                    <form method="POST" class="d-flex gap-1 m-0">
                                                                        <input type="hidden" name="voucher_id" value="<?php echo $v['id']; ?>">
                                                                        <select name="new_status" class="form-select form-select-sm" style="width: 140px;">
                                                                            <option value="Unpaid" <?php if($v['status']=='Unpaid') echo 'selected'; ?>>Unpaid</option>
                                                                            <option value="Paid" <?php if($v['status']=='Paid') echo 'selected'; ?>>Paid</option>
                                                                            <option value="Carried Forward" <?php if($v['status']=='Carried Forward') echo 'selected'; ?>>Carried Forward</option>
                                                                        </select>
                                                                        <button type="submit" name="update_status" class="btn btn-sm btn-primary"><i class="bi bi-save me-1"></i> Update</button>
                                                                    </form>
                                                                    <form method="POST" class="m-0">
                                                                        <input type="hidden" name="voucher_id" value="<?php echo $v['id']; ?>">
                                                                        <button type="submit" name="toggle_lock" class="btn btn-sm btn-dark" title="Lock Voucher"><i class="bi bi-lock-fill"></i></button>
                                                                    </form>
                                                                <?php endif; ?>

                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php endwhile; else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-5">
                                                            <i class="bi bi-inbox fs-3 d-block mb-2 text-secondary"></i> 
                                                            No hostel records found for this student.
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

    <div class="modal fade" id="confirmGenerateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Double Verification Required</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <i class="bi bi-calendar-check text-danger" style="font-size: 4rem;"></i>
                    <h5 class="fw-bold mt-3">Generate Next Month's Hostel Billing?</h5>
                    <p class="text-muted mb-0">This action will read the specific dues of every hostel resident. Any student with an unpaid voucher will automatically receive a <strong>1,000 PKR Penalty Fine</strong> and their arrears will be carried over to the new bill.</p>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary fw-bold" data-bs-dismiss="modal">Cancel & Go Back</button>
                    <form method="POST" class="m-0">
                        <button type="submit" name="generate_billing" class="btn btn-danger fw-bold shadow-sm">Yes, Generate All Billing</button>
                    </form>
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
            document.getElementById('currentGroupLabel').innerText = 'Hostel Residents Groups';
            document.getElementById('folderSearch').value = '';
        }

        function filterFolders() {
            let input = document.getElementById('folderSearch').value.toLowerCase().trim();
            
            if (input !== '') {
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