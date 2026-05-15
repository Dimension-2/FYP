<?php
session_start();
$current_page = 'attendance.php';
$page_title = 'Attendance History'; 
$registration_no = $_SESSION['registration_no'];
// 1. Database Connection
$conn = new mysqli("localhost", "root", "", "fyp");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Get Course Info from URL
$course_code = isset($_GET['code']) ? $_GET['code'] : 'Unknown';
$course_title = isset($_GET['title']) ? $_GET['title'] : 'Course Details';

// 3. FETCH STATISTICS FOR TOP BAR
$stats_sql = "SELECT 
                COUNT(*) as total, 
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as presents,
                SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absents
              FROM attendance_logs WHERE course_code = '$course_code' AND registration_no = '$registration_no'";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

$total_lec = $stats['total'] ?? 0;
$present_count = $stats['presents'] ?? 0;
$absent_count = $stats['absents'] ?? 0;
$percentage = ($total_lec > 0) ? round(($present_count / $total_lec) * 100) : 0;

// 4. Fetch History Logs
$sql = "SELECT * FROM attendance_logs WHERE course_code = '$course_code' AND registration_no = '$registration_no' ORDER BY date DESC, lecture_no DESC";
$result = $conn->query($sql);

include 'includes/navbar.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - <?php echo htmlspecialchars($course_code); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <link rel="stylesheet" href="assets/faculty.css"> 
    <link rel="stylesheet" href="assets/details.css"> 
</head>
<body>

<div class="main-wrapper">
    <?php include 'includes/header.php'; ?>
    
    <div class="content-container">
        
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="summary-card bg-white shadow-sm border-0 p-3 rounded-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-primary-subtle text-primary me-3">
                            <i class="bi bi-collection"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Lectures</small>
                            <span class="h4 fw-bold mb-0"><?php echo $total_lec; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card bg-white shadow-sm border-0 p-3 rounded-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-success-subtle text-success me-3">
                            <i class="bi bi-check-all"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Present</small>
                            <span class="h4 fw-bold mb-0"><?php echo $present_count; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card bg-white shadow-sm border-0 p-3 rounded-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-danger-subtle text-danger me-3">
                            <i class="bi bi-x-lg"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Absent</small>
                            <span class="h4 fw-bold mb-0"><?php echo $absent_count; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card bg-white shadow-sm border-0 p-3 rounded-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle <?php echo ($percentage < 75) ? 'bg-danger text-white' : 'bg-info-subtle text-info'; ?> me-3">
                            <i class="bi bi-percent"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Percentage</small>
                            <span class="h4 fw-bold mb-0"><?php echo $percentage; ?>%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-card shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h3 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($course_title); ?></h3>
                    <span class="text-teal small fw-bold uppercase tracking-wider"><?php echo htmlspecialchars($course_code); ?></span>
                </div>
                <a href="attendance.php" class="btn btn-dark btn-sm rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr class="text-secondary small uppercase">
                            <th class="ps-4">Date & Day</th>
                            <th>Status</th>
                            <th>Topic Covered</th>
                            <th class="text-center">Lecture</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): 
                                $formatted_date = date('d M, Y', strtotime($row['date']));
                                $day_name = date('l', strtotime($row['date']));
                            ?>
                            <tr class="history-row">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="date-icon me-3">
                                            <span class="month"><?php echo date('M', strtotime($row['date'])); ?></span>
                                            <span class="day"><?php echo date('d', strtotime($row['date'])); ?></span>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo $day_name; ?></div>
                                            <div class="small text-muted"><?php echo $formatted_date; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if($row['status'] == 'Present'): ?>
                                        <span class="status-pill status-present">Present</span>
                                    <?php else: ?>
                                        <span class="status-pill status-absent">Absent</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="topic-box text-secondary">
                                        <i class="bi bi-journal-bookmark me-2 opacity-50"></i>
                                        <?php echo htmlspecialchars($row['topic']); ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="lecture-badge">L-<?php echo $row['lecture_no']; ?></span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-5">No logs found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $conn->close(); ?>
</body>
</html>