<?php
session_start();
$page_title = "Admin - Faculty Feedback Records";
$current_page = "admin_feedback_view.php";

$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

// 1. Fetch All Teachers and Courses from assignments
$hierarchy = [];
$sql_courses = "SELECT ca.teacher_name, ca.course_code, ca.course_title, IFNULL(tfl.is_locked, 0) as is_locked 
                FROM course_assignments ca 
                LEFT JOIN teacher_feedback_locks tfl ON ca.teacher_name = tfl.teacher_name
                WHERE ca.teacher_name IS NOT NULL AND ca.teacher_name != ''";
$res_courses = $conn->query($sql_courses);

while ($row = $res_courses->fetch_assoc()) {
    $teacher = $row['teacher_name'];
    if (!isset($hierarchy[$teacher])) {
        $hierarchy[$teacher] = [
            'is_locked' => $row['is_locked'],
            'courses' => [],
            'total_evaluations' => 0,
            'teacher_avg' => 0,
            'all_scores' => []
        ];
    }
    $hierarchy[$teacher]['courses'][$row['course_code']] = [
        'title' => $row['course_title'],
        'evaluations' => 0,
        'course_avg' => 0,
        'scores' => [],
        'feedbacks' => []
    ];
}

// 2. Fetch Feedback and populate hierarchy
$sql_feedback = "SELECT * FROM faculty_feedback";
$res_feedback = $conn->query($sql_feedback);

while ($fb = $res_feedback->fetch_assoc()) {
    $teacher = $fb['teacher_name'];
    $code = $fb['course_code'];

    // Fallback if teacher_name wasn't saved in older records (requires join)
    if (empty($teacher)) {
        $stmt = $conn->query("SELECT teacher_name FROM course_assignments WHERE course_code = '$code' LIMIT 1");
        if ($stmt && $stmt->num_rows > 0)
            $teacher = $stmt->fetch_assoc()['teacher_name'];
    }

    if (isset($hierarchy[$teacher]) && isset($hierarchy[$teacher]['courses'][$code])) {
        $scores = json_decode($fb['scores'], true);
        $avg = is_array($scores) && count($scores) > 0 ? array_sum($scores) / count($scores) : 0;

        $hierarchy[$teacher]['courses'][$code]['evaluations']++;
        $hierarchy[$teacher]['courses'][$code]['scores'][] = $avg;
        $hierarchy[$teacher]['courses'][$code]['feedbacks'][] = $fb;

        $hierarchy[$teacher]['total_evaluations']++;
        $hierarchy[$teacher]['all_scores'][] = $avg;
    }
}

// 3. Calculate Averages
foreach ($hierarchy as $t_name => &$t_data) {
    if (count($t_data['all_scores']) > 0) {
        $t_data['teacher_avg'] = array_sum($t_data['all_scores']) / count($t_data['all_scores']);
    }
    foreach ($t_data['courses'] as $c_code => &$c_data) {
        if (count($c_data['scores']) > 0) {
            $c_data['course_avg'] = array_sum($c_data['scores']) / count($c_data['scores']);
        }
    }
}
$hierarchy_json = json_encode($hierarchy);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --teal-color: #00cba9;
            --folder-color: #c946f5;
        }

        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-wrapper {
            display: flex;
            width: 100%;
        }

        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            background: #2c3e50;
        }

        .content-area {
            flex-grow: 1;
            margin-left: var(--sidebar-width);
            padding: 30px;
        }

        /* Search Bar */
        .search-container {
            position: relative;
            max-width: 600px;
            margin: 0 auto 40px auto;
        }

        .search-container input {
            border-radius: 30px;
            padding: 15px 20px 15px 50px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            width: 100%;
            font-size: 1.1rem;
        }

        .search-container i {
            position: absolute;
            left: 20px;
            top: 18px;
            color: #a0aec0;
            font-size: 1.2rem;
        }

        /* Folders */
        .folder-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 30px;
        }

        .folder-card {
            background: transparent;
            border: none;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s;
            position: relative;
            padding: 15px;
            border-radius: 15px;
        }

        .folder-card:hover {
            transform: translateY(-5px);
            background: #fff;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .folder-icon {
            font-size: 80px;
            color: var(--folder-color);
            line-height: 1;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }

        .badge-count {
            position: absolute;
            top: 15px;
            right: 0;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 2px solid #f4f7f6;
        }

        /* Breadcrumb / Nav */
        .view-section {
            display: none;
        }

        .view-section.active {
            display: block;
        }

        .back-btn {
            cursor: pointer;
            color: var(--teal-color);
            font-weight: bold;
            margin-bottom: 20px;
            display: inline-block;
        }
    </style>
</head>

<body>

    <div class="main-wrapper">
        <div id="sidebar"><?php include('sidebar.php'); ?></div>

        <div class="content-area">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold">Faculty Evaluation Directory</h3>
                <div>
                    <button class="btn btn-warning btn-sm me-2 fw-bold" onclick="toggleGlobalLock(1)"><i
                            class="bi bi-lock-fill"></i> Lock All</button>
                    <button class="btn btn-success btn-sm fw-bold" onclick="toggleGlobalLock(0)"><i
                            class="bi bi-unlock-fill"></i> Unlock All</button>
                </div>
            </div>

            <div id="teacherView" class="view-section active">
                <div class="search-container">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" placeholder="Search across all teachers globally..."
                        onkeyup="filterTeachers()">
                </div>
                <div class="folder-grid" id="teacherGrid"></div>
            </div>

            <div id="courseView" class="view-section">
                <span class="back-btn" onclick="showView('teacherView')"><i class="bi bi-arrow-left"></i> Back to
                    Teachers</span>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 id="currentTeacherName" class="fw-bold m-0"></h4>
                    <div>
                        <button id="btnToggleLock" class="btn btn-sm text-white fw-bold"
                            onclick="toggleTeacherLock()"></button>
                        <button class="btn btn-outline-primary btn-sm ms-2" onclick="window.print()"><i
                                class="bi bi-printer"></i> Print Report</button>
                    </div>
                </div>
                <div class="folder-grid" id="courseGrid"></div>
            </div>

            <div id="detailView" class="view-section">
                <span class="back-btn" onclick="showView('courseView')"><i class="bi bi-arrow-left"></i> Back to
                    Courses</span>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 id="currentCourseName" class="fw-bold m-0"></h4>
                    <div class="badge bg-teal fs-6" id="courseAvgBadge"></div>
                </div>
                <div class="card shadow-sm border-0" style="border-radius:15px;">
                    <table class="table table-hover mb-0">
                        <thead style="background:var(--teal-color); color:white;">
                            <tr>
                                <th>Student Reg #</th>
                                <th>Average Score</th>
                                <th>Comments</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="feedbackTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const data = <?php echo $hierarchy_json; ?>;
        let currentTeacher = '';

        function renderTeachers() {
            const grid = document.getElementById('teacherGrid');
            grid.innerHTML = '';
            for (const [teacher, info] of Object.entries(data)) {
                const pct = (info.teacher_avg / 5.0) * 100;
                const lockIcon = info.is_locked == 1 ? '<i class="bi bi-lock-fill text-danger position-absolute" style="bottom:15px; right:15px;"></i>' : '';

                grid.innerHTML += `
                <div class="folder-card teacher-card" onclick="openTeacher('${teacher}')" data-name="${teacher.toLowerCase()}">
                    <div class="folder-icon">
                        <i class="bi bi-folder-fill"></i>
                        <div class="badge-count">${Object.keys(info.courses).length}</div>
                        ${lockIcon}
                    </div>
                    <h6 class="fw-bold mb-1">${teacher}</h6>
                    <small class="text-muted">${info.total_evaluations} Evaluations</small><br>
                    <small class="text-success fw-bold">${pct.toFixed(1)}% Rating</small>
                </div>
            `;
            }
        }

        function filterTeachers() {
            const term = document.getElementById('searchInput').value.toLowerCase();
            document.querySelectorAll('.teacher-card').forEach(card => {
                card.style.display = card.getAttribute('data-name').includes(term) ? 'block' : 'none';
            });
        }

        function openTeacher(teacher) {
            currentTeacher = teacher;
            document.getElementById('currentTeacherName').innerText = teacher;

            // Setup Lock Button
            const btnLock = document.getElementById('btnToggleLock');
            const isLocked = data[teacher].is_locked == 1;
            btnLock.className = isLocked ? 'btn btn-sm btn-success fw-bold' : 'btn btn-sm btn-danger fw-bold';
            btnLock.innerHTML = isLocked ? '<i class="bi bi-unlock-fill"></i> Unlock Evaluations' : '<i class="bi bi-lock-fill"></i> Lock Evaluations';

            // Render Course Folders
            const grid = document.getElementById('courseGrid');
            grid.innerHTML = '';
            const courses = data[teacher].courses;
            for (const [code, c_data] of Object.entries(courses)) {
                const pct = (c_data.course_avg / 5.0) * 100;
                grid.innerHTML += `
                <div class="folder-card" onclick="openCourse('${code}')">
                    <div class="folder-icon" style="color: #00cba9;"><i class="bi bi-folder-fill"></i>
                        <div class="badge-count bg-secondary">${c_data.evaluations}</div>
                    </div>
                    <h6 class="fw-bold mb-1">${code}</h6>
                    <small class="text-muted">${c_data.title}</small><br>
                    <small class="text-primary fw-bold">${pct.toFixed(1)}% Rating</small>
                </div>
            `;
            }
            showView('courseView');
        }

        function openCourse(code) {
            const c_data = data[currentTeacher].courses[code];
            document.getElementById('currentCourseName').innerText = `${code} - ${c_data.title}`;
            document.getElementById('courseAvgBadge').innerText = `Overall: ${((c_data.course_avg / 5) * 100).toFixed(1)}%`;

            const tbody = document.getElementById('feedbackTableBody');
            tbody.innerHTML = '';
            if (c_data.feedbacks.length === 0) tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No feedback yet.</td></tr>';

            c_data.feedbacks.forEach(f => {
                const scores = JSON.parse(f.scores);
                const avg = Object.values(scores).reduce((a, b) => Number(a) + Number(b), 0) / Object.keys(scores).length;
                tbody.innerHTML += `
                <tr>
                    <td class="fw-bold">${f.registration_no}</td>
                    <td><span class="badge ${avg >= 4 ? 'bg-success' : (avg >= 3 ? 'bg-warning text-dark' : 'bg-danger')}">${avg.toFixed(1)} / 5.0</span></td>
                    <td><small class="text-muted">${f.comments || 'No comments'}</small></td>
                    <td>${new Date(f.submission_date).toLocaleDateString()}</td>
                </tr>
            `;
            });
            showView('detailView');
        }

        function showView(viewId) {
            document.querySelectorAll('.view-section').forEach(v => v.classList.remove('active'));
            document.getElementById(viewId).classList.add('active');
        }

        async function toggleTeacherLock() {
            const newState = data[currentTeacher].is_locked == 1 ? 0 : 1;
            await fetch('admin_lock_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=toggle&teacher=${encodeURIComponent(currentTeacher)}&state=${newState}`
            });
            data[currentTeacher].is_locked = newState;
            openTeacher(currentTeacher); // Refresh view
        }

        async function toggleGlobalLock(state) {
            if (!confirm(`Are you sure you want to ${state == 1 ? 'LOCK' : 'UNLOCK'} all teachers globally?`)) return;
            await fetch('admin_lock_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=global&state=${state}`
            });
            location.reload();
        }

        renderTeachers();
    </script>
</body>

</html>