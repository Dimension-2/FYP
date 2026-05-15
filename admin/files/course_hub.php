<?php
include('db_config.php');

$error_message = "";
$success_message = "";

// 1. Handle Add New Course
if (isset($_POST['add_course'])) {
    $code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $title = mysqli_real_escape_string($conn, $_POST['course_title']);
    $semester = intval($_POST['semester']);
    $credit_val = intval($_POST['credit_hours']);

    $is_lab = isset($_POST['is_lab']) ? true : false;

    if ($credit_val < 1 || $credit_val > 4) {
        header("Location: course_hub.php?error=Invalid Credit Hours");
    } else {
        $display_title = $is_lab ? $title . " (Lab)" : $title;
        $credit_string = $is_lab ? "$credit_val(0-$credit_val)" : "$credit_val($credit_val-0)";

        $sql = "INSERT INTO course_assignments (course_code, course_title, credit_hours, semester) 
                VALUES ('$code', '$display_title', '$credit_string', '$semester')";
        mysqli_query($conn, $sql);
        header("Location: course_hub.php?success=Course added successfully!");
        exit();
    }
}

// 2. Handle Course Deletion
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM course_assignments WHERE id = $id");
    header("Location: course_hub.php?deleted=1");
    exit();
}

// 3. Handle Teacher Assignment
if (isset($_POST['assign_teacher'])) {
    $course_id = intval($_POST['course_id']);
    $teacher = mysqli_real_escape_string($conn, $_POST['teacher_name']);

    $check_sql = "SELECT COUNT(*) as total FROM course_assignments WHERE teacher_name = '$teacher'";
    $check_res = mysqli_query($conn, $check_sql);
    $count_row = mysqli_fetch_assoc($check_res);
    $current_count = $count_row['total'];

    if ($current_count >= 4) {
        header("Location: course_hub.php?error=$teacher already has 4 assigned subjects!");
    } else {
        $sql = "UPDATE course_assignments SET teacher_name = '$teacher' WHERE id = $course_id";
        mysqli_query($conn, $sql);
        header("Location: course_hub.php?assigned=1");
        exit();
    }
}

// Data Fetching
$teachers_list = mysqli_query($conn, "SELECT full_name FROM teachers ORDER BY full_name ASC");
$all_courses = mysqli_query($conn, "SELECT * FROM course_assignments ORDER BY course_code ASC");
$courses_by_semester = [];
while ($row = mysqli_fetch_assoc($all_courses)) {
    $courses_by_semester[$row['semester']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management | Admin Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/admin_style.css">

    <style>
        :root {
            --primary: #4f46e5;
            --success: #10b981;
            --bg: #f1f5f9;
            --dark: #1e293b;
            --border: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            margin: 0;
            color: var(--dark);
        }

        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .content-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .container {
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            box-sizing: border-box;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 30px;
            align-items: start;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
            margin-bottom: 20px;
        }

        h2 {
            margin-top: 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 6px;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
            background: #fff;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            margin-bottom: 15px;
            background: #f8fafc;
            padding: 10px;
            border-radius: 8px;
            border: 1px dashed var(--border);
        }

        .checkbox-group input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .semester-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .semester-box {
            background: #fff;
            border-radius: 12px;
            border-top: 4px solid var(--primary);
            padding: 20px;
            border: 1px solid var(--border);
        }

        .semester-title {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
        }

        .course-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .course-info {
            flex: 1;
        }

        .course-info strong {
            display: block;
            font-size: 14px;
        }

        .course-info small {
            color: #64748b;
            font-size: 12px;
            display: block;
            margin-bottom: 4px;
        }

        .assignment-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #ecfdf5;
            color: #065f46;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 5px;
        }

        .unassigned {
            background: #fff1f1;
            color: #991b1b;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 5px;
        }

        .delete-btn {
            color: #cbd5e1;
            padding: 5px;
            cursor: pointer;
        }

        .delete-btn:hover {
            color: #ef4444;
        }
    </style>
</head>

<body>

    <div class="main-wrapper">
        <?php include('sidebar.php'); ?>
        <div class="content-area">
            <?php include('header.php'); ?>
            <div class="container">
                <header style="margin-bottom: 30px;">
                    <h1><i class="fas fa-university" style="color: var(--primary); margin-right: 10px;"></i>Academic Hub
                    </h1>
                </header>

                <div class="admin-grid">
                    <div class="controls">
                        <div class="card">
                            <h2><i class="fas fa-plus-circle"></i> Add Course</h2>
                            <form method="POST">
                                <div class="form-group">
                                    <label>Course Code</label>
                                    <input type="text" name="course_code" placeholder="CS-101" required>
                                </div>
                                <div class="form-group">
                                    <label>Course Title</label>
                                    <input type="text" name="course_title" placeholder="OOP" required>
                                </div>
                                <div class="checkbox-group">
                                    <input type="checkbox" name="is_lab" id="labCheck" onchange="handleLabToggle(this)">
                                    <label for="labCheck" style="margin:0;">Assign as Lab Course</label>
                                </div>
                                <div class="form-group">
                                    <label>Credit Hours</label>
                                    <select name="credit_hours" id="creditSelect" required>
                                        <option value="1">1 Credit Hour</option>
                                        <option value="2">2 Credit Hours</option>
                                        <option value="3" selected>3 Credit Hours</option>
                                        <option value="4">4 Credit Hours</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Semester</label>
                                    <select name="semester">
                                        <?php for ($i = 1; $i <= 8; $i++)
                                            echo "<option value='$i'>Semester $i</option>"; ?>
                                    </select>
                                </div>
                                <button type="submit" name="add_course" class="btn btn-primary">Register Course</button>
                            </form>
                        </div>

                        <div class="card">
                            <h2><i class="fas fa-user-tie"></i> Assign Teacher</h2>
                            <form method="POST">
                                <div class="form-group">
                                    <label>Select Course</label>
                                    <select name="course_id" required>
                                        <option value="" disabled selected>Choose course...</option>
                                        <?php
                                        mysqli_data_seek($all_courses, 0);
                                        while ($c = mysqli_fetch_assoc($all_courses)) {
                                            echo "<option value='" . $c['id'] . "'>[Sem " . $c['semester'] . "] " . $c['course_code'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Select Teacher</label>
                                    <select name="teacher_name" required>
                                        <option value="" disabled selected>Choose teacher...</option>
                                        <?php
                                        mysqli_data_seek($teachers_list, 0);
                                        while ($t = mysqli_fetch_assoc($teachers_list)) {
                                            echo "<option value='" . htmlspecialchars($t['full_name']) . "'>" . htmlspecialchars($t['full_name']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <button type="submit" name="assign_teacher" class="btn btn-success">Assign
                                    Faculty</button>
                            </form>
                        </div>
                    </div>

                    <div class="main-content">
                        <h2 style="margin-bottom: 20px;"><i class="fas fa-layer-group"></i> Semester Curriculum</h2>
                        <div class="semester-grid">
                            <?php for ($s = 1; $s <= 8; $s++): ?>
                                <div class="semester-box">
                                    <div class="semester-title">
                                        <span>Semester <?php echo $s; ?></span>
                                    </div>
                                    <?php if (isset($courses_by_semester[$s])): ?>
                                        <?php foreach ($courses_by_semester[$s] as $course): ?>
                                            <div class="course-item">
                                                <div class="course-info">
                                                    <strong><?php echo htmlspecialchars($course['course_code']); ?></strong>
                                                    <small><?php echo htmlspecialchars($course['course_title']); ?>
                                                        (<?php echo $course['credit_hours']; ?>)</small>
                                                    <?php if (!empty($course['teacher_name'])): ?>
                                                        <span class="assignment-badge"><i class="fas fa-chalkboard-teacher"></i>
                                                            <?php echo htmlspecialchars($course['teacher_name']); ?></span>
                                                    <?php else: ?>
                                                        <span class="unassigned">Unassigned</span>
                                                    <?php endif; ?>
                                                </div>
                                                <a href="#" onclick="confirmDelete(<?php echo $course['id']; ?>)"
                                                    class="delete-btn">
                                                    <i class="fas fa-trash-can"></i>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p style="text-align: center; font-size: 12px; color: #cbd5e1;">Empty</p>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Lab Toggle Logic
        function handleLabToggle(checkbox) {
            const creditSelect = document.getElementById('creditSelect');
            creditSelect.value = checkbox.checked ? "1" : "3";
        }

        // Beautiful Delete Confirmation
        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This course will be permanently removed!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?delete_id=' + id;
                }
            })
        }

        // Handle Status Popups from PHP
        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.has('success')) {
            Swal.fire({ icon: 'success', title: 'Success!', text: urlParams.get('success'), timer: 2500, showConfirmButton: false });
        }

        if (urlParams.has('error')) {
            Swal.fire({ icon: 'error', title: 'Limit Reached', text: urlParams.get('error') });
        }

        if (urlParams.has('deleted')) {
            Swal.fire({ icon: 'info', title: 'Deleted', text: 'Course has been removed.', timer: 2000, showConfirmButton: false });
        }

        if (urlParams.has('assigned')) {
            Swal.fire({ icon: 'success', title: 'Assigned', text: 'Teacher assigned successfully!', timer: 2000, showConfirmButton: false });
        }

        // Clean URL to prevent popup on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.pathname);
        }
    </script>
</body>

</html>