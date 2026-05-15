<?php
session_start();
// Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "fyp";
$conn = new mysqli($host, $user, $pass, $db);

// Assuming teacher session is stored as 'teacher_name' during login
if (!isset($_SESSION['teacher_name'])) {
    // For testing, manually set a teacher name if you haven't built the login yet:
    // $_SESSION['teacher_name'] = 'Mr. X'; 
    die("Access Denied. Teacher not logged in.");
}

$teacher_name = $_SESSION['teacher_name'];
$message = "";

// --- PART 1: HANDLE ATTENDANCE SUBMISSION ---
if (isset($_POST['submit_attendance'])) {
    $course_code = $_POST['course_code'];
    $date = $_POST['date'];
    $topic = $_POST['topic'];
    $lecture_no = $_POST['lecture_no'];
    $room = $_POST['lecture_room'];
    $attendance_data = $_POST['status']; // Array of registration_no => status

    foreach ($attendance_data as $reg_no => $status) {
        $stmt = $conn->prepare("INSERT INTO attendance_logs (course_code, date, status, topic, lecture_no, lecture_room, registration_no) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $course_code, $date, $status, $topic, $lecture_no, $room, $reg_no);
        $stmt->execute();
    }
    $message = "<div class='alert alert-success'>Attendance marked successfully for " . count($attendance_data) . " students!</div>";
}

// --- PART 2: FETCH TEACHER'S ASSIGNED COURSES ---
$course_query = "SELECT * FROM course_assignments WHERE teacher_name = '$teacher_name'";
$course_result = $conn->query($course_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Teacher Panel - Mark Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: #f4f7f6;
        }

        .mark-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-present {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .btn-absent {
            background-color: #f8d7da;
            color: #842029;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <?php echo $message; ?>

                <div class="card mark-card p-4 mb-4">
                    <h3 class="fw-bold text-primary mb-4"><i class="bi bi-person-workspace"></i> Teacher Panel: Mark
                        Attendance</h3>

                    <form method="GET" class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Select Your Assigned Course</label>
                            <select name="selected_course" class="form-select form-select-lg"
                                onchange="this.form.submit()">
                                <option value="">-- Choose Course --</option>
                                <?php while ($course = $course_result->fetch_assoc()): ?>
                                    <option value="<?php echo $course['course_code']; ?>" <?php echo (isset($_GET['selected_course']) && $_GET['selected_course'] == $course['course_code']) ? 'selected' : ''; ?>>
                                        <?php echo $course['course_code'] . " - " . $course['course_title']; ?> (Semester
                                        <?php echo $course['semester']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </form>
                </div>

                <?php
                if (isset($_GET['selected_course'])):
                    $c_code = $_GET['selected_course'];
                    // Get course details to find the correct semester
                    $det = $conn->query("SELECT semester FROM course_assignments WHERE course_code = '$c_code'")->fetch_assoc();
                    $sem = $det['semester'];

                    // Fetch students enrolled in this semester
                    $students = $conn->query("SELECT registration_no FROM profile WHERE semester = '$sem'");
                    ?>

                    <form method="POST">
                        <input type="hidden" name="course_code" value="<?php echo $c_code; ?>">

                        <div class="card mark-card p-4 mb-4 bg-white">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>"
                                        required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Lecture No</label>
                                    <input type="number" name="lecture_no" class="form-control" placeholder="e.g. 1"
                                        required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Lecture Room</label>
                                    <input type="text" name="lecture_room" class="form-control" placeholder="e.g. Lab 01"
                                        required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Topic</label>
                                    <input type="text" name="topic" class="form-control" placeholder="e.g. Intro to PHP"
                                        required>
                                </div>
                            </div>
                        </div>

                        <div class="card mark-card overflow-hidden">
                            <table class="table table-hover m-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Registration No</th>
                                        <th class="text-center">Status (Present / Absent)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($st = $students->fetch_assoc()): ?>
                                        <tr>
                                            <td class="fw-bold align-middle"><?php echo $st['registration_no']; ?></td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <input type="radio" class="btn-check"
                                                        name="status[<?php echo $st['registration_no']; ?>]"
                                                        id="p_<?php echo $st['registration_no']; ?>" value="Present" checked>
                                                    <label class="btn btn-outline-success px-4"
                                                        for="p_<?php echo $st['registration_no']; ?>">P</label>

                                                    <input type="radio" class="btn-check"
                                                        name="status[<?php echo $st['registration_no']; ?>]"
                                                        id="a_<?php echo $st['registration_no']; ?>" value="Absent">
                                                    <label class="btn btn-outline-danger px-4"
                                                        for="a_<?php echo $st['registration_no']; ?>">A</label>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <div class="p-3 bg-light text-end">
                                <button type="submit" name="submit_attendance" class="btn btn-primary btn-lg shadow">
                                    <i class="bi bi-cloud-upload"></i> Save Attendance Logs
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>

</html>