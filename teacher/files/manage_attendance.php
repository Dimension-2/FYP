<?php
session_start();

// 1. Database Connection & Session Check
$host = "localhost";
$user = "root";
$pass = "";
$db = "fyp";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['teacher_id'])) {
    header("Location: ../../login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// 2. Fetch Teacher's Name (Using $data to match your header.php requirements)
$t_query = $conn->prepare("SELECT * FROM teachers WHERE employee_id = ?");
$t_query->bind_param("s", $teacher_id);
$t_query->execute();
$t_result = $t_query->get_result();
$data = $t_result->fetch_assoc(); // Changed from $t_data to $data
$teacher_name = $data['full_name'];
$message = "";

// 3. LOGIC: DELETE ENTIRE CLASS SESSION
if (isset($_GET['delete_session'])) {
    $date = $_GET['date'];
    $lec = $_GET['lec'];
    $course = $_GET['course_code'];
    $conn->query("DELETE FROM attendance_logs WHERE date = '$date' AND lecture_no = '$lec' AND course_code = '$course'");
    $message = "<div class='alert alert-danger border-0 shadow-sm'>Full class session deleted.</div>";
}

// 4. LOGIC: UPDATE SINGLE STUDENT STATUS (EDIT)
if (isset($_GET['update_log_id'])) {
    $log_id = $_GET['update_log_id'];
    $new_status = $_GET['new_status'];
    $conn->query("UPDATE attendance_logs SET status = '$new_status' WHERE log_id = '$log_id'");
    header("Location: manage_attendance.php?course_code=" . $_GET['course_code'] . "&tab=history");
    exit();
}

// 5. LOGIC: MARK NEW ATTENDANCE
if (isset($_POST['submit_attendance'])) {
    $course_code = $_POST['course_code'];
    $date = $_POST['date'];
    $topic = $_POST['topic'];
    $lec_no = $_POST['lecture_no'];
    $room = $_POST['lecture_room'];
    $statuses = $_POST['status'];

    foreach ($statuses as $reg_no => $status) {
        $stmt = $conn->prepare("INSERT INTO attendance_logs (course_code, date, status, topic, lecture_no, lecture_room, registration_no) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $course_code, $date, $status, $topic, $lec_no, $room, $reg_no);
        $stmt->execute();
    }
    $message = "<div class='alert alert-success border-0 shadow-sm'><i class='fas fa-check-circle me-2'></i>Attendance successfully recorded!</div>";
}

$assigned_courses = $conn->query("SELECT * FROM course_assignments WHERE teacher_name = '$teacher_name'");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Hub | <?php echo $teacher_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/profile_style.css">
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --danger: #e74a3b;
        }

        body {
            background: #f0f2f5;
            font-family: 'Poppins', sans-serif;
        }

        #page-content-wrapper {
            width: 100%;
            transition: all 0.3s;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
        }

        .course-picker {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border-radius: 20px;
        }

        .nav-pills .nav-link {
            color: #5a5c69;
            border-radius: 10px;
            padding: 12px 25px;
            transition: 0.3s;
        }

        .nav-pills .nav-link.active {
            background: var(--primary);
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.3);
        }

        .attendance-row:hover {
            background-color: #f8f9fc;
            transform: scale(1.01);
            transition: 0.2s;
        }

        .status-pill {
            padding: 5px 15px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .session-group {
            border-left: 5px solid var(--primary);
            margin-bottom: 30px;
        }
    </style>
</head>

<body>

    <div class="d-flex" id="wrapper">
        <?php include('../Bars/sidebar.php'); ?>

        <div id="page-content-wrapper">
            <?php include('../Bars/header.php'); ?>

            <div class="container-fluid p-4">
                <?php echo $message; ?>

                <div class="card course-picker p-4 mb-4 shadow border-0">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="fw-bold mb-1"><i class="fas fa-graduation-cap me-2"></i>Academic Management</h3>
                            <p class="mb-0 opacity-75">Select a course to mark attendance or manage records</p>
                        </div>
                        <div class="col-md-4">
                            <form method="GET">
                                <select name="course_code" class="form-select border-0 shadow-sm"
                                    onchange="this.form.submit()" style="height: 50px; border-radius: 12px;">
                                    <option value="">Choose Course...</option>
                                    <?php while ($c = $assigned_courses->fetch_assoc()): ?>
                                        <option value="<?php echo $c['course_code']; ?>" <?php echo (isset($_GET['course_code']) && $_GET['course_code'] == $c['course_code']) ? 'selected' : ''; ?>>
                                            <?php echo $c['course_code']; ?> | <?php echo $c['course_title']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if (isset($_GET['course_code'])):
                    $selected_code = $_GET['course_code'];
                    $sem_lookup = $conn->query("SELECT semester FROM course_assignments WHERE course_code = '$selected_code'")->fetch_assoc();
                    $target_semester = $sem_lookup['semester'];
                    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'mark';
                    ?>

                    <ul class="nav nav-pills mb-4 justify-content-center" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link <?php echo ($active_tab == 'mark') ? 'active' : ''; ?> fw-bold"
                                data-bs-toggle="pill" data-bs-target="#markNew"><i class="fas fa-edit me-2"></i>Mark
                                Attendance</button>
                        </li>
                        <li class="nav-item ms-3">
                            <button class="nav-link <?php echo ($active_tab == 'history') ? 'active' : ''; ?> fw-bold"
                                data-bs-toggle="pill" data-bs-target="#viewHistory"><i
                                    class="fas fa-history me-2"></i>Session Logs & History</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade <?php echo ($active_tab == 'mark') ? 'show active' : ''; ?>" id="markNew">
                            <form method="POST">
                                <input type="hidden" name="course_code" value="<?php echo $selected_code; ?>">
                                <div class="card glass-card p-4 mb-4">
                                    <div class="row g-3">
                                        <div class="col-md-3"><label class="small text-muted fw-bold">LECTURE
                                                DATE</label><input type="date" name="date" class="form-control"
                                                value="<?php echo date('Y-m-d'); ?>" required></div>
                                        <div class="col-md-2"><label class="small text-muted fw-bold">LECTURE
                                                #</label><input type="number" name="lecture_no" class="form-control"
                                                placeholder="Ex: 1" required></div>
                                        <div class="col-md-3"><label
                                                class="small text-muted fw-bold">VENUE/ROOM</label><input type="text"
                                                name="lecture_room" class="form-control" placeholder="Hall A" required>
                                        </div>
                                        <div class="col-md-4"><label class="small text-muted fw-bold">TOPIC
                                                DESCRIPTION</label><input type="text" name="topic" class="form-control"
                                                placeholder="What was taught today?" required></div>
                                    </div>
                                </div>

                                <div class="card glass-card overflow-hidden">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4 py-3">Student Registration</th>
                                                <th class="text-center py-3">Status Configuration</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $students = $conn->query("SELECT registration_no FROM profile WHERE semester = '$target_semester'");
                                            while ($st = $students->fetch_assoc()): ?>
                                                <tr class="attendance-row">
                                                    <td class="ps-4 fw-bold text-dark"><?php echo $st['registration_no']; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group" role="group">
                                                            <input type="radio" class="btn-check"
                                                                name="status[<?php echo $st['registration_no']; ?>]"
                                                                id="p_<?php echo $st['registration_no']; ?>" value="Present"
                                                                checked>
                                                            <label class="btn btn-outline-success rounded-start-pill px-4"
                                                                for="p_<?php echo $st['registration_no']; ?>">Present</label>
                                                            <input type="radio" class="btn-check"
                                                                name="status[<?php echo $st['registration_no']; ?>]"
                                                                id="a_<?php echo $st['registration_no']; ?>" value="Absent">
                                                            <label class="btn btn-outline-danger rounded-end-pill px-4"
                                                                for="a_<?php echo $st['registration_no']; ?>">Absent</label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                    <div class="card-footer bg-white p-4 text-center">
                                        <button type="submit" name="submit_attendance"
                                            class="btn btn-primary btn-lg px-5 shadow-sm rounded-pill">Finalize & Save
                                            Records</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade <?php echo ($active_tab == 'history') ? 'show active' : ''; ?>"
                            id="viewHistory">
                            <?php
                            // Get unique class sessions
                            $sessions = $conn->query("SELECT DISTINCT date, lecture_no, topic, lecture_room FROM attendance_logs WHERE course_code = '$selected_code' ORDER BY date DESC, lecture_no DESC");

                            if ($sessions->num_rows > 0):
                                while ($s = $sessions->fetch_assoc()):
                                    $s_date = $s['date'];
                                    $s_lec = $s['lecture_no'];
                                    ?>
                                    <div class="card glass-card mb-4 session-group">
                                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                            <div>
                                                <span class="badge bg-primary me-2">Lec #<?php echo $s_lec; ?></span>
                                                <strong class="text-dark"><?php echo date('M d, Y', strtotime($s_date)); ?></strong>
                                                <span class="text-muted ms-3"><i class="fas fa-book-open me-1"></i>
                                                    <?php echo $s['topic']; ?></span>
                                                <span class="text-muted ms-3"><i class="fas fa-map-marker-alt me-1"></i>
                                                    <?php echo $s['lecture_room']; ?></span>
                                            </div>
                                            <a href="manage_attendance.php?course_code=<?php echo $selected_code; ?>&delete_session=true&date=<?php echo $s_date; ?>&lec=<?php echo $s_lec; ?>"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Delete entire class record?')">
                                                <i class="fas fa-trash-alt me-1"></i> Delete Session
                                            </a>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="small text-uppercase bg-light">
                                                    <tr>
                                                        <th class="ps-4">Registration No</th>
                                                        <th>Status</th>
                                                        <th class="text-end pe-4">Toggle Status (Edit)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $logs = $conn->query("SELECT * FROM attendance_logs WHERE course_code = '$selected_code' AND date = '$s_date' AND lecture_no = '$s_lec'");
                                                    while ($l = $logs->fetch_assoc()): ?>
                                                        <tr>
                                                            <td class="ps-4"><?php echo $l['registration_no']; ?></td>
                                                            <td>
                                                                <span
                                                                    class="status-pill <?php echo ($l['status'] == 'Present') ? 'bg-success text-white' : 'bg-danger text-white'; ?>">
                                                                    <?php echo $l['status']; ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-end pe-4">
                                                                <?php if ($l['status'] == 'Present'): ?>
                                                                    <a href="manage_attendance.php?course_code=<?php echo $selected_code; ?>&update_log_id=<?php echo $l['log_id']; ?>&new_status=Absent"
                                                                        class="btn btn-link btn-sm text-danger text-decoration-none">Mark
                                                                        Absent</a>
                                                                <?php else: ?>
                                                                    <a href="manage_attendance.php?course_code=<?php echo $selected_code; ?>&update_log_id=<?php echo $l['log_id']; ?>&new_status=Present"
                                                                        class="btn btn-link btn-sm text-success text-decoration-none">Mark
                                                                        Present</a>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endwhile;
                            else: ?>
                                <div class="text-center p-5 card glass-card">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No attendance logs found for this course.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>