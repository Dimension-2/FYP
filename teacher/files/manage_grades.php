<?php
session_start();
$db_path = "../../db_config.php";
$conn = file_exists($db_path) ? include($db_path) : mysqli_connect("localhost", "root", "", "fyp");

// Safety check for session
if (!isset($_SESSION['teacher_id'])) {
    die("Access Denied. Please log in.");
}

$emp_id = mysqli_real_escape_string($conn, $_SESSION['teacher_id']);

// Fetch teacher data
$user_query = mysqli_query($conn, "SELECT * FROM teachers WHERE employee_id = '$emp_id'");
$data = mysqli_fetch_assoc($user_query); 
$teacher_name = $data['full_name'] ?? "Teacher";

$success_msg = "";
$error_msg = "";

// DELETE ASSESSMENT ITEM LOGIC
if (isset($_GET['delete_item']) && isset($_GET['course'])) {
    $item_id = mysqli_real_escape_string($conn, $_GET['delete_item']);
    $c_code = mysqli_real_escape_string($conn, $_GET['course']);
    mysqli_query($conn, "DELETE FROM student_marks WHERE item_id = '$item_id'");
    mysqli_query($conn, "DELETE FROM grading_items WHERE id = '$item_id'");
    header("Location: manage_grades.php?course=" . $c_code . "&msg=deleted");
    exit();
}

if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $success_msg = "Assessment deleted successfully.";
}

// ADD NEW ASSESSMENT ITEM
if (isset($_POST['add_item'])) {
    $c_code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $label = trim(mysqli_real_escape_string($conn, $_POST['item_label']));
    $cat = mysqli_real_escape_string($conn, $_POST['category']);
    $max = mysqli_real_escape_string($conn, $_POST['max_marks']);

    $check = mysqli_query($conn, "SELECT id FROM grading_items WHERE course_code='$c_code' AND item_label='$label'");
    if (mysqli_num_rows($check) > 0) {
        $error_msg = "Label '$label' already exists for this course.";
    } else {
        mysqli_query($conn, "INSERT INTO grading_items (course_code, item_type, item_label, total_marks) VALUES ('$c_code', '$cat', '$label', '$max')");
        $success_msg = "Created '$label' successfully.";
    }
}

// UPDATE MARKS LOGIC
if (isset($_POST['update_marks'])) {
    $item_id = mysqli_real_escape_string($conn, $_POST['item_id']);
    $course_code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $res = mysqli_query($conn, "SELECT total_marks FROM grading_items WHERE id = '$item_id'");
    $max_limit = mysqli_fetch_assoc($res)['total_marks'];

    if (isset($_POST['marks'])) {
        foreach ($_POST['marks'] as $reg_no => $obtained) {
            $reg_no = mysqli_real_escape_string($conn, $reg_no);
            $is_absent = isset($_POST['absent'][$reg_no]) ? 1 : 0;
            $final_marks = ($is_absent == 1) ? 0 : mysqli_real_escape_string($conn, $obtained);

            if ($final_marks <= $max_limit) {
                $query = "INSERT INTO student_marks (registration_no, course_code, item_id, obtained_marks, is_absent) 
                          VALUES ('$reg_no', '$course_code', '$item_id', '$final_marks', '$is_absent') 
                          ON DUPLICATE KEY UPDATE obtained_marks = '$final_marks', is_absent = '$is_absent'";
                mysqli_query($conn, $query);
            }
        }
        $success_msg = "Grades updated successfully.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Management | Teacher Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/profile_style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root { 
            --sidebar-width: 260px;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
        }
        
        body { 
            background: #f0f2f5; 
            font-family: 'Inter', sans-serif; 
            margin: 0;
            padding: 0;
        }

        /* Fixed Sidebar Alignment */
        .sidebar-container {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
        }

        /* Content area flows to the right of sidebar */
        .content-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header Alignment */
        .header-container {
            width: 100%;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            padding: 10px 20px;
        }

        .main-content-area {
            padding: 30px;
        }

        .main-card { background: white; border-radius: 15px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); padding: 30px; border: none; }
        .sidebar-card { background: white; border-radius: 15px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); padding: 20px; }

        .mark-input {
            width: 100px; text-align: center; font-weight: 700;
            border: 2px solid #eee; border-radius: 8px; padding: 5px;
        }
        .mark-input:disabled { background-color: #f8f9fa; color: #ced4da; cursor: not-allowed; }

        .btn-delete { color: #dc3545; border: 1px solid #dc3545; background: transparent; }
        .btn-delete:hover { background: #dc3545; color: white; transform: scale(1.05); }

        @media print {
            .no-print, .sidebar-container, .header-container { display: none !important; }
            .content-wrapper { margin-left: 0 !important; }
            .main-card { box-shadow: none; border: 1px solid #eee; width: 100%; }
        }

        @media (max-width: 992px) {
            .sidebar-container { display: none; }
            .content-wrapper { margin-left: 0; }
        }
    </style>
</head>
<body>

    <div class="sidebar-container no-print">
        <?php include('../Bars/sidebar.php'); ?>
    </div>

    <div class="content-wrapper">
        
        <div class="header-container no-print">
            <?php include('../Bars/header.php'); ?>
        </div>

        <div class="main-content-area">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                    <h2 class="fw-bold"><i class="fa-solid fa-layer-group text-primary me-2"></i>Grading System</h2>
                    <div class="d-flex gap-2">
                        <?php if(isset($_GET['item_id'])): ?>
                            <button onclick="window.print()" class="btn btn-outline-dark rounded-pill px-4">
                                <i class="fa-solid fa-file-pdf me-2"></i>Export PDF
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($success_msg): ?>
                    <script>Swal.fire({ icon: 'success', title: 'Done!', text: '<?= $success_msg ?>', timer: 2000, showConfirmButton: false });</script>
                <?php endif; ?>
                <?php if ($error_msg): ?>
                    <script>Swal.fire({ icon: 'error', title: 'Error', text: '<?= $error_msg ?>' });</script>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-4 no-print">
                        <div class="sidebar-card mb-4">
                            <form method="GET">
                                <label class="form-label small fw-bold text-uppercase text-muted">Course Selection</label>
                                <select name="course" class="form-select form-select-lg mb-3" onchange="this.form.submit()">
                                    <option value="">Select a Course...</option>
                                    <?php
                                    $courses = mysqli_query($conn, "SELECT DISTINCT course_code FROM course_assignments WHERE teacher_name = '$teacher_name'");
                                    while ($c = mysqli_fetch_assoc($courses)): ?>
                                        <option value="<?= $c['course_code']; ?>" <?= (isset($_GET['course']) && $_GET['course'] == $c['course_code']) ? 'selected' : ''; ?>><?= $c['course_code'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </form>

                            <?php if (isset($_GET['course'])): 
                                $sel_course = mysqli_real_escape_string($conn, $_GET['course']); ?>
                                <hr>
                                <h6 class="fw-bold mb-3"><i class="fa-solid fa-plus-circle me-2"></i>New Assessment</h6>
                                <form method="POST">
                                    <input type="hidden" name="course_code" value="<?= $sel_course; ?>">
                                    <select name="category" class="form-select mb-2" required>
                                        <option value="Sessional">Sessional</option>
                                        <option value="Mid">Midterm</option>
                                        <option value="Final">Final Exam</option>
                                    </select>
                                    <input type="text" name="item_label" class="form-control mb-2" placeholder="Label (e.g. Quiz 1)" required>
                                    <input type="number" name="max_marks" class="form-control mb-3" placeholder="Total Marks" required>
                                    <button type="submit" name="add_item" class="btn btn-primary w-100 fw-bold">Create Item</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <?php if (isset($_GET['course'])): ?>
                            <div class="main-card">
                                <form method="GET" class="row g-2 mb-4 no-print">
                                    <input type="hidden" name="course" value="<?= htmlspecialchars($_GET['course']); ?>">
                                    <div class="col-md-7">
                                        <select name="item_id" id="item_id_select" class="form-select" required>
                                            <option value="">-- Choose Assessment --</option>
                                            <?php
                                            $items = mysqli_query($conn, "SELECT * FROM grading_items WHERE course_code = '$sel_course'");
                                            while ($i = mysqli_fetch_assoc($items)): ?>
                                                <option value="<?= $i['id']; ?>" <?= (isset($_GET['item_id']) && $_GET['item_id'] == $i['id']) ? 'selected' : ''; ?>><?= $i['item_label']; ?> (Max: <?= $i['total_marks']; ?>)</option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-dark w-100">Load List</button>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-delete w-100" onclick="handleDelete()">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </form>

                                <?php if (isset($_GET['item_id']) && !empty($_GET['item_id'])):
                                    $sel_item_id = mysqli_real_escape_string($conn, $_GET['item_id']);
                                    $item_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM grading_items WHERE id = '$sel_item_id'"));
                                    $sem_res = mysqli_query($conn, "SELECT semester FROM course_assignments WHERE course_code = '$sel_course' LIMIT 1");
                                    $semester = mysqli_fetch_assoc($sem_res)['semester'];
                                    $students = mysqli_query($conn, "SELECT registration_no FROM profile WHERE semester = '$semester'");
                                    ?>
                                    
                                    <div class="text-center mb-4">
                                        <h4 class="fw-bold mb-0"><?= $sel_course ?> - <?= $item_data['item_label'] ?></h4>
                                        <p class="text-muted">Total Marks: <?= $item_data['total_marks'] ?> | Semester: <?= $semester ?></p>
                                    </div>

                                    <form method="POST" id="gradeForm">
                                        <input type="hidden" name="course_code" value="<?= $sel_course; ?>">
                                        <input type="hidden" name="item_id" value="<?= $sel_item_id; ?>">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Registration No</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Obtained Marks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($s = mysqli_fetch_assoc($students)):
                                                        $reg = $s['registration_no'];
                                                        $m_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM student_marks WHERE registration_no = '$reg' AND item_id = '$sel_item_id'"));
                                                        $val = $m_row['obtained_marks'] ?? '';
                                                        $abs = ($m_row['is_absent'] ?? 0) == 1 ? 'checked' : '';
                                                        ?>
                                                        <tr>
                                                            <td class="fw-bold text-primary"><?= $reg ?></td>
                                                            <td class="text-center">
                                                                <div class="form-check form-switch d-inline-block">
                                                                    <input type="checkbox" name="absent[<?= $reg ?>]" class="form-check-input"
                                                                           onclick="toggleAbsent('<?= $reg ?>', this)" <?= $abs ?>>
                                                                    <label class="small text-muted">Absent</label>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <input type="number" step="0.01" name="marks[<?= $reg ?>]"
                                                                        id="input_<?= $reg ?>" class="mark-input" value="<?= $val ?>"
                                                                        max="<?= $item_data['total_marks'] ?>" <?= $abs ? 'disabled' : '' ?>>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <button type="submit" name="update_marks" class="btn btn-success btn-lg w-100 fw-bold mt-3 no-print shadow-sm">
                                            <i class="fa-solid fa-cloud-arrow-up me-2"></i>UPDATE PERFORMANCE RECORD
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    function toggleAbsent(reg, checkbox) {
        let input = document.getElementById('input_' + reg);
        if (checkbox.checked) {
            input.dataset.oldValue = input.value;
            input.value = "0";
            input.disabled = true;
            input.style.opacity = "0.5";
        } else {
            input.disabled = false;
            input.style.opacity = "1";
            if (input.dataset.oldValue) input.value = input.dataset.oldValue;
        }
    }

    function handleDelete() {
        let select = document.getElementById('item_id_select');
        let itemId = select.value;
        let courseCode = "<?= isset($_GET['course']) ? $_GET['course'] : '' ?>";

        if (!itemId) {
            Swal.fire('Error', 'Please select an assessment to delete.', 'error');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: "All marks for this item will be lost!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "manage_grades.php?course=" + courseCode + "&delete_item=" + itemId;
            }
        });
    }

    document.getElementById('gradeForm')?.addEventListener('submit', function(e) {
        const inputs = this.querySelectorAll('.mark-input');
        inputs.forEach(input => input.disabled = false);
        
        Swal.fire({
            title: 'Saving...',
            html: 'Updating student records',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading() }
        });
    });
</script>
</body>
</html>