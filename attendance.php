<?php
session_start();

// 1. Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "fyp";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Redirect if not logged in
if (!isset($_SESSION['registration_no'])) {
    header("Location: login.php");
    exit();
}

$registration_no = $_SESSION['registration_no'];

// 2. GET STUDENT SEMESTER
$profile_query = "SELECT semester FROM profile WHERE registration_no = '$registration_no' LIMIT 1";
$profile_result = $conn->query($profile_query);
$student_data = $profile_result->fetch_assoc();
$current_semester = $student_data['semester'] ?? 1;

// 3. SYNC LOGIC: Ensure all semester courses exist in the 'attendance' table for this student
$sync_sql = "SELECT * FROM course_assignments WHERE semester = '$current_semester'";
$sync_result = $conn->query($sync_sql);

if ($sync_result->num_rows > 0) {
    while ($course = $sync_result->fetch_assoc()) {
        $c_code = $course['course_code'];
        $c_title = $course['course_title'];
        $c_hours = $course['credit_hours'];

        // Check if this course is already in the attendance summary table for this student
        $check_query = "SELECT id FROM attendance WHERE registration_no = '$registration_no' AND course_code = '$c_code'";
        $check_result = $conn->query($check_query);

        if ($check_result->num_rows == 0) {
            // If doesn't exist, insert it (initializes counts to 0)
            $insert_sql = "INSERT INTO attendance (course_code, course_title, credit_hours, total_classes, attended_classes, semester, registration_no) 
                           VALUES ('$c_code', '$c_title', '$c_hours', 0, 0, '$current_semester', '$registration_no')";
            $conn->query($insert_sql);
        }
    }
}

// 4. FETCH FINAL DATA
$sql = "SELECT 
            a.course_code, 
            a.course_title, 
            a.credit_hours,
            ca.teacher_name,
            (SELECT COUNT(*) FROM attendance_logs al 
             WHERE al.course_code = a.course_code 
             AND al.registration_no = '$registration_no') as total_classes,
            (SELECT COUNT(*) FROM attendance_logs al 
             WHERE al.course_code = a.course_code 
             AND al.status = 'Present' 
             AND al.registration_no = '$registration_no') as attended_classes
        FROM attendance a
        LEFT JOIN course_assignments ca ON a.course_code = ca.course_code
        WHERE a.registration_no = '$registration_no' AND a.semester = '$current_semester'";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Dashboard | Student Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <link rel="stylesheet" href="assets/attendance_style.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        :root {
            --premium-bg: #f8fafc;
        }

        body {
            background-color: var(--premium-bg);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            color: #334155;
        }

        /* ---------------------------------------------------
           CORE LAYOUT (MATCHING FACULTY.PHP)
        ----------------------------------------------------- */
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .content-container {
            flex-grow: 1;
            background-color: var(--premium-bg);
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        /* ---------------------------------------------------
           ATTENDANCE SPECIFIC STYLES
        ----------------------------------------------------- */
        .content-card {
            border-radius: 14px;
            background: #ffffff;
            padding: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: none !important; 
            margin-bottom: 0;
        }

        .icon-box {
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.02) 100%) !important;
            border: 1px solid rgba(16, 185, 129, 0.15);
            border-radius: 10px !important;
        }

        .icon-box i {
            color: #10b981 !important;
        }

        .custom-alert {
            background-color: #f0fdf4 !important;
            border: none !important;
            border-left: 3px solid #10b981 !important;
            border-radius: 6px;
            padding: 10px 16px;
            color: #14532d !important;
            font-size: 13px;
            font-weight: 500;
        }

        .table-responsive {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: none !important;
        }

        .table {
            margin-bottom: 0;
            border-bottom: none !important;
        }

        .table thead th {
            background-color: #f8fafc;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 14px 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table tbody td {
            padding: 14px 20px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .table tbody tr:last-child td {
            border-bottom: none !important; 
        }

        .table-hover tbody tr:hover td {
            background-color: #f8fafc;
        }

        .progress-wrapper {
            min-width: 150px;
        }

        .progress {
            border-radius: 6px;
            background-color: #f1f5f9;
            height: 6px;
            overflow: hidden;
        }

        .status-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: 1px solid transparent;
        }

        .badge-premium-success { background-color: #f0fdf4 !important; color: #166534 !important; border-color: #bbf7d0 !important; }
        .badge-premium-warning { background-color: #fffbec !important; color: #9a3412 !important; border-color: #fde68a !important; }
        .badge-premium-danger { background-color: #fef2f2 !important; color: #991b1b !important; border-color: #fecaca !important; }

        .btn-action-pill {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 5px 14px;
            border-radius: 20px;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .btn-action-pill:hover {
            background: #1e293b;
            color: #ffffff !important;
            border-color: #1e293b;
        }
    </style>
</head>

<body>

    <div class="main-wrapper">
        <?php include 'includes/navbar.php'; ?>

        <div class="content-container">
            <?php include 'includes/header.php'; ?>

            <div class="p-4">
                <div class="content-card">

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box me-3">
                                <i class="bi bi-calendar-check fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark m-0">Attendance Overview</h5>
                                <div class="text-muted mt-0.5" style="font-size: 12px; font-weight: 500;">
                                    Registration No: <span class="text-dark fw-semibold"><?php echo htmlspecialchars($registration_no); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert custom-alert d-flex align-items-center mb-3" role="alert">
                        <i class="bi bi-info-circle-fill me-2 fs-6"></i>
                        <div>
                            Currently displaying active course profiles for <span class="fw-bold">Semester <?php echo $current_semester; ?></span> track modules.
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Course & Instructor</th>
                                    <th class="text-center">CR Hours</th>
                                    <th class="text-center">Attendance</th>
                                    <th>Progress Breakdown</th>
                                    <th class="text-center">Academic Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result && $result->num_rows > 0):
                                    while ($row = $result->fetch_assoc()):
                                        $total = $row['total_classes'];
                                        $attended = $row['attended_classes'];
                                        $percentage = ($total > 0) ? round(($attended / $total) * 100) : 0;

                                        if ($percentage >= 75) {
                                            $premium_class = "badge-premium-success";
                                            $color = "success";
                                            $label = "Satisfactory";
                                            $icon = "bi-check-circle-fill";
                                        } elseif ($percentage >= 65) {
                                            $premium_class = "badge-premium-warning";
                                            $color = "warning";
                                            $label = "Low Attendance";
                                            $icon = "bi-exclamation-triangle-fill";
                                        } else {
                                            $premium_class = "badge-premium-danger";
                                            $color = "danger";
                                            $label = "Shortage Penalty";
                                            $icon = "bi-x-octagon-fill";
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark" style="font-size: 14px; letter-spacing: -0.1px;">
                                                    <?php echo htmlspecialchars($row['course_code']); ?>
                                                </div>
                                                <div class="text-secondary small my-0.5" style="font-size: 12px; font-weight: 500;">
                                                    <?php echo htmlspecialchars($row['course_title']); ?>
                                                </div>
                                                <div class="text-primary d-flex align-items-center gap-1" style="font-size: 11px; font-weight: 600;">
                                                    <i class="bi bi-person-badge-fill text-secondary"></i>
                                                    <?php echo htmlspecialchars($row['teacher_name'] ?? 'Not Assigned'); ?>
                                                </div>
                                            </td>
                                            
                                            <td class="text-center">
                                                <span class="badge bg-light text-secondary border px-2 py-1 fw-semibold" style="font-size: 11px;">
                                                    <?php echo $row['credit_hours']; ?> Cr
                                                </span>
                                            </td>
                                            
                                            <td class="text-center">
                                                <span class="fs-6 fw-bold text-dark"><?php echo $attended; ?></span>
                                                <span class="text-muted mx-0.5" style="font-size: 12px;">/</span> 
                                                <span class="text-secondary font-semibold" style="font-size: 13px;"><?php echo $total; ?></span>
                                            </td>
                                            
                                            <td class="progress-wrapper">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="fw-bold text-<?php echo $color; ?>" style="font-size: 12px;"><?php echo $percentage; ?>%</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-<?php echo $color; ?>" 
                                                         role="progressbar" 
                                                         style="width: <?php echo $percentage; ?>%" 
                                                         aria-valuenow="<?php echo $percentage; ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100"></div>
                                                </div>
                                            </td>
                                            
                                            <td class="text-center">
                                                <span class="status-badge <?php echo $premium_class; ?>">
                                                    <i class="bi <?php echo $icon; ?>"></i> <?php echo $label; ?>
                                                </span>
                                            </td>
                                            
                                            <td class="text-end">
                                                <a href="attendance_details.php?code=<?php echo urlencode($row['course_code']); ?>" 
                                                   class="btn btn-action-pill text-nowrap shadow-none">
                                                    <i class="bi bi-clock-history"></i> View Log
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-folder-x fs-2 d-block mb-2 text-secondary" style="opacity: 0.4;"></i>
                                            <span class="small fw-medium">No verified attendance records cataloged for this specific semester track.</span>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php $conn->close(); ?>
</body>

</html>