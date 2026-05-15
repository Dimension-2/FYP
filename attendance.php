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
        :root {
            --sidebar-width: 260px;
            --header-height: 70px;
        }

        body {
            background: #f0f2f5;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Sidebar: Fixed to left */
        .sidebar-container {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            background: #1e1e2d;
            /* Adjust color to match your sidebar theme */
            overflow-y: auto;
        }

        /* Content Wrapper: Pushed to the right of sidebar */
        .content-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header: Top of the content area */
        .header-container {
            width: 100%;
            height: var(--header-height);
            background: white;
            padding: 0 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .main-content-area {
            padding: 30px;
            flex-grow: 1;
        }

        .content-card {
            border-radius: 15px;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 30px;
            border: none;
        }

        .progress {
            border-radius: 10px;
            background-color: #e9ecef;
            height: 8px;
        }

        .icon-box {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 992px) {
            .sidebar-container {
                display: none;
            }

            .content-wrapper {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="sidebar-container">
        <?php include 'includes/navbar.php'; ?>
    </div>

    <div class="content-wrapper">

        <div class="header-container">
            <?php include 'includes/header.php'; ?>
        </div>

        <div class="main-content-area">
            <div class="container-fluid">
                <div class="content-card">

                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box p-2 rounded-3 me-3" style="background: #000000;">
                            <i class="bi bi-calendar-check fs-3" style="color: #10b981;"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold m-0">Attendance Overview</h4>
                            <small class="text-muted">Registration No:
                                <?php echo htmlspecialchars($registration_no); ?></small>
                        </div>
                    </div>

                    <div class="alert alert-primary border-0 shadow-sm mb-4"
                        style="background-color: #f0f7ff; color: #004085;">
                        <i class="bi bi-info-circle me-2"></i><strong>Semester <?php echo $current_semester; ?></strong>
                        - Active Courses
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Course & Instructor</th>
                                    <th class="text-center">CR Hours</th>
                                    <th class="text-center">Attendance</th>
                                    <th>Progress</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Action</th>
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
                                            $color = "success";
                                            $label = "Satisfactory";
                                            $icon = "bi-check-circle-fill";
                                        } elseif ($percentage >= 65) {
                                            $color = "warning";
                                            $label = "Low";
                                            $icon = "bi-exclamation-triangle-fill";
                                        } else {
                                            $color = "danger";
                                            $label = "Shortage";
                                            $icon = "bi-x-octagon-fill";
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">
                                                    <?php echo htmlspecialchars($row['course_code']); ?></div>
                                                <div class="text-muted small">
                                                    <?php echo htmlspecialchars($row['course_title']); ?></div>
                                                <div class="text-primary mt-1" style="font-size: 0.8rem;">
                                                    <i class="bi bi-person-badge"></i>
                                                    <?php echo htmlspecialchars($row['teacher_name'] ?? 'Not Assigned'); ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-light text-dark border"><?php echo $row['credit_hours']; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold"><?php echo $attended; ?></span> / <?php echo $total; ?>
                                            </td>
                                            <td style="min-width: 150px;">
                                                <small
                                                    class="fw-bold text-<?php echo $color; ?>"><?php echo $percentage; ?>%</small>
                                                <div class="progress mt-1">
                                                    <div class="progress-bar bg-<?php echo $color; ?>"
                                                        style="width: <?php echo $percentage; ?>%"></div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-<?php echo $color; ?>-subtle text-<?php echo $color; ?> border px-2 py-1">
                                                    <i class="bi <?php echo $icon; ?>"></i> <?php echo $label; ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="attendance_details.php?code=<?php echo urlencode($row['course_code']); ?>"
                                                    class="btn btn-sm btn-outline-secondary rounded-pill">
                                                    <i class="bi bi-clock-history"></i> History
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">No courses found for your
                                            semester.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $conn->close(); ?>
</body>

</html>