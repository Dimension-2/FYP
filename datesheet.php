<?php 
session_start();
$page_title = "Datesheet"; 

$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// 1. CHECK FIRST
if (!isset($_SESSION['registration_no'])) { 
    header("Location: login.php"); 
    exit(); 
}

// 2. ASSIGN SECOND
$reg_no = $_SESSION['registration_no'];
if (!isset($_SESSION['registration_no'])) { header("Location: login.php"); exit(); }
    // Fetch Student Profile Info
    $student_stmt = $conn->prepare("SELECT * FROM profile WHERE registration_no = ?");
    $student_stmt->bind_param("s", $reg_no);
    $student_stmt->execute();
    $student_res = $student_stmt->get_result();
    $student = $student_res->fetch_assoc();

    // Fetch Datesheet Records
    $datesheet_stmt = $conn->prepare("SELECT * FROM exam_datesheet WHERE registration_no = ? ORDER BY exam_date ASC");
    $datesheet_stmt->bind_param("s", $reg_no);
    $datesheet_stmt->execute();
    $datesheet_res = $datesheet_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datesheet - <?php echo $student['student_name'] ?? 'Student'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <link rel="stylesheet" href="assets/datesheet_style.css">
    <style>
        :root { 
            --sidebar-width: 260px; 
            --uw-blue: #1a365d; 
            --uw-teal: #00cba9; 
        }

        body, html { margin: 0; padding: 0; background-color: #f4f7f6; }

        .main-wrapper { display: flex; width: 100%; }

        /* Sidebar Fix: Prevents the gap */
        #sidebar { 
            width: var(--sidebar-width); 
            height: 100vh; 
            position: fixed; 
            left: 0; 
            top: 0; 
            z-index: 1000; 
        }

        /* Content Area Alignment */
        .content-area { 
            flex-grow: 1; 
            margin-left: var(--sidebar-width); 
            min-height: 100vh; 
            width: calc(100% - var(--sidebar-width)); 
        }

        /* Slip Styling */
        .slip-container {
            max-width: 900px;
            background: white;
            padding: 40px;
            border-radius: 12px;
            margin: 30px auto;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }

        .slip-header {
            border-bottom: 2px solid var(--uw-blue);
            margin-bottom: 25px;
            padding-bottom: 15px;
        }

        /* Fixed Grid for Student Data */
        .student-data-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--uw-teal);
            margin-bottom: 25px;
        }

        .data-row { display: flex; flex-direction: column; }
        .data-label { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; }
        .data-value { font-size: 15px; font-weight: 600; color: #1e293b; }

        .table thead { background-color: var(--uw-blue); color: white; }
        .table th { font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Status Badges */
        .badge-eligible { background: #dcfce7; color: #166534; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; }
        .badge-short { background: #fee2e2; color: #991b1b; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; }

        @media print {
            .no-print, #sidebar, .header-container { display: none !important; }
            .content-area { margin-left: 0 !important; width: 100% !important; }
            .slip-container { box-shadow: none !important; border: none !important; margin: 0 !important; max-width: 100% !important; }
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div id="sidebar" class="no-print">
        <?php include('includes/navbar.php'); ?>
    </div>

    <div class="content-area">
        <div class="no-print">
            <?php include('includes/header.php'); ?>
        </div>

        <div class="container-fluid">
            <div class="d-flex justify-content-end px-4 mt-3 no-print">
                <button class="btn btn-primary btn-sm px-4 fw-bold shadow-sm" onclick="window.print()">
                    <i class="bi bi-printer-fill me-2"></i> PRINT EXAMINATION SLIP
                </button>
            </div>

            <div class="slip-container">
                <div class="slip-header d-flex align-items-center">
                    <img src="assets/img/logo.png" alt="University Logo" style="width: 80px;" class="me-4">
                    <div class="text-center flex-grow-1">
                        <h2 class="fw-bold mb-0" style="color: var(--uw-blue);">UNIVERSITY OF WAH</h2>
                        <h6 class="text-muted fw-bold mb-1">EXAMINATION BRANCH</h6>
                        <span class="badge bg-dark">SPRING 2026 ENTRANCE SLIP</span>
                    </div>
                </div>

                <div class="student-data-box">
                    <div class="data-row">
                        <span class="data-label">Full Name</span>
                        <span class="data-value"><?php echo $student['full_name'] ?? 'N/A'; ?></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Registration No.</span>
                        <span class="data-value"><?php echo $student['registration_no'] ?? 'N/A'; ?></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Father's Name</span>
                        <span class="data-value"><?php echo $student['father_name'] ?? 'N/A'; ?></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Department</span>
                        <span class="data-value"><?php echo ($student['department'] ?? 'N/A') . " (" . ($student['semester'] ?? '') . ")"; ?></span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr class="text-center">
                                <th>Code</th>
                                <th class="text-start">Course Title</th>
                                <th>Exam Date</th>
                                <th>Time</th>
                                <th>Attendance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($datesheet_res->num_rows > 0): ?>
                                <?php while($row = $datesheet_res->fetch_assoc()): ?>
                                <tr class="text-center">
                                    <td class="fw-bold"><?php echo $row['course_code']; ?></td>
                                    <td class="text-start fw-semibold"><?php echo $row['course_title']; ?></td>
                                    <td><?php echo date('d-M-Y', strtotime($row['exam_date'])); ?></td>
                                    <td><?php echo $row['exam_time']; ?></td>
                                    <td class="fw-bold <?php echo $row['attendance_pct'] < 75 ? 'text-danger' : ''; ?>">
                                        <?php echo $row['attendance_pct']; ?>%
                                    </td>
                                    <td>
                                        <?php if($row['status'] == 'Eligible'): ?>
                                            <span class="badge-eligible text-uppercase">Eligible</span>
                                        <?php else: ?>
                                            <span class="badge-short text-uppercase">Short Attendance</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center p-4 text-muted">No records found for this registration number.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <div class="row small text-muted">
                        <div class="col-8">
                            <p class="mb-1"><strong>Instructions:</strong></p>
                            <ul class="ps-3 mb-0">
                                <li>Bring your Student ID Card and this Slip to the Exam Hall.</li>
                                <li>Mobile phones are strictly prohibited.</li>
                            </ul>
                        </div>
                        <div class="col-4 text-center">
                            <div style="height: 50px;"></div>
                            <p class="mb-0 fw-bold border-top pt-1">Examination Branch</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>