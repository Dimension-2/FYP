<?php
session_start();

// 1. DATABASE CONNECTION
// We try to include the config. If it fails, we establish connection manually.
$db_path = "../../db_config.php";
if (file_exists($db_path)) {
    include($db_path);
} else {
    $conn = mysqli_connect("localhost", "root", "", "fyp");
}

// 2. FETCH TEACHER DATA MANUALLY (Since login.php didn't save the name)
$teacher_name = "";
if (isset($_SESSION['teacher_id'])) {
    $emp_id = $_SESSION['teacher_id'];
    // Look up the name using the ID stored in session
    $user_query = mysqli_query($conn, "SELECT full_name FROM teachers WHERE employee_id = '$emp_id'");
    if ($user_row = mysqli_fetch_assoc($user_query)) {
        $teacher_name = $user_row['full_name'];
    }
}

// 3. DEFINE $data TO STOP THE HEADER WARNING
// Your header.php is looking for a variable called $data. We create it here.
$data = ['full_name' => $teacher_name];

// 5. VALIDATION
if (empty($teacher_name)) {
    echo "<div class='alert alert-danger m-5'>Error: Could not identify teacher. Please re-login.</div>";
    exit();
}
// include_once('../Bars/header.php');
// include_once('../Bars/sidebar.php');
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/profile_style.css">
<link rel="stylesheet" href="../css/teacher_course.css">

<div class="d-flex" id="wrapper">
    <?php include('../Bars/sidebar.php'); ?>

    <div id="page-content-wrapper">
        <?php include('../Bars/header.php'); ?>
        <div class="content-wrapper" style="padding: 20px; background: #f8f9fa; min-height: 100vh;">
            <div class="container-fluid">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-white py-3">
                        <h3 class="card-title" style="font-weight: 700; color: #2c3e50;">
                            <i class="fas fa-book mr-2" style="color: #10b981;"></i> My Assigned Courses
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4 p-3"
                            style="background: rgba(16, 185, 129, 0.05); border-left: 4px solid #10b981; border-radius: 8px;">
                            <div>
                                <p class="text-muted mb-0"
                                    style="font-size: 0.9rem; letter-spacing: 0.5px; text-transform: uppercase;">
                                    Instructor</p>
                                <h5 class="mb-0" style="font-weight: 700; color: #2c3e50;">
                                    <?php echo htmlspecialchars($teacher_name); ?>
                                </h5>
                            </div>
                            <div class="ml-auto text-right">
                                <span class="badge badge-light shadow-sm p-2"
                                    style="border-radius: 10px; color: #64748b;">
                                    <i class="fas fa-calendar-alt mr-1"></i> Academic Session 2024
                                </span>
                            </div>
                        </div>

                        <div class="table-responsive"
                            style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                            <table class="table table-borderless align-middle mb-0">
                                <thead style="background-color: #1e293b; color: #f8fafc;">
                                    <tr>
                                        <th class="py-3 px-4" style="font-weight: 500; font-size: 0.85rem;">#</th>
                                        <th class="py-3" style="font-weight: 500; font-size: 0.85rem;">COURSE CODE</th>
                                        <th class="py-3" style="font-weight: 500; font-size: 0.85rem;">COURSE TITLE</th>
                                        <th class="py-3 text-center" style="font-weight: 500; font-size: 0.85rem;">
                                            CREDITS</th>
                                        <th class="py-3 text-center" style="font-weight: 500; font-size: 0.85rem;">
                                            SEMESTER</th>
                                    </tr>
                                </thead>
                                <tbody style="background-color: white;">
                                    <?php
                                    $safe_name = mysqli_real_escape_string($conn, $teacher_name);
                                    $course_sql = "SELECT * FROM course_assignments WHERE teacher_name = '$safe_name' ORDER BY semester ASC";
                                    $course_result = mysqli_query($conn, $course_sql);

                                    if (mysqli_num_rows($course_result) > 0) {
                                        $n = 1;
                                        while ($row = mysqli_fetch_assoc($course_result)) {
                                            ?>
                                            <tr style="border-bottom: 1px solid #f1f5f9; transition: all 0.2s ease;"
                                                onmouseover="this.style.backgroundColor='#f8fafc'"
                                                onmouseout="this.style.backgroundColor='white'">
                                                <td class="px-4 text-muted font-weight-bold">
                                                    <?php echo str_pad($n++, 2, "0", STR_PAD_LEFT); ?></td>
                                                <td>
                                                    <span class="badge"
                                                        style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 6px 12px; font-size: 0.85rem; font-weight: 700;">
                                                        <?php echo $row['course_code']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div style="font-weight: 600; color: #1e293b; font-size: 1rem;">
                                                        <?php echo $row['course_title']; ?></div>
                                                    <small class="text-muted">Theory & Practical</small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-inline-block px-3 py-1"
                                                        style="background: #eff6ff; color: #1e40af; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                                        <?php echo $row['credit_hours']; ?> Hrs
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span style="color: #6366f1; font-weight: 700; font-size: 0.9rem;">
                                                        <i class="fas fa-graduation-cap mr-1"></i> Semester
                                                        <?php echo $row['semester']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center py-5'>
                            <img src='https://cdn-icons-png.flaticon.com/512/7486/7486744.png' style='width: 80px; opacity: 0.3; margin-bottom: 15px;'><br>
                            <span class='text-muted'>No courses assigned to your profile yet.</span>
                          </td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>
</div>
</div>