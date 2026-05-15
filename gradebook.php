<?php
session_start();
// Database connection
$conn = new mysqli("localhost", "root", "", "fyp");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Authentication Check
if (!isset($_SESSION['registration_no'])) {
    header("Location: login.php");
    exit();
}

$reg_no = $_SESSION['registration_no'];

// Fetch Student Profile
$student_stmt = $conn->prepare("SELECT * FROM profile WHERE registration_no = ?");
$student_stmt->bind_param("s", $reg_no);
$student_stmt->execute();
$student = $student_stmt->get_result()->fetch_assoc();
$semester = $student['semester'] ?? 1;

// Configuration Weights
$weights = ['Sessional' => 0.25, 'Mid' => 0.30, 'Final' => 0.45];

function getStatusColor($score)
{
    if ($score >= 80)
        return '#059669';
    if ($score >= 60)
        return '#2563eb';
    if ($score >= 50)
        return '#d97706';
    return '#dc2626';
}

// Fetch Courses
$course_query = "SELECT * FROM course_assignments WHERE semester = '$semester'";
$course_res = $conn->query($course_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Insights | Student Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">

    <style>
        :root {
            --glass: rgba(255, 255, 255, 0.95);
            --primary: #4f46e5;
        }

        body {
            background: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
            margin: 0;
        }

        .main-wrapper {
            display: flex;
            width: 100%;
        }

        .content-area {
            flex: 1;
            padding: 30px;
            min-height: 100vh;
        }

        .glass-card {
            background: var(--glass);
            border-radius: 16px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            padding: 20px;
        }

        /* Fixed Column widths for neatness */
        .table thead th {
            border: none;
            background: #f1f5f9;
            color: #64748b;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        .course-row {
            border-bottom: 1px solid #f1f5f9;
        }

        /* New Individual Entry Styling */
        .entry-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }

        .entry-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 4px 8px;
            min-width: 70px;
        }

        .entry-label {
            font-size: 10px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .entry-marks {
            font-weight: 700;
            font-size: 13px;
            color: #1e293b;
        }

        /* Colors for different categories */
        .sessional-box {
            border-left: 3px solid #10b981;
        }

        .mid-box {
            border-left: 3px solid #f59e0b;
        }

        .final-box {
            border-left: 3px solid #ef4444;
        }

        .progress-container {
            width: 100px;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin-left: auto;
        }

        .progress-bar {
            height: 100%;
            transition: width 0.5s;
        }
    </style>
</head>

<body>
    <div class="main-wrapper">

        <?php if (file_exists('includes/navbar.php'))
            include('includes/navbar.php'); ?>
        <div class="content-area">
            <?php if (file_exists('includes/header.php'))
                include('includes/header.php'); ?>

            <div class="container-fluid mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold text-dark mb-1">Academic Performance</h2>
                        <p class="text-muted small">Semester <?= htmlspecialchars($semester) ?> Detailed Breakdown</p>
                    </div>
                </div>

                <div class="glass-card p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr class="text-center">
                                    <th class="text-start py-3 ps-4" style="width: 25%;">COURSE DETAILS</th>
                                    <th style="width: 30%;">SESSIONAL ENTRIES (25%)</th>
                                    <th style="width: 15%;">MIDTERM (30%)</th>
                                    <th style="width: 15%;">FINAL (45%)</th>
                                    <th class="text-end pe-4">OVERALL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($course_res && $course_res->num_rows > 0): ?>
                                    <?php while ($course = $course_res->fetch_assoc()):
                                        $c_code = $course['course_code'];
                                        $course_weighted_total = 0;
                                        ?>
                                        <tr class="course-row">
                                            <td class="ps-4 py-4">
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($course['course_title']) ?>
                                                </div>
                                                <div class="small text-muted"><?= htmlspecialchars($c_code) ?></div>
                                            </td>

                                            <?php
                                            $categories = ['Sessional', 'Mid', 'Final'];
                                            foreach ($categories as $cat):
                                                $cat_ob = 0;
                                                $cat_tot = 0;

                                                // Handle Sessional logic (Quizzes/Assignments) vs Exams
                                                if ($cat === 'Sessional') {
                                                    $item_stmt = $conn->prepare("SELECT id, item_label, total_marks FROM grading_items WHERE course_code = ? AND (item_type = 'Sessional' OR item_type IS NULL OR item_type = '')");
                                                    $item_stmt->bind_param("s", $c_code);
                                                } else {
                                                    $item_stmt = $conn->prepare("SELECT id, item_label, total_marks FROM grading_items WHERE course_code = ? AND item_type = ?");
                                                    $item_stmt->bind_param("ss", $c_code, $cat);
                                                }

                                                $item_stmt->execute();
                                                $items = $item_stmt->get_result();
                                                ?>
                                                <td class="text-center">
                                                    <div class="entry-container">
                                                        <?php if ($items->num_rows > 0): ?>
                                                            <?php while ($item = $items->fetch_assoc()):
                                                                $item_id = $item['id'];
                                                                $mark_stmt = $conn->prepare("SELECT obtained_marks FROM student_marks WHERE registration_no = ? AND item_id = ?");
                                                                $mark_stmt->bind_param("si", $reg_no, $item_id);
                                                                $mark_stmt->execute();
                                                                $mark_res = $mark_stmt->get_result()->fetch_assoc();

                                                                $obtained = $mark_res['obtained_marks'] ?? 0;
                                                                $cat_ob += $obtained;
                                                                $cat_tot += $item['total_marks'];

                                                                // Dynamic Class
                                                                $box_class = strtolower($cat) . "-box";
                                                                ?>
                                                                <div class="entry-box <?= $box_class ?>">
                                                                    <span
                                                                        class="entry-label"><?= htmlspecialchars($item['item_label']) ?></span>
                                                                    <span
                                                                        class="entry-marks"><?= $obtained ?>/<?= $item['total_marks'] ?></span>
                                                                </div>
                                                            <?php endwhile; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted opacity-25">--</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <?php
                                                $perc = ($cat_tot > 0) ? ($cat_ob / $cat_tot) * 100 : 0;
                                                $course_weighted_total += ($perc * $weights[$cat]);
                                            endforeach; ?>

                                            <td class="text-end pe-4">
                                                <div class="fw-bold h5 mb-0"
                                                    style="color: <?= getStatusColor($course_weighted_total) ?>">
                                                    <?= round($course_weighted_total, 1) ?>%
                                                </div>
                                                <div class="progress-container">
                                                    <div class="progress-bar"
                                                        style="width: <?= $course_weighted_total ?>%; background: <?= getStatusColor($course_weighted_total) ?>">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5">No academic data found.</td>
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
</body>

</html>