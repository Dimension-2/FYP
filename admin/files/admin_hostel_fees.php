<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$page_title = "Admin - Hostel Fee Management";

// 1. Handle Approve / Reject Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $voucher_id = intval($_POST['voucher_id']);
    $new_status = ($_POST['action'] == 'approve') ? 'Paid' : 'Unpaid';

    $stmt = $conn->prepare("UPDATE hostel_vouchers SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $voucher_id);
    $stmt->execute();
    $stmt->close();

    $message = "<div class='alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4' role='alert'>
                    <i class='bi bi-check-circle-fill me-2'></i>Voucher #$voucher_id status successfully updated to <strong>$new_status</strong>.
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
}

// 2. Fetch Search and Tab Filter Inputs (Sanitized for High Sensitivity)
$search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
$tab_filter = isset($_GET['tab']) ? $_GET['tab'] : 'folders'; // Default is now Folders

// 3. Dynamic Calculation for KPI Stats Cards
$total_vouchers = $conn->query("SELECT COUNT(*) as count FROM hostel_vouchers")->fetch_assoc()['count'] ?? 0;
$paid_vouchers = $conn->query("SELECT COUNT(*) as count FROM hostel_vouchers WHERE status='Paid'")->fetch_assoc()['count'] ?? 0;
$unpaid_vouchers = $conn->query("SELECT COUNT(*) as count FROM hostel_vouchers WHERE status='Unpaid'")->fetch_assoc()['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Hostel Fees Dashboard | University Portal</title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .kpi-card { border-radius: 12px; transition: transform 0.2s; }
        .kpi-card:hover { transform: translateY(-3px); }
        .nav-pills .nav-link.active { background-color: #212529; color: #fff; }
        .nav-pills .nav-link { color: #495057; font-weight: 600; }
        .student-folder-card { cursor: pointer; transition: all 0.2s ease-in-out; border-radius: 15px; border: 2px solid transparent; }
        .student-folder-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; border-color: #ffc107; }
        .folder-icon-wrapper { width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #f8f9fa; margin: 0 auto 10px; }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <?php include('sidebar.php'); ?>
        <div class="content-area">
            <?php include('header.php'); ?>

            <main class="dashboard-body">
                <div class="container-fluid px-4 py-2">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold text-dark mb-1"><i class="bi bi-shield-check text-primary me-2"></i>Hostel Finance Desk</h2>
                            <p class="text-muted small mb-0">Track, search, and approve monthly student accommodations fees</p>
                        </div>
                    </div>

                    <?php if (isset($message)) echo $message; ?>

                    <!-- KPI STATS CARDS -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card kpi-card bg-white border-0 shadow-sm p-3 border-start border-primary border-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted text-uppercase small fw-bold">Total Enrolled Bills</h6>
                                        <h3 class="fw-bold text-dark mb-0"><?php echo $total_vouchers; ?></h3>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary"><i class="bi bi-file-earmark-text fs-3"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card kpi-card bg-white border-0 shadow-sm p-3 border-start border-success border-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted text-uppercase small fw-bold">Verified Paid Accounts</h6>
                                        <h3 class="fw-bold text-success mb-0"><?php echo $paid_vouchers; ?></h3>
                                    </div>
                                    <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success"><i class="bi bi-cash-stack fs-3"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card kpi-card bg-white border-0 shadow-sm p-3 border-start border-danger border-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted text-uppercase small fw-bold">Pending / Overdue Bills</h6>
                                        <h3 class="fw-bold text-danger mb-0"><?php echo $unpaid_vouchers; ?></h3>
                                    </div>
                                    <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger"><i class="bi bi-exclamation-octagon fs-3"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEARCH BAR & TABS SELECTION -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-3 bg-white rounded">
                            <form method="GET" action="" class="row g-3 align-items-center">
                                <div class="col-md-6">
                                    <ul class="nav nav-pills">
                                        <!-- NEW: Directory Folder Tab -->
                                        <li class="nav-item">
                                            <a class="nav-link <?php if ($tab_filter == 'folders') echo 'active'; ?>" href="?tab=folders&search=<?php echo urlencode($search); ?>"><i class="bi bi-grid-fill me-1"></i> Student Folders</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php if ($tab_filter == 'all') echo 'active'; ?>" href="?tab=all&search=<?php echo urlencode($search); ?>"><i class="bi bi-list-task me-1"></i> All Vouchers</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php if ($tab_filter == 'unpaid') echo 'active'; ?>" href="?tab=unpaid&search=<?php echo urlencode($search); ?>"><i class="bi bi-clock-history me-1"></i> Unpaid</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab_filter); ?>">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                        <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Search student name or reg no..." value="<?php echo htmlspecialchars($search); ?>">
                                        <button type="submit" class="btn btn-dark fw-bold px-4">Search</button>
                                        <?php if (!empty($search)): ?>
                                            <a href="?tab=<?php echo $tab_filter; ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php
                    // Build dynamic system query matching search criteria
                    $where_clauses = [];
                    if ($tab_filter == 'paid') { $where_clauses[] = "status='Paid'"; } 
                    elseif ($tab_filter == 'unpaid') { $where_clauses[] = "status='Unpaid'"; }

                    // Highly Sensitive Search Logic
                    if (!empty($search)) {
                        $search_terms = explode(' ', $search);
                        $search_conditions = [];
                        foreach ($search_terms as $term) {
                            if (!empty($term)) {
                                $search_conditions[] = "(registration_no LIKE '%$term%' OR student_name LIKE '%$term%' OR challan_no LIKE '%$term%' OR status LIKE '%$term%')";
                            }
                        }
                        if (count($search_conditions) > 0) {
                            $where_clauses[] = "(" . implode(" AND ", $search_conditions) . ")";
                        }
                    }

                    $query = "SELECT * FROM hostel_vouchers";
                    if (count($where_clauses) > 0) {
                        $query .= " WHERE " . implode(" AND ", $where_clauses);
                    }
                    $query .= " ORDER BY id DESC";

                    $result = $conn->query($query);
                    $all_records_cache = [];
                    $student_profiles = []; // Array to group students

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $all_records_cache[] = $row;
                            
                            // Build the Student Folders grouping
                            $reg = $row['registration_no'];
                            if (!isset($student_profiles[$reg])) {
                                $student_profiles[$reg] = [
                                    'name' => $row['student_name'],
                                    'reg_no' => $reg,
                                    'total_vouchers' => 0,
                                    'unpaid_dues' => 0
                                ];
                            }
                            $student_profiles[$reg]['total_vouchers'] += 1;
                            if ($row['status'] == 'Unpaid') {
                                $student_profiles[$reg]['unpaid_dues'] += 1;
                            }
                        }
                    }
                    ?>

                    <?php if ($tab_filter == 'folders'): ?>
                        <!-- NEW FEATURE: STUDENT FOLDER PROFILE GRID -->
                        <div class="row g-4">
                            <?php if (count($student_profiles) > 0): ?>
                                <?php foreach ($student_profiles as $profile): ?>
                                    <div class="col-xl-3 col-lg-4 col-md-6">
                                        <div class="card bg-white shadow-sm student-folder-card h-100" onclick='viewStudentFolder("<?php echo htmlspecialchars($profile['reg_no'], ENT_QUOTES); ?>", "<?php echo htmlspecialchars($profile['name'], ENT_QUOTES); ?>")'>
                                            <div class="card-body text-center p-4">
                                                <div class="folder-icon-wrapper text-warning">
                                                    <i class="bi bi-folder-fill fs-1"></i>
                                                </div>
                                                <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?php echo $profile['name']; ?>"><?php echo $profile['name']; ?></h5>
                                                <span class="badge bg-light text-dark border mb-3"><?php echo $profile['reg_no']; ?></span>
                                                
                                                <div class="d-flex justify-content-between align-items-center bg-light rounded p-2 small">
                                                    <div class="text-start">
                                                        <span class="text-muted d-block" style="font-size: 11px;">Total Records</span>
                                                        <strong class="text-dark"><?php echo $profile['total_vouchers']; ?> Vouchers</strong>
                                                    </div>
                                                    <div class="text-end border-start ps-2">
                                                        <span class="text-muted d-block" style="font-size: 11px;">Status</span>
                                                        <?php if ($profile['unpaid_dues'] > 0): ?>
                                                            <strong class="text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i><?php echo $profile['unpaid_dues']; ?> Pending</strong>
                                                        <?php else: ?>
                                                            <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i>All Clear</strong>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <button class="btn btn-outline-dark btn-sm w-100 mt-3 fw-bold"><i class="bi bi-box-arrow-up-right me-1"></i> Open Folder</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center py-5">
                                    <i class="bi bi-search-heart display-6 text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No student profiles found matching your search.</h5>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- TRADITIONAL MASTER DATABASE TABLE (For All / Unpaid Tabs) -->
                        <div class="card border-0 shadow-sm bg-white">
                            <div class="card-header bg-dark text-white fw-bold py-3">
                                <i class="bi bi-table me-2"></i>Live Master Database Log
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3">Challan ID</th>
                                                <th>Registration No</th>
                                                <th>Student Name</th>
                                                <th>Amount Due</th>
                                                <th>Deadline</th>
                                                <th>Status</th>
                                                <th>Document</th>
                                                <th class="text-end pe-3">Desk Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($all_records_cache) > 0): ?>
                                                <?php foreach ($all_records_cache as $row): 
                                                    $is_late = (strtotime($row['due_date']) < time() && $row['status'] == 'Unpaid');
                                                    $badge_color = $row['status'] == 'Paid' ? 'bg-success' : ($is_late ? 'bg-dark' : 'bg-danger');
                                                    $status_text = $row['status'] == 'Paid' ? 'Paid' : ($is_late ? 'Locked' : 'Unpaid');
                                                    
                                                    $file_icon = "bi-file-earmark-arrow-up";
                                                    if (!empty($row['receipt_image'])) {
                                                        $ext = strtolower(pathinfo($row['receipt_image'], PATHINFO_EXTENSION));
                                                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) $file_icon = "bi-file-earmark-image text-warning";
                                                        elseif ($ext === 'pdf') $file_icon = "bi-file-earmark-pdf text-danger";
                                                        elseif (in_array($ext, ['doc', 'docx'])) $file_icon = "bi-file-earmark-word text-primary";
                                                    }
                                                ?>
                                                    <tr>
                                                        <td class='ps-3 fw-bold text-secondary'><?php echo $row['challan_no']; ?></td>
                                                        <td><span class='badge bg-light text-dark border p-2' style="cursor:pointer;" onclick='viewStudentFolder("<?php echo htmlspecialchars($row['registration_no'], ENT_QUOTES); ?>", "<?php echo htmlspecialchars($row['student_name'], ENT_QUOTES); ?>")'><i class='bi bi-folder2-open text-warning me-1'></i><?php echo $row['registration_no']; ?></span></td>
                                                        <td><h6 class='mb-0 fw-bold text-dark'><?php echo $row['student_name']; ?></h6></td>
                                                        <td class='fw-bold text-success'>PKR <?php echo number_format($row['total_payable']); ?></td>
                                                        <td><?php echo date('d-M-Y', strtotime($row['due_date'])); ?></td>
                                                        <td><span class='badge <?php echo $badge_color; ?>'><?php echo $status_text; ?></span></td>
                                                        <td>
                                                            <?php if (!empty($row['receipt_image'])): ?>
                                                                <a href='uploads/<?php echo $row['receipt_image']; ?>' target='_blank' class='btn btn-sm btn-outline-secondary fw-bold'><i class='bi <?php echo $file_icon; ?> me-1'></i> View File</a>
                                                            <?php else: ?>
                                                                <span class='text-muted small'><i class='bi bi-clock'></i> None</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class='text-end pe-3'>
                                                            <form method='POST' style='display:inline-block;'>
                                                                <input type='hidden' name='voucher_id' value='<?php echo $row['id']; ?>'>
                                                                <?php if ($row['status'] == 'Unpaid'): ?>
                                                                    <button type='submit' name='action' value='approve' class='btn btn-sm btn-success fw-bold shadow-sm'><i class='bi bi-check2-circle'></i> Verify</button>
                                                                <?php else: ?>
                                                                    <button type='submit' name='action' value='reject' class='btn btn-sm btn-outline-danger fw-bold'><i class='bi bi-arrow-counterclockwise'></i> Revert</button>
                                                                <?php endif; ?>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </main>
        </div>
    </div>

    <!-- INTERACTIVE STUDENT RECORD FOLDER MODAL DRAWER -->
    <div class="modal fade" id="studentFolderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold" id="folderTitle"><i class="bi bi-folder-symlink text-warning me-2"></i> Student Portfolio Folder</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row align-items-center mb-4">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center p-3 bg-light rounded-3 border">
                                <div class="bg-secondary bg-opacity-10 p-3 rounded-circle text-secondary me-3 border">
                                    <i class="bi bi-person-bounding-box fs-3"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-1 text-dark" id="folderStudentName">---</h4>
                                    <span class="badge bg-dark px-3 py-2 fs-6" id="folderRegNo">---</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                             <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-3 border border-danger border-opacity-25 d-inline-block text-center">
                                 <h6 class="mb-0 fw-bold">Total Pending Fines / Dues</h6>
                                 <h3 class="fw-bold mb-0 mt-1" id="folderTotalPending">PKR 0</h3>
                             </div>
                        </div>
                    </div>
                    
                    <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2"><i class="bi bi-clock-history me-1"></i> Fee Payment History & Fine Statements</h6>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-light small text-uppercase fw-bold text-center">
                                <tr>
                                    <th>Challan ID</th>
                                    <th>Total Billed</th>
                                    <th>Deadline</th>
                                    <th>Fines Status</th>
                                    <th>Payment Status</th>
                                    <th>Attached Doc</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="folderTableBody" class="text-center">
                                <!-- Dynamic history records will be appended here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-secondary px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Close Folder Drawer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inject the PHP internal records cache array securely into Javascript
        const voucherDatabase = <?php echo json_encode($all_records_cache); ?>;

        function viewStudentFolder(regNo, studentName) {
            document.getElementById('folderStudentName').innerText = studentName;
            document.getElementById('folderRegNo').innerText = regNo;
            
            const tableBody = document.getElementById('folderTableBody');
            tableBody.innerHTML = ''; 
            
            // Filter global cache database for this specific student portfolio
            const studentRecords = voucherDatabase.filter(item => item.registration_no === regNo);
            let totalPendingMoney = 0;
            
            if (studentRecords.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-folder-x display-6 d-block mb-2"></i>No structural logs found for this folder.</td></tr>';
            } else {
                studentRecords.forEach(record => {
                    const dueDate = new Date(record.due_date);
                    const today = new Date();
                    const isOverdue = (dueDate < today && record.status === 'Unpaid');
                    
                    if(record.status === 'Unpaid') {
                        totalPendingMoney += parseInt(record.total_payable);
                    }
                    
                    let statusBadge = record.status === 'Paid' 
                        ? '<span class="badge bg-success px-3 py-2">Verified Paid</span>' 
                        : (isOverdue ? '<span class="badge bg-dark px-3 py-2">Locked / Overdue</span>' : '<span class="badge bg-danger px-3 py-2">Unpaid</span>');
                        
                    let fineColumn = isOverdue 
                        ? '<span class="text-danger fw-bold bg-danger bg-opacity-10 px-2 py-1 rounded"><i class="bi bi-exclamation-triangle-fill"></i> + PKR 1,000 Late Fine</span>' 
                        : '<span class="text-muted small">Standard Rate</span>';

                    let docLink = record.receipt_image 
                        ? `<a href="uploads/${record.receipt_image}" target="_blank" class="btn btn-sm btn-info text-white fw-bold shadow-sm"><i class="bi bi-file-earmark-check"></i> View Document</a>` 
                        : '<span class="text-muted small fst-italic">No File Attached</span>';

                    let amountFormatted = new Intl.NumberFormat().format(record.total_payable);
                    let dateFormatted = new Date(record.due_date).toLocaleDateString('en-GB', {
                        day: '2-digit', month: 'short', year: 'numeric'
                    });
                    
                    let actionButton = record.status === 'Unpaid' 
                        ? `<form method="POST" class="m-0"><input type="hidden" name="voucher_id" value="${record.id}"><button type="submit" name="action" value="approve" class="btn btn-sm btn-success fw-bold shadow-sm"><i class="bi bi-check2-circle"></i> Verify</button></form>`
                        : `<span class="text-success fw-bold small"><i class="bi bi-check-circle-fill"></i> Cleared</span>`;

                    let rowHTML = `
                        <tr>
                            <td class="fw-bold text-secondary text-start ps-3">${record.challan_no}</td>
                            <td class="fw-bold text-dark fs-6">PKR ${amountFormatted}</td>
                            <td class="small fw-medium">${dateFormatted}</td>
                            <td class="small">${fineColumn}</td>
                            <td>${statusBadge}</td>
                            <td>${docLink}</td>
                            <td class="text-end pe-3">${actionButton}</td>
                        </tr>
                    `;
                    tableBody.innerHTML += rowHTML;
                });
            }
            
            // Update the top pending money counter in the modal
            document.getElementById('folderTotalPending').innerText = "PKR " + new Intl.NumberFormat().format(totalPendingMoney);
            
            // Open the customized student structural profile modal drawer
            var folderModal = new bootstrap.Modal(document.getElementById('studentFolderModal'));
            folderModal.show();
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>