<?php
session_start();

// 1. INTEGRATED DATABASE CONNECTION
$host = "localhost";
$user = "root";
$pass = "";
$db = "fyp";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("<div style='color:red; padding:20px;'>Database Connection Failed: " . $conn->connect_error . "</div>");
}

// 2. Setup Student Session
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}
$user_id = $_SESSION['user_id'];

// 3. AUTO-REPAIR: Ensure required tables exist
$conn->query("CREATE TABLE IF NOT EXISTS student_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    course_code VARCHAR(20),
    grade VARCHAR(2)
)");

// 4. FETCH STUDENT SEMESTER FROM PROFILE
$profile_res = mysqli_query($conn, "SELECT semester, full_name, registration_no FROM profile WHERE id = $user_id");
if ($profile_res && mysqli_num_rows($profile_res) > 0) {
    $student = mysqli_fetch_assoc($profile_res);
    $current_semester = $student['semester'];
} else {
    $student = ['full_name' => 'Student', 'registration_no' => 'N/A'];
    $current_semester = 1;
}

// 5. FETCH REGULAR COURSES
$reg_sql = "SELECT * FROM course_assignments 
            WHERE semester = '$current_semester' 
            AND course_code NOT IN (
                SELECT course_code FROM student_results 
                WHERE student_id = $user_id AND (grade = 'A' OR grade = 'B')
            ) ORDER BY course_code ASC";
$regular_courses = mysqli_query($conn, $reg_sql);

// 6. FETCH IMPROVEMENT COURSES
$imp_sql = "SELECT ca.*, sr.grade 
            FROM course_assignments ca
            INNER JOIN student_results sr ON ca.course_code = sr.course_code
            WHERE sr.student_id = $user_id 
            AND sr.grade IN ('C', 'D', 'F') 
            ORDER BY ca.semester ASC";
$improvement_courses = mysqli_query($conn, $imp_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Enrollment | Student Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
    :root {
        /* Emerald Palette Refinement */
        --primary: #10b981;    /* Emerald 500 */
        --primary-dark: #047857; /* Emerald 700 */
        --success: #059669;
        --warning: #f59e0b;
        --danger: #ef4444;
        --bg: #f0fdf4;         /* Mint-white background */
        --border: #d1fae5;     /* Emerald 100 border */
    }

    body {
        font-family: 'Inter', sans-serif;
        background: var(--bg);
        margin: 0;
        color: #064e3b;        /* Deep Emerald text */
    }

    /* UI LAYOUT SETTINGS */
    .main-wrapper {
        display: flex;
        min-height: 100vh;
    }

    .content-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
        overflow-x: hidden;
    }

    .container {
        padding: 30px;
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
        box-sizing: border-box;
    }

    .welcome-card {
        /* End-level Emerald Gradient */
        background: linear-gradient(135deg, #059669, #10b981);
        border-radius: 12px;
        padding: 25px;
        color: white;
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.3);
    }

    .section-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin: 30px 0 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--primary-dark);
    }

    .course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .course-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid var(--border);
        padding: 20px;
        position: relative;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .course-card:hover {
        transform: translateY(-3px);
        /* Styled glow instead of generic shadow */
        box-shadow: 0 12px 20px -8px rgba(16, 185, 129, 0.2);
        border-color: var(--primary);
    }

    .course-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--primary);
        border-radius: 12px 0 0 12px;
    }

    .improvement::after {
        background: var(--warning);
    }

    .course-code {
        font-weight: 700;
        color: var(--primary);
        font-size: 0.85rem;
    }

    .course-title {
        display: block;
        font-weight: 600;
        margin: 10px 0;
        font-size: 1rem;
        color: #1e293b;
    }

    .badge {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-reg {
        background: #d1fae5; /* Light Emerald */
        color: #065f46;      /* Dark Emerald */
    }

    .badge-imp {
        background: #fef3c7;
        color: #92400e;
    }

    .teacher-info {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 15px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .empty-state {
        background: #fff;
        border: 1px dashed var(--primary);
        padding: 30px;
        text-align: center;
        border-radius: 12px;
        color: var(--primary-dark);
        background: rgba(255, 255, 255, 0.5);
    }
</style>
</head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="assets/navbar.css">
<link rel="stylesheet" href="assets/header.css">

<body>

    <div class="main-wrapper">
        <?php include('includes/navbar.php'); ?>

        <div class="content-area">
            <?php include('includes/header.php'); ?>

            <div class="container">
                <div class="welcome-card">
                    <div>
                        <h2 style="margin:0;">Welcome, <?php echo htmlspecialchars($student['full_name']); ?></h2>
                        <p style="margin:5px 0 0; opacity:0.8;">ID:
                            <?php echo htmlspecialchars($student['registration_no']); ?>
                        </p>
                    </div>
                    <div style="text-align:right;">
                        <span style="font-size:0.8rem; text-transform:uppercase;">Current Status</span>
                        <div style="font-size:1.2rem; font-weight:700;">Semester <?php echo $current_semester; ?></div>
                    </div>
                </div>

                <div class="section-title">
                    <i class="fas fa-graduation-cap" style="color:var(--primary)"></i> Current Semester Curriculum
                </div>

                <div class="course-grid">
                    <?php if (mysqli_num_rows($regular_courses) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($regular_courses)): ?>
                            <div class="course-card">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <span class="course-code"><?php echo $row['course_code']; ?></span>
                                    <span class="badge badge-reg"><?php echo $row['credit_hours']; ?> Credits</span>
                                </div>
                                <span class="course-title"><?php echo $row['course_title']; ?></span>
                                <div class="teacher-info">
                                    <i class="fas fa-user-tie"></i>
                                    <?php echo !empty($row['teacher_name']) ? htmlspecialchars($row['teacher_name']) : "Assigning Faculty..."; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state" style="grid-column: 1/-1;">
                            <p>No new courses found for Semester <?php echo $current_semester; ?>.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (mysqli_num_rows($improvement_courses) > 0): ?>
                    <div class="section-title" style="margin-top:50px;">
                        <i class="fas fa-exclamation-triangle" style="color:var(--warning)"></i> Improvement & Reappears
                    </div>
                    <div class="course-grid">
                        <?php while ($row = mysqli_fetch_assoc($improvement_courses)): ?>
                            <div class="course-card improvement">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <span class="course-code"><?php echo $row['course_code']; ?></span>
                                    <span class="badge badge-imp">Grade: <?php echo $row['grade']; ?></span>
                                </div>
                                <span class="course-title"><?php echo $row['course_title']; ?></span>
                                <div class="teacher-info">
                                    <i class="fas fa-history"></i>
                                    Requirement: Re-enrollment
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>