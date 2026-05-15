<?php 
    session_start();
    // Database connection
    $conn = new mysqli("localhost", "root", "", "fyp");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
if (!isset($_SESSION['registration_no'])) { header("Location: login.php"); exit(); }
    $page_title = "Complaints Manager"; 
   $student_reg = $_SESSION['registration_no'];

    // 1. Handle Receipt Download (Pulling from DB)
    // 1. Handle Receipt View (Formatted for PDF/Print)
    if (isset($_GET['download_id'])) {
        $dl_id = $_GET['download_id'];
        // Join with a student table if you have one to get the 'student_name'
        $dl_sql = "SELECT * FROM complaints WHERE complaint_id = '$dl_id' AND registration_no = '$student_reg'";
        $dl_res = $conn->query($dl_sql);
        
        if ($dl_res && $comp = $dl_res->fetch_assoc()) {
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <style>
                    body { font-family: sans-serif; padding: 30px; color: #333; }
                    .receipt-card { border: 2px solid #000; padding: 20px; position: relative; max-width: 600px; margin: auto; }
                    .header { text-align: center; border-bottom: 2px solid #024b30; pb: 10px; mb: 20px; }
                    .logo { font-size: 24px; font-weight: bold; color: #024b30; }
                    .stamp { position: absolute; bottom: 20px; right: 20px; border: 3px double #d9534f; color: #d9534f; padding: 5px 10px; transform: rotate(-15deg); font-weight: bold; border-radius: 5px; opacity: 0.7; }
                    .row { display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px dashed #ccc; }
                    .label { font-weight: bold; }
                    @media print { .no-print { display: none; } }
                </style>
            </head>
            <body onload="window.print()">
                <div class="no-print" style="text-align:center; margin-bottom: 20px;">
                    <button onclick="window.location.href='complaints.php'">← Back to Manager</button>
                    <p>Select "Save as PDF" in the print destination.</p>
                </div>
                <div class="receipt-card">
                    <div class="header">
                        <div class="logo">UNIVERSITY OF WAH</div>
                        <div style="font-size: 12px;">Official Complaint Acknowledgment</div>
                    </div>
                    <div class="row"><span class="label">Complaint ID:</span> <span><?php echo $comp['complaint_id']; ?></span></div>
                    <div class="row"><span class="label">Reg No:</span> <span><?php echo $comp['registration_no']; ?></span></div>
                    <div class="row"><span class="label">Category:</span> <span><?php echo $comp['category']; ?></span></div>
                    <div class="row"><span class="label">Subject:</span> <span><?php echo $comp['subject']; ?></span></div>
                    <div class="row"><span class="label">Date:</span> <span><?php echo date('d-M-Y', strtotime($comp['created_at'])); ?></span></div>
                    <div class="row"><span class="label">Status:</span> <span style="text-transform: uppercase; color: blue;"><?php echo $comp['status']; ?></span></div>
                    
                    <div style="margin-top: 20px;">
                        <div class="label">Notes:</div>
                        <div style="font-size: 13px; margin-top: 5px; min-height: 100px; border: 1px solid #eee; padding: 10px;">
                            <?php echo nl2br($comp['notes']); ?>
                        </div>
                    </div>

                    <div class="stamp">OFFICIALLY RECORDED</div>
                    
                    <div style="margin-top: 50px; font-size: 10px; text-align: center; border-top: 1px solid #ccc; padding-top: 5px;">
                        This is a computer-generated receipt and does not require a physical signature.
                    </div>
                </div>
            </body>
            </html>
            <?php
            exit();
        }
    }
    

    // 2. Handle Form Submission (Saving to DB)
   // 2. Handle Form Submission (Saving to DB)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_complaint'])) {
        // Generate a random ID: CMP + Year + 4 random digits (e.g., CMP26-8234)
        $c_id = 'CMP' . date('y') . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        $cat = $conn->real_escape_string($_POST['category']);
        $sub = $conn->real_escape_string($_POST['subject']);
        $notes = $conn->real_escape_string($_POST['notes']);

        $ins_sql = "INSERT INTO complaints (complaint_id, registration_no, category, subject, notes, status) 
                    VALUES ('$c_id', '$student_reg', '$cat', '$sub', '$notes', 'Pending')";
        
        if ($conn->query($ins_sql)) {
            header("Location: complaints.php?status=submitted");
            exit();
        }
    }
    

    // 3. Fetch History Count for Badge
    $count_sql = "SELECT COUNT(*) as total FROM complaints WHERE registration_no = '$student_reg'";
    $count_res = $conn->query($count_sql);
    $total_complaints = $count_res->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints Manager - University of Wah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <link rel="stylesheet" href="assets/complaints.css">
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
            <div class="page-title-box mb-4">
                <h4 class="fw-bold text-dark"><i class="bi bi-chat-left-text me-2"></i>COMPLAINTS MANAGER</h4>
            </div>

            <?php if(isset($_GET['status']) && $_GET['status'] == 'submitted'): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Your complaint has been successfully recorded.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <ul class="nav nav-tabs border-bottom-0" id="complaintTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#compose">Compose</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#history">
                        History <span class="badge rounded-pill bg-danger ms-1"><?php echo $total_complaints; ?></span>
                    </button>
                </li>
            </ul>

            <div class="tab-content bg-white border rounded-bottom shadow-sm p-4">
                <div class="tab-pane fade show active" id="compose">
                    <h6 class="fw-bold text-success mb-3">NEW COMPLAINT</h6>
                    <form action="" method="POST">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select shadow-none" required>
                                    <option value="">Select Recipient</option>
                                    <option>Departmental Issues (Chairperson)</option>
                                    <option>Dean</option>
                                    <option>Administrative Issues</option>
                                    <option>IT / AVICENNA</option>
                                    <option>Finance related issues</option>
                                    <option>Transport related issues</option>
                                    <option>Registrar</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Subject <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control shadow-none" placeholder="Enter subject" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Notes</label>
                                <textarea name="notes" class="form-control shadow-none" rows="8" placeholder="Type details here..."></textarea>
                            </div>
                            <div class="col-12 text-end pt-2">
                                <button type="reset" class="btn btn-secondary px-4 me-2">Clear</button>
                                <button type="submit" name="submit_complaint" class="btn btn-primary px-4">Submit Complaint</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="history">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">ID</th>
                                    <th>Recipient</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $hist_sql = "SELECT * FROM complaints WHERE registration_no = '$student_reg' ORDER BY created_at DESC";
                                $hist_res = $conn->query($hist_sql);

                                if($hist_res && $hist_res->num_rows > 0): 
                                    while($comp = $hist_res->fetch_assoc()):
                                        // Status badge coloring
                                        $status_class = "bg-warning text-dark"; // Default Pending
                                        if($comp['status'] == 'Resolved') $status_class = "bg-success";
                                        if($comp['status'] == 'Rejected') $status_class = "bg-danger";
                                        if($comp['status'] == 'In Progress') $status_class = "bg-info text-dark";
                                ?>
                                    <tr>
                                        <td class="ps-3 small fw-bold text-primary"><?php echo $comp['complaint_id']; ?></td>
                                        <td class="small"><?php echo $comp['category']; ?></td>
                                        <td class="fw-semibold small"><?php echo $comp['subject']; ?></td>
                                        <td class="small text-muted"><?php echo date('d-M-y', strtotime($comp['created_at'])); ?></td>
                                        <td><span class="badge <?php echo $status_class; ?> px-2 py-1" style="font-size: 0.7rem;"><?php echo $comp['status']; ?></span></td>
                                        <td class="text-end pe-3">
                                            <a href="?download_id=<?php echo $comp['complaint_id']; ?>" class="btn btn-sm btn-outline-dark border-0">
                                                <i class="bi bi-download"></i> Receipt
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center py-5 text-muted small">No history available in database.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>