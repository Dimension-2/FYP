<?php
session_start();
$current_page = 'faculty.php';
$page_title = 'Faculty Feedback';

// 1. Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "fyp";

$conn = new mysqli($host, $user, $pass, $db);

// Authentication Check
if (!isset($_SESSION['registration_no'])) {
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$reg_no = $_SESSION['registration_no'];

// 2. Fetch completed evaluations for this student
$completed_query = "SELECT course_code FROM faculty_feedback WHERE registration_no = '$reg_no'";
$completed_result = $conn->query($completed_query);
$completed_courses = [];

if ($completed_result && $completed_result->num_rows > 0) {
    while ($row = $completed_result->fetch_assoc()) {
        $completed_courses[] = $row['course_code'];
    }
}

// 3. Fetch Semester from 'profile' table
$profile_query = "SELECT semester FROM profile WHERE registration_no = '$reg_no'";
$profile_res = $conn->query($profile_query);
$student_semester = ($profile_res && $profile_res->num_rows > 0) ? $profile_res->fetch_assoc()['semester'] : 0;

// 4. Fetch courses assigned along with lock status
$courses_query = "SELECT ca.course_code, ca.course_title, ca.teacher_name, ca.credit_hours, IFNULL(tfl.is_locked, 0) as is_locked 
                  FROM course_assignments ca
                  LEFT JOIN teacher_feedback_locks tfl ON ca.teacher_name = tfl.teacher_name
                  WHERE ca.semester = '$student_semester'";
$courses_result = $conn->query($courses_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">

    <style>
        .text-teal {
            color: #00cba9;
        }

        .btn-evaluate {
            background-color: #00cba9;
            color: white;
            border-radius: 20px;
            padding: 6px 22px;
            transition: 0.3s;
            text-decoration: none;
            border: none;
            display: inline-block;
        }

        .btn-evaluate:hover {
            background-color: #00a88d;
            color: white;
            transform: scale(1.05);
        }

        .status-badge {
            font-size: 0.85rem;
            padding: 5px 12px;
            border-radius: 12px;
        }

        /* Ensure content doesn't hide under fixed sidebars */
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .content-container {
            flex-grow: 1;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body class="bg-light">

    <div class="main-wrapper">
        <?php include 'includes/navbar.php'; ?>

        <div class="content-container">
            <?php include 'includes/header.php'; ?>

            <div class="p-4">
                <div class="content-card bg-white shadow-sm p-4" style="border-radius: 15px;">
                    <div class="header-section d-flex align-items-center gap-3 mb-4">
                        <i class="bi bi-chat-left-dots-fill fs-2 text-teal"></i>
                        <div>
                            <h4 class="fw-bold m-0">Faculty Feedback</h4>
                            <p class="text-muted m-0">Assigned Courses for Semester
                                <?php echo htmlspecialchars($student_semester); ?>
                            </p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Course Title</th>
                                    <th>Instructor</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($courses_result && $courses_result->num_rows > 0): ?>
                                    <?php while ($c = $courses_result->fetch_assoc()):
                                        $is_done = in_array($c['course_code'], $completed_courses);
                                        $instructor = !empty($c['teacher_name']) ? $c['teacher_name'] : "Not Assigned";
                                        ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?php echo htmlspecialchars($c['course_code']); ?>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($c['course_title']); ?>
                                                </div>
                                                <small class="text-muted">Credits:
                                                    <?php echo htmlspecialchars($c['credit_hours']); ?></small>
                                            </td>
                                            <td>
                                                <span class="text-secondary">
                                                    <i
                                                        class="bi bi-person-badge me-1"></i><?php echo htmlspecialchars($instructor); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($is_done): ?>
                                                    <span class="badge bg-success-subtle text-success status-badge">Completed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-dark status-badge">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($is_done): ?>
                                                    <span class="text-success small fw-bold"><i class="bi bi-check-circle-fill"></i>
                                                        Done</span>
                                                <?php elseif ($instructor == "Not Assigned"): ?>
                                                    <span class="text-muted small italic">Unavailable</span>
                                                <?php elseif ($c['is_locked'] == 1): ?>
                                                    <span class="badge bg-danger">Locked by Admin</span>
                                                <?php else: ?>
                                                    <a href="evaluate.php?course_code=<?php echo urlencode($c['course_code']); ?>&course_title=<?php echo urlencode($c['course_title']); ?>&instructor=<?php echo urlencode($instructor); ?>"
                                                        class="btn btn-evaluate">Start Evaluation</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            No courses found for Semester
                                            <?php echo htmlspecialchars($student_semester); ?>.
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/dist/js/bootstrap.bundle.min.js"></script>
    <?php $conn->close(); ?>
</body>

</html>