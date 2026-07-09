<?php
include('db_config.php');

$error_message = "";
$success_message = "";

// 1. Handle Add New Course
if (isset($_POST['add_course'])) {
    $dept = mysqli_real_escape_string($conn, $_POST['department']);
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

        $sql = "INSERT INTO course_assignments (department, course_code, course_title, credit_hours, semester) 
                VALUES ('$dept', '$code', '$display_title', '$credit_string', '$semester')";
        mysqli_query($conn, $sql);
        header("Location: course_hub.php?success=Course registered in $dept successfully!");
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

    // Check maximum 4 subjects rule
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

// --- DATA FETCHING & HIERARCHY BUILDING ---
$teachers_list = mysqli_query($conn, "SELECT full_name, department FROM teachers ORDER BY department ASC, full_name ASC");
$all_courses = mysqli_query($conn, "SELECT * FROM course_assignments ORDER BY department ASC, semester ASC, course_code ASC");

// Pre-initialize Departments so folders always show up
$allowed_departments = [
    'Computer Science',
    'Artificial Intelligence',
    'Cyber Security',
    'Data Science',
    'Physics',
    'Mathematics',
    'Psychology',
    'English'
];

$hierarchy = [];
foreach ($allowed_departments as $dept) {
    $hierarchy[$dept] = ['total_courses' => 0, 'semesters' => []];
    // Pre-initialize exactly 8 semesters for every department
    for ($i = 1; $i <= 8; $i++) {
        $hierarchy[$dept]['semesters'][$i] = [];
    }
}

while ($row = mysqli_fetch_assoc($all_courses)) {
    // Map abbreviation to full name if needed, or use as is
    $dept = $row['department'] ? trim($row['department']) : 'Computer Science';
    $sem = (int) $row['semester'];

    // Fallback if data contains unknown department
    if (!isset($hierarchy[$dept])) {
        $hierarchy[$dept] = ['total_courses' => 0, 'semesters' => []];
        for ($i = 1; $i <= 8; $i++)
            $hierarchy[$dept]['semesters'][$i] = [];
    }

    if ($sem >= 1 && $sem <= 8) {
        $hierarchy[$dept]['semesters'][$sem][] = $row;
        $hierarchy[$dept]['total_courses']++;
    }
}

$json_data = json_encode($hierarchy);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curriculum Hub | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/admin_style.css">

    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-surface: #ffffff;
            --border-color: #e2e8f0;
            --border-hover: #cbd5e1;
            
            --text-main: #0f172a;
            --text-muted: #64748b;
            
            --primary-solid: #2563eb;
            --primary-hover: #1d4ed8;
            --secondary-solid: #0f172a;
            
            --danger-solid: #ef4444;
            --danger-hover: #dc2626;

            --radius-md: 8px;
            --radius-lg: 12px;
            --shadow-sm: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-body); margin: 0; color: var(--text-main); }
        .main-wrapper { display: flex; min-height: 100vh; }
        .content-area { flex: 1; display: flex; flex-direction: column; overflow-x: hidden; }
        .container { padding: 40px; max-width: 1600px; margin: 0 auto; width: 100%; box-sizing: border-box; }

        header h1 { font-size: 1.75rem; font-weight: 700; display: flex; align-items: center; gap: 12px; margin-bottom: 5px; }
        
        /* Breadcrumbs & Navigation */
        .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
        .breadcrumb-container { display: flex; align-items: center; gap: 15px; margin-top: 10px; }
        
        .btn-back {
            display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px;
            background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md);
            color: var(--text-main); font-size: 13px; font-weight: 600; cursor: pointer;
            transition: all 0.2s; box-shadow: var(--shadow-sm);
        }
        .btn-back:hover { border-color: var(--primary-solid); color: var(--primary-solid); transform: translateX(-2px); }
        .btn-back:disabled { opacity: 0.5; cursor: not-allowed; transform: none; border-color: var(--border-color); color: var(--text-muted); }

        .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; color: var(--text-muted); }
        .breadcrumb-item { cursor: pointer; transition: color 0.2s; padding: 4px 8px; border-radius: 6px; }
        .breadcrumb-item:hover { color: var(--primary-solid); background: #eff6ff; }
        .breadcrumb-item.active { color: var(--secondary-solid); font-weight: 700; pointer-events: none; background: #e2e8f0; }

        /* --- LAYOUT GRID --- */
        .admin-grid { display: grid; grid-template-columns: 340px 1fr; gap: 32px; align-items: start; }
        .controls { position: sticky; top: 40px; display: flex; flex-direction: column; gap: 24px; }

        /* --- CONTROL PANELS (Forms) --- */
        .control-panel { background: var(--bg-surface); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); }
        .control-panel h2 { font-size: 1.1rem; font-weight: 600; margin-top: 0; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; }
        
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
        input, select { width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 14px; font-family: 'Inter', sans-serif; background: var(--bg-surface); box-sizing: border-box; transition: 0.2s; }
        input:focus, select:focus { outline: none; border-color: var(--primary-solid); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        optgroup { font-weight: 700; color: var(--secondary-solid); }

        .checkbox-group { display: flex; align-items: center; gap: 10px; background: var(--bg-body); padding: 12px; border-radius: var(--radius-md); border: 1px solid var(--border-color); cursor: pointer; margin-bottom: 16px; }
        .checkbox-group input { width: 16px; height: 16px; accent-color: var(--primary-solid); }
        .checkbox-group label { margin: 0; font-weight: 500; cursor: pointer; }

        .btn { width: 100%; padding: 12px; border: none; border-radius: var(--radius-md); font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; color: white; }
        .btn-primary { background: var(--primary-solid); }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-secondary { background: var(--secondary-solid); }
        .btn-secondary:hover { background: #334155; }

        /* --- FOLDER VIEW (Departments) --- */
        .folder-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; }
        .folder-card { background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; cursor: pointer; transition: all 0.2s; display: flex; align-items: flex-start; gap: 16px; box-shadow: var(--shadow-sm); }
        .folder-card:hover { border-color: var(--primary-solid); box-shadow: var(--shadow-md); transform: translateY(-3px); }
        .folder-card.empty-folder { opacity: 0.7; background: #f8fafc; }
        .folder-card.empty-folder:hover { opacity: 1; background: var(--bg-surface); }
        .folder-icon { width: 50px; height: 50px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; background: #eff6ff; color: #3b82f6; }
        .folder-content h3 { margin: 0 0 6px 0; font-size: 16px; font-weight: 700; color: var(--text-main); }
        .folder-content p { margin: 0; font-size: 13px; color: var(--text-muted); font-weight: 500; }

        /* --- SEMESTER VIEW (Horizontal Rows) --- */
        .semester-container { display: flex; flex-direction: column; gap: 24px; }
        .semester-row { background: var(--bg-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); overflow: hidden; }
        .semester-header { background: #f1f5f9; padding: 16px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; }
        .semester-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; }
        
        .courses-grid { padding: 24px; display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; background: var(--bg-surface); }
        
        /* Clean Course Card */
        .course-card { background: var(--bg-surface); border-radius: var(--radius-md); padding: 16px; border: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 12px; transition: border-color 0.2s; }
        .course-card:hover { border-color: var(--border-hover); box-shadow: var(--shadow-sm); }
        .course-top { display: flex; justify-content: space-between; align-items: flex-start; }
        .course-code { font-size: 15px; font-weight: 700; color: var(--text-main); }
        .course-title { font-size: 13px; color: var(--text-muted); line-height: 1.4; }
        
        .delete-btn { color: var(--text-muted); background: none; border: none; padding: 4px; cursor: pointer; border-radius: 4px; transition: 0.2s; }
        .delete-btn:hover { color: var(--danger-solid); background: #fee2e2; }

        /* Tags */
        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; width: fit-content; }
        .badge-assigned { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .badge-unassigned { background: #fff1f2; color: #9f1239; border: 1px solid #fecdd3; }
        .empty-state { grid-column: 1 / -1; text-align: center; padding: 20px; color: var(--text-muted); font-size: 14px; font-weight: 500; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .view-animate { animation: fadeIn 0.3s ease forwards; }
    </style>
</head>

<body>

    <div class="main-wrapper">
        <?php include('sidebar.php'); ?>
        <div class="content-area">
            <?php include('header.php'); ?>
            <div class="container">
                
                <div class="page-header">
                    <div>
                        <h1><i class="fas fa-layer-group text-primary"></i> Curriculum & Faculty Matrix</h1>
                        <div class="breadcrumb-container">
                            <button class="btn-back" id="btnBack" onclick="goBack()" disabled>
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <div class="breadcrumb" id="breadcrumb"></div>
                        </div>
                    </div>
                </div>

                <div class="admin-grid">
                    
                    <div class="controls">
                        <div class="control-panel">
                            <h2><i class="fas fa-book-open" style="color: var(--primary-solid);"></i> Register Course</h2>
                            <form method="POST">
                                <div class="form-group">
                                    <label>Department Target</label>
                                    <select name="department" required>
                                        <option value="" disabled selected>Select Department...</option>
                                        <?php foreach ($allowed_departments as $d)
                                            echo "<option value='$d'>$d</option>"; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Course Code</label>
                                    <input type="text" name="course_code" placeholder="e.g. CS-101" required>
                                </div>
                                <div class="form-group">
                                    <label>Course Title</label>
                                    <input type="text" name="course_title" placeholder="e.g. Intro to Programming" required>
                                </div>
                                <div class="checkbox-group" onclick="document.getElementById('labCheck').click()">
                                    <input type="checkbox" name="is_lab" id="labCheck" onchange="handleLabToggle(this); event.stopPropagation();">
                                    <label for="labCheck">Set as Lab Course</label>
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
                                    <label>Semester Target</label>
                                    <select name="semester">
                                        <?php for ($i = 1; $i <= 8; $i++)
                                            echo "<option value='$i'>Semester $i</option>"; ?>
                                    </select>
                                </div>
                                <button type="submit" name="add_course" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Course
                                </button>
                            </form>
                        </div>

                        <div class="control-panel">
                            <h2><i class="fas fa-user-tie" style="color: var(--secondary-solid);"></i> Assign Faculty</h2>
                            <form method="POST">
                                <div class="form-group">
                                    <label>Select Course</label>
                                    <select name="course_id" required>
                                        <option value="" disabled selected>Choose course...</option>
                                        <?php
                                        $curr_dept = '';
                                        mysqli_data_seek($all_courses, 0);
                                        while ($c = mysqli_fetch_assoc($all_courses)) {
                                            $d = $c['department'] ? $c['department'] : 'Computer Science';
                                            if ($d != $curr_dept) {
                                                if ($curr_dept != '')
                                                    echo "</optgroup>";
                                                echo "<optgroup label='$d'>";
                                                $curr_dept = $d;
                                            }
                                            echo "<option value='{$c['id']}'>[Sem {$c['semester']}] {$c['course_code']}</option>";
                                        }
                                        if ($curr_dept != '')
                                            echo "</optgroup>";
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Select Faculty Member</label>
                                    <select name="teacher_name" required>
                                        <option value="" disabled selected>Choose professor...</option>
                                        <?php
                                        $curr_t_dept = '';
                                        mysqli_data_seek($teachers_list, 0);
                                        while ($t = mysqli_fetch_assoc($teachers_list)) {
                                            $td = $t['department'] ? $t['department'] : 'Other';
                                            if ($td != $curr_t_dept) {
                                                if ($curr_t_dept != '')
                                                    echo "</optgroup>";
                                                echo "<optgroup label='$td'>";
                                                $curr_t_dept = $td;
                                            }
                                            echo "<option value='" . htmlspecialchars($t['full_name']) . "'>" . htmlspecialchars($t['full_name']) . "</option>";
                                        }
                                        if ($curr_t_dept != '')
                                            echo "</optgroup>";
                                        ?>
                                    </select>
                                </div>
                                <button type="submit" name="assign_teacher" class="btn btn-secondary">
                                    <i class="fas fa-link"></i> Link to Course
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="main-content" id="main-view">
                        </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Data Injected from PHP
        const dataHierarchy = <?php echo $json_data; ?>;
        
        let currentView = 'departments'; // 'departments', 'semesters'
        let stateDept = null;

        const mainView = document.getElementById('main-view');
        const breadcrumb = document.getElementById('breadcrumb');
        const btnBack = document.getElementById('btnBack');

        // Initialize UI
        renderDepartments();

        function goBack() {
            if (currentView === 'semesters') renderDepartments();
        }

        function setViewHTML(html) {
            mainView.innerHTML = `<div class="view-animate">${html}</div>`;
        }

        function updateNavState() {
            btnBack.disabled = (currentView === 'departments');
            
            let html = `<span class="breadcrumb-item ${currentView === 'departments' ? 'active' : ''}" onclick="renderDepartments()"><i class="fas fa-building"></i> Departments</span>`;
            
            if (stateDept) {
                html += `<i class="fas fa-chevron-right breadcrumb-separator"></i>`;
                html += `<span class="breadcrumb-item active">${stateDept}</span>`;
            }
            breadcrumb.innerHTML = html;
        }

        // LEVEL 1: Render Department Folders
        function renderDepartments() {
            currentView = 'departments';
            stateDept = null;
            updateNavState();

            let html = `<div class="folder-grid">`;
            for (const [deptName, deptData] of Object.entries(dataHierarchy)) {
                const emptyClass = deptData.total_courses === 0 ? 'empty-folder' : '';
                html += `
                    <div class="folder-card ${emptyClass}" onclick="renderSemesters('${deptName}')">
                        <div class="folder-icon"><i class="fas fa-network-wired"></i></div>
                        <div class="folder-content">
                            <h3>${deptName}</h3>
                            <p><i class="fas fa-book"></i> ${deptData.total_courses} Total Courses</p>
                        </div>
                    </div>
                `;
            }
            setViewHTML(html + `</div>`);
        }

        // LEVEL 2: Render the 8 Semester Rows for the clicked Department
        function renderSemesters(deptName) {
            currentView = 'semesters';
            stateDept = deptName;
            updateNavState();

            const semesters = dataHierarchy[deptName].semesters;
            let html = `<div class="semester-container">`;
            
            // Loop exactly 1 to 8 to show all semester boxes
            for (let s = 1; s <= 8; s++) {
                const courses = semesters[s] || [];
                
                html += `
                    <div class="semester-row">
                        <div class="semester-header">
                            <h3>Semester ${s}</h3>
                        </div>
                        <div class="courses-grid">
                `;

                if (courses.length > 0) {
                    courses.forEach(course => {
                        const teacherBadge = course.teacher_name 
                            ? `<span class="badge badge-assigned"><i class="fas fa-check-circle"></i> ${course.teacher_name}</span>`
                            : `<span class="badge badge-unassigned"><i class="fas fa-exclamation-circle"></i> Needs Faculty</span>`;

                        html += `
                            <div class="course-card">
                                <div class="course-top">
                                    <div class="course-code">${course.course_code}</div>
                                    <button type="button" onclick="confirmDelete(${course.id})" class="delete-btn" title="Remove Course">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                <div class="course-title">
                                    ${course.course_title} <br>
                                    <strong>${course.credit_hours}</strong>
                                </div>
                                <div style="margin-top: auto;">
                                    ${teacherBadge}
                                </div>
                            </div>
                        `;
                    });
                } else {
                    html += `<div class="empty-state">No courses assigned to Semester ${s} in this department.</div>`;
                }

                html += `</div></div>`; // Close grid and row
            }
            
            setViewHTML(html + `</div>`);
        }

        // --- UTILS & SWEETALERTS ---
        function handleLabToggle(checkbox) {
            document.getElementById('creditSelect').value = checkbox.checked ? "1" : "3";
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Remove Course',
                text: "Are you sure you want to delete this course?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = '?delete_id=' + id;
            });
        }

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) Swal.fire({ icon: 'success', title: 'Success', text: urlParams.get('success'), timer: 2000, showConfirmButton: false });
        if (urlParams.has('error')) Swal.fire({ icon: 'error', title: 'Error', text: urlParams.get('error') });
        if (urlParams.has('deleted')) Swal.fire({ icon: 'info', title: 'Deleted', text: 'Course has been removed.', timer: 2000, showConfirmButton: false });
        if (urlParams.has('assigned')) Swal.fire({ icon: 'success', title: 'Assigned', text: 'Faculty linked successfully.', timer: 2000, showConfirmButton: false });

        if (window.history.replaceState) window.history.replaceState(null, null, window.location.pathname);
    </script>
</body>
</html>