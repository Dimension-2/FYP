<?php
session_start();

$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Connection failed");
}
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
if (!isset($_SESSION['registration_no'])) {
    header("Location: login.php");
    exit();
}
$student_roll = $_SESSION['registration_no'];

// 1. Fetch Profile (Updated table and column names)
$stmt_profile = $conn->prepare("SELECT * FROM profile WHERE registration_no = ? LIMIT 1");
$stmt_profile->bind_param("s", $student_roll);
$stmt_profile->execute();
$student = $stmt_profile->get_result()->fetch_assoc();
$semester = $student['semester'] ?? 1;

// 2. Configuration & Grading
$weights = ['Sessional' => 0.25, 'Mid' => 0.30, 'Final' => 0.45];

function calculateGrade($score)
{
    if ($score >= 80)
        return ['A', 4.0];
    if ($score >= 70)
        return ['B', 3.0];
    if ($score >= 60)
        return ['C', 2.0];
    if ($score >= 50)
        return ['D', 1.0];
    return ['F', 0.0];
}

// 3. Fetch Courses and Calculate Marks (Replicating Gradebook logic)
$course_query = "SELECT * FROM course_assignments WHERE semester = '$semester'";
$course_res = $conn->query($course_query);

$total_cr = 3; // You might want to pull this from your course table
$total_gp_weighted = 0;
$total_credits_earned = 0;
$subjects = [];
$has_failed_grade = false;

if ($course_res && $course_res->num_rows > 0) {
    while ($course = $course_res->fetch_assoc()) {
        $c_code = $course['course_code'];
        $course_weighted_total = 0;

        // Internal category storage for the table display
        $display_marks = ['Sessional' => 0, 'Mid' => 0, 'Final' => 0];

        $categories = ['Sessional', 'Mid', 'Final'];
        foreach ($categories as $cat) {
            $cat_ob = 0.0;  // Initialize as float
            $cat_tot = 0.0; // Initialize as float

            if ($cat === 'Sessional') {
                $item_stmt = $conn->prepare("SELECT id, total_marks FROM grading_items WHERE course_code = ? AND (item_type = 'Sessional' OR item_type IS NULL OR item_type = '')");
                $item_stmt->bind_param("s", $c_code);
            } else {
                $item_stmt = $conn->prepare("SELECT id, total_marks FROM grading_items WHERE course_code = ? AND item_type = ?");
                $item_stmt->bind_param("ss", $c_code, $cat);
            }

            $item_stmt->execute();
            $items = $item_stmt->get_result();

            while ($item = $items->fetch_assoc()) {
                $item_id = $item['id'];
                $mark_stmt = $conn->prepare("SELECT obtained_marks FROM student_marks WHERE registration_no = ? AND item_id = ?");
                $mark_stmt->bind_param("si", $student_roll, $item_id);
                $mark_stmt->execute();
                $mark_res = $mark_stmt->get_result()->fetch_assoc();

                // FIX: Cast everything to float immediately upon retrieval
                $obtained = (float) ($mark_res['obtained_marks'] ?? 0.0);
                $total_avail = (float) ($item['total_marks'] ?? 0.0);

                $cat_ob += $obtained;
                $cat_tot += $total_avail;
            }

            // FIX: Guard against division by zero and ensure float math
            $perc = ($cat_tot > 0) ? ($cat_ob / $cat_tot) * 100.0 : 0.0;

            // FIX: Safely get weight
            $cat_weight = (float) ($weights[$cat] ?? 0.0);

            // FIX: Perform contribution math
            $weighted_contribution = (float) $perc * (float) $cat_weight;

            $course_weighted_total += (float) $weighted_contribution;

            // Store for table display
            $ui_limit = ($cat == 'Sessional') ? 30 : (($cat == 'Mid') ? 25 : 45);
            $display_marks[$cat] = round(((float) $perc / 100.0) * (float) $ui_limit, 1);
        }
        $grade_info = calculateGrade($course_weighted_total);
        if ($grade_info[0] == 'F')
            $has_failed_grade = true;

        $cr_hours = $course['credit_hours'] ?? 3; // Defaulting to 3 if not found
        $total_credits_earned += $cr_hours;
        $total_gp_weighted += ($grade_info[1] * $cr_hours);

        $subjects[] = [
            'subject_name' => $course['course_title'],
            'subject_code' => $c_code,
            'credit_hours' => $cr_hours,
            'mid_term' => $display_marks['Mid'],
            'final_term' => $display_marks['Final'],
            'sessional' => $display_marks['Sessional'],
            'total' => round($course_weighted_total, 1),
            'grade' => $grade_info[0],
            'gp' => $grade_info[1]
        ];
    }
}

$sgpa = ($total_credits_earned > 0) ? ($total_gp_weighted / $total_credits_earned) : 0;
if ($has_failed_grade) {
    $status_text = "FAILED";
    $status_class = "status-failed";
} elseif ($sgpa < 2.00) {
    $status_text = "PROBATION";
    $status_class = "status-probation";
} else {
    $status_text = "PASSED";
    $status_class = "status-passed";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Semester Result | University of Wah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <link rel="stylesheet" href="assets/results.css">
</head>

<body class="bg-light">

    <div class="main-wrapper d-flex">
        <div class="no-print"><?php include('includes/navbar.php'); ?></div>

        <div class="content-area flex-grow-1">
            <div class="no-print"><?php include('includes/header.php'); ?></div>

            <div class="container-fluid px-4 py-4">
                <div class="result-card shadow-lg border-0">
                    <div class="result-header-banner">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <p class="mb-0 small text-uppercase fw-bold opacity-75" style="letter-spacing:1px;">
                                    Academic Record</p>
                                <h1 class="fw-bold mb-0 text-white">SEMESTER RESULT</h1>
                            </div>
                            <button class="btn btn-light rounded-pill px-4 shadow-sm no-print" onclick="window.print()">
                                <i class="bi bi-printer-fill me-2"></i>Print Result
                            </button>
                        </div>

                        <div class="student-info-grid">
                            <div class="info-box">
                                <label>Full Name</label>
                                <span><?php echo htmlspecialchars($student['full_name'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="info-box">
                                <label>Roll Number</label>
                                <span><?php echo htmlspecialchars($student_roll); ?></span>
                            </div>
                            <div class="info-box">
                                <label>Semester</label>
                                <span><?php echo htmlspecialchars($student['semester'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="info-box">
                                <label>Session</label>
                                <span><?php echo htmlspecialchars($student['session'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-white">
                        <div class="table-responsive">
                            <table class="table table-result align-middle">
                                <thead>
                                    <tr>
                                        <th>Course Description</th>
                                        <th class="text-center">Cr.H</th>
                                        <th class="text-center">Mid (25)</th>
                                        <th class="text-center">Final (45)</th>
                                        <th class="text-center">Sessional (30)</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Grade</th>
                                        <th class="text-center">GPA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($subjects)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">No academic records found
                                                for this semester.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($subjects as $s): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-dark"><?php echo $s['subject_name']; ?></div>
                                                    <div class="text-muted small"><?php echo $s['subject_code']; ?></div>
                                                <td class="text-center"><?php echo $s['credit_hours']; ?></td>
                                                <td class="text-center"><?php echo $s['mid_term']; ?></td>
                                                <td class="text-center"><?php echo $s['final_term']; ?></td>
                                                <td class="text-center"><?php echo $s['sessional']; ?></td>
                                                <td class="text-center fw-bold text-teal"><?php echo $s['total']; ?></td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge rounded-pill <?php echo ($s['grade'] == 'F') ? 'bg-danger' : 'bg-success'; ?> px-3">
                                                        <?php echo $s['grade']; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center fw-bold"><?php echo number_format($s['gp'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="row g-4 mt-2">
                            <div class="col-md-4">
                                <div class="card h-100 border-0 bg-light p-4 text-center rounded-4">
                                    <span class="text-muted small text-uppercase fw-bold">Credit Hours</span>
                                    <h2 class="fw-bold mb-0 mt-2"><?php echo $total_credits_earned; ?></h2>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div
                                    class="card h-100 border-0 bg-white shadow-sm p-4 text-center rounded-4 border-start border-teal border-5">
                                    <span class="text-teal small text-uppercase fw-bold">GPA</span>
                                    <h2 class="fw-bold mb-0 mt-2 text-teal"><?php echo number_format($sgpa, 2); ?></h2>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="status-card h-100 <?php echo $status_class; ?> shadow-sm">
                                    <span class="small text-uppercase fw-bold opacity-75">Academic Status</span>
                                    <h2 class="mt-2"><?php echo $status_text; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-light border-top text-center text-muted small">
                        <i class="bi bi-info-circle-fill me-1"></i> Result is generated based on Mid (25), Final (45),
                        and Sessional (30).
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>