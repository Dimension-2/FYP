<?php
session_start();
// Authentication check could go here

$page_title = "Admin - Faculty Feedback Records";
$current_page = "admin_feedback_view.php";

// --- DATABASE CONNECTION ---
$host = "localhost";
$user = "root";
$pass = "";
$db = "fyp";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/** * FETCH DATA: 
 * We fetch the course_code and course_title by joining faculty_feedback 
 * with the course_assignments table to ensure the names show up correctly.
 */
$sql = "SELECT f.*, c.course_title AS assigned_title 
        FROM faculty_feedback f
        LEFT JOIN course_assignments c ON f.course_code = c.course_code 
        ORDER BY f.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --teal-color: #00cba9;
        }

        body {
            background-color: #f4f7f6;
        }

        .main-wrapper {
            display: flex;
            width: 100%;
        }

        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            background: #2c3e50;
        }

        .content-area {
            flex-grow: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width));
        }

        .table-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .thead-teal {
            background-color: var(--teal-color);
            color: white;
        }

        .score-badge {
            font-weight: bold;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
    </style>
</head>
 <link rel="stylesheet" href="../css/admin_style.css">
   
<body>

    <div class="main-wrapper">
        <div id="sidebar">
            <?php include('sidebar.php'); ?>
        </div>

        <div class="content-area">
            <?php include('header.php'); ?>

            <div class="container-fluid px-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold text-dark">Faculty Evaluation Records</h3>
                    <button class="btn btn-outline-success btn-sm" onclick="window.print()">
                        <i class="bi bi-printer me-2"></i> Print Report
                    </button>
                </div>

                <div class="card table-card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="thead-teal">
                                    <tr>
                                        <th class="ps-4">Student Reg #</th>
                                        <th>Course Details</th>
                                        <th>Avg Score</th>
                                        <th>Comments</th>
                                        <th>Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <?php
                                            // Handle JSON scores
                                            $scores = json_decode($row['scores'], true);
                                            $avg = 0;
                                            if (is_array($scores) && count($scores) > 0) {
                                                $avg = array_sum($scores) / count($scores);
                                            }
                                            $badgeClass = ($avg >= 4) ? 'bg-success' : (($avg >= 3) ? 'bg-warning text-dark' : 'bg-danger text-white');

                                            // Logic to show course name even if feedback table entry is empty
                                            $displayTitle = !empty($row['course_title']) ? $row['course_title'] : $row['assigned_title'];
                                            ?>
                                            <tr>
                                                <td class="ps-4 fw-bold">
                                                    <?php echo htmlspecialchars($row['registration_no']); ?></td>
                                                <td>
                                                    <span
                                                        class="fw-bold text-primary"><?php echo htmlspecialchars($row['course_code']); ?></span><br>
                                                    <small
                                                        class="text-muted"><?php echo htmlspecialchars($displayTitle); ?></small>
                                                </td>
                                                <td>
                                                    <span class="score-badge <?php echo $badgeClass; ?>">
                                                        <?php echo number_format($avg, 1); ?> / 5.0
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo !empty($row['comments']) ? substr(htmlspecialchars($row['comments']), 0, 40) . '...' : 'No comments'; ?>
                                                    </small>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($row['submission_date'])); ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-light border" title="View Details">
                                                        <i class="bi bi-eye text-info"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">No evaluation records found.
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>