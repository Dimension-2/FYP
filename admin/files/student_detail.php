<?php
include('db_config.php');

// 1. PRE-INITIALIZE THE COMPLETE FOLDER STRUCTURE
// This ensures all Programs and Departments exist even with 0 students.
$allowed_programs = ['BS', 'MS', 'MPHIL', 'PHD'];
$allowed_departments = [
    'Computer Science', 'Artificial Intelligence', 'Cyber Security', 
    'Data Science', 'Physics', 'Mathematics', 'Psychology', 'English'
];

$hierarchy = [];
foreach ($allowed_programs as $prog) {
    $hierarchy[$prog] = ['total_students' => 0, 'departments' => []];
    foreach ($allowed_departments as $dept) {
        $hierarchy[$prog]['departments'][$dept] = ['total_students' => 0, 'batches' => []];
    }
}

// Department Name Normalizer (Maps abbreviations to pre-initialized full names)
function normalizeDepartment($raw_dept) {
    $map = [
        'CS'  => 'Computer Science',
        'AI'  => 'Artificial Intelligence',
        'CYS' => 'Cyber Security',
        'DS'  => 'Data Science',
        'PHY' => 'Physics',
        'MTH' => 'Mathematics',
        'PSG' => 'Psychology',
        'ENG' => 'English'
    ];
    $code = strtoupper(trim($raw_dept));
    return isset($map[$code]) ? $map[$code] : $code . ' Department'; // Fallback for unknown
}

function normalizeDegree($raw_deg) {
    $code = strtoupper(trim($raw_deg));
    return in_array($code, ['BS', 'MS', 'MPHIL', 'PHD']) ? $code : 'Other Programs';
}

// 2. FETCH AND AGGREGATE DATA
$query = "SELECT u.id, u.registration_no, p.full_name, p.status_badge 
          FROM users u 
          LEFT JOIN profile p ON u.registration_no = p.registration_no 
          WHERE u.registration_no LIKE 'UW-%'
          ORDER BY u.registration_no ASC";

$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    // SMART REGISTRATION PARSER (Case Insensitive)
    $reg = strtoupper(trim($row['registration_no']));
    $parts = explode('-', $reg);
    
    $degree = 'Other Programs';
    $batch = 'Unknown Batch';
    $dept = 'Unknown Department';
    $roll_no = 0;

    // Expected Format: UW-BATCH-DEPT-[M]-DEGREE-ROLLNO
    if (count($parts) >= 5 && $parts[0] === 'UW') {
        // FIXED: Removed the single quote (Batch '22 -> Batch 22) to prevent JavaScript Syntax Errors
        $batch = 'Batch ' . $parts[1]; 
        $dept = normalizeDepartment($parts[2]);
        
        // Handle Migration check ('M')
        if ($parts[3] === 'M' || $parts[3] === 'MIG') {
            $degree = isset($parts[4]) ? normalizeDegree($parts[4]) : 'Other Programs';
            $roll_no = isset($parts[5]) ? (int)$parts[5] : 0;
        } else {
            $degree = isset($parts[3]) ? normalizeDegree($parts[3]) : 'Other Programs';
            $roll_no = isset($parts[4]) ? (int)$parts[4] : 0;
        }
    }

    $row['extracted_roll'] = $roll_no;

    // Ensure the dynamic keys exist if a student has an unexpected format
    if (!isset($hierarchy[$degree])) {
        $hierarchy[$degree] = ['total_students' => 0, 'departments' => []];
    }
    if (!isset($hierarchy[$degree]['departments'][$dept])) {
        $hierarchy[$degree]['departments'][$dept] = ['total_students' => 0, 'batches' => []];
    }
    if (!isset($hierarchy[$degree]['departments'][$dept]['batches'][$batch])) {
        $hierarchy[$degree]['departments'][$dept]['batches'][$batch] = [];
    }

    // Assign Student
    $hierarchy[$degree]['departments'][$dept]['batches'][$batch][] = $row;
    
    // Increment Counters
    $hierarchy[$degree]['departments'][$dept]['total_students']++;
    $hierarchy[$degree]['total_students']++;
}

// 3. POST-PROCESSING: Sort all student arrays strictly by Roll Number
foreach ($hierarchy as $degKey => &$degData) {
    foreach ($degData['departments'] as $deptKey => &$deptData) {
        // Sort batches by name sequentially
        ksort($deptData['batches']);
        
        foreach ($deptData['batches'] as $batchKey => &$studentsArray) {
            usort($studentsArray, function($a, $b) {
                return $a['extracted_roll'] <=> $b['extracted_roll'];
            });
        }
    }
}

$json_data = json_encode($hierarchy);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Directory | Academic Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css"> 
    
    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-surface: #ffffff;
            --border: #e2e8f0;
            --border-hover: #94a3b8;
            
            --text-main: #0f172a;
            --text-muted: #64748b;
            
            --primary: #1e293b;
            --accent: #2563eb;
            
            --radius-md: 8px;
            --radius-lg: 12px;
            --shadow-sm: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-body); margin: 0; color: var(--text-main); -webkit-font-smoothing: antialiased; }
        .main-wrapper { display: flex; min-height: 100vh; }
        .content-area { flex: 1; display: flex; flex-direction: column; overflow-x: hidden; }
        .directory-container { padding: 40px; max-width: 1500px; margin: 0 auto; width: 100%; box-sizing: border-box; }

        /* --- HEADER & NAVIGATION --- */
        .page-header { margin-bottom: 35px; display: flex; justify-content: space-between; align-items: flex-end; }
        .page-title { font-size: 1.6rem; font-weight: 700; color: var(--text-main); margin: 0 0 12px 0; display: flex; align-items: center; gap: 12px; }

        .breadcrumb-container { display: flex; align-items: center; gap: 15px; }
        
        .btn-back {
            display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px;
            background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-md);
            color: var(--text-main); font-size: 13px; font-weight: 600; cursor: pointer;
            transition: all 0.2s; box-shadow: var(--shadow-sm);
        }
        .btn-back:hover { border-color: var(--accent); color: var(--accent); transform: translateX(-2px); }
        .btn-back:disabled { opacity: 0.5; cursor: not-allowed; transform: none; border-color: var(--border); color: var(--text-muted); }

        .breadcrumb { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 14px; font-weight: 500; color: var(--text-muted); }
        .breadcrumb-item { cursor: pointer; transition: color 0.2s; padding: 4px 8px; border-radius: 6px; }
        .breadcrumb-item:hover { color: var(--accent); background: #f1f5f9; }
        .breadcrumb-item.active { color: var(--primary); font-weight: 700; pointer-events: none; background: #e2e8f0; }
        .breadcrumb-separator { color: var(--border-hover); font-size: 11px; }

        /* Search Bar */
        .search-wrapper { position: relative; width: 400px; }
        .search-wrapper input {
            width: 100%; padding: 12px 16px 12px 45px; border: 2px solid var(--border); border-radius: var(--radius-lg);
            font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 500; color: var(--text-main);
            background: var(--bg-surface); box-sizing: border-box; transition: all 0.2s; box-shadow: var(--shadow-sm);
        }
        .search-wrapper input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
        .search-wrapper i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 16px; }

        /* --- ANIMATIONS --- */
        @keyframes slideFadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .view-animate { animation: slideFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        /* --- FOLDER CARDS --- */
        .grid-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; }
        .block-card {
            background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg);
            padding: 24px; cursor: pointer; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex; align-items: flex-start; gap: 16px; box-shadow: var(--shadow-sm);
        }
        .block-card:hover { border-color: var(--accent); box-shadow: var(--shadow-md); transform: translateY(-3px); }
        
        /* Visually dim folders with 0 students to make active ones pop */
        .block-card.empty-folder { opacity: 0.65; background: #f8fafc; }
        .block-card.empty-folder:hover { opacity: 1; background: var(--bg-surface); }

        .block-icon { width: 54px; height: 54px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
        
        .icon-program { background: #eff6ff; color: #3b82f6; }
        .icon-dept { background: #fdf4ff; color: #d946ef; }
        .icon-batch { background: #f0fdf4; color: #16a34a; }

        .block-content { flex: 1; }
        .block-title { font-size: 17px; font-weight: 700; color: var(--text-main); margin: 0 0 6px 0; text-transform: uppercase; }
        .block-meta { font-size: 13px; color: var(--text-muted); font-weight: 500; display: flex; align-items: center; gap: 6px; }

        /* --- DATA TABLE --- */
        .table-container { background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        thead { background: #f8fafc; border-bottom: 1px solid var(--border); }
        th { padding: 16px 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); }
        td { padding: 16px 20px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid var(--border); vertical-align: middle; }
        tbody tr { transition: background 0.2s; } tbody tr:hover { background: #f1f5f9; } tbody tr:last-child td { border-bottom: none; }
        
        .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .status-active { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .status-warning { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .context-tag { font-size: 12px; color: var(--text-muted); background: #f1f5f9; padding: 4px 8px; border-radius: 6px; border: 1px solid var(--border); }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-state i { font-size: 40px; margin-bottom: 15px; opacity: 0.5; }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <?php include('sidebar.php'); ?>
        
        <div class="content-area">
            <?php include('header.php'); ?>
            
            <div class="directory-container">
                <div class="page-header">
                    <div>
                        <h1 class="page-title"><i class="fas fa-sitemap" style="color: var(--primary);"></i> Institutional Database Hub</h1>
                        
                        <div class="breadcrumb-container">
                            <button class="btn-back" id="btnBack" onclick="goBack()" disabled>
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            
                            <div class="breadcrumb" id="breadcrumb">
                                </div>
                        </div>
                    </div>
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="globalSearch" placeholder="Global Search (Name, Reg, Dept...)" onkeyup="handleSearch()">
                    </div>
                </div>

                <div id="main-view"></div>
            </div>
        </div>
    </div>

    <script>
        // Inject Data from PHP
        const dataHierarchy = <?php echo $json_data; ?>;
        
        // State Management
        let currentView = 'programs'; // 'programs', 'departments', 'batches', 'students', 'search'
        let stateProgram = null;
        let stateDept = null;
        let stateBatch = null;

        const mainView = document.getElementById('main-view');
        const breadcrumb = document.getElementById('breadcrumb');
        const btnBack = document.getElementById('btnBack');

        // Initial Load
        renderPrograms();

        // --- BACK BUTTON LOGIC ---
        function goBack() {
            if (currentView === 'students') {
                renderBatches(stateProgram, stateDept);
            } else if (currentView === 'batches') {
                renderDepartments(stateProgram);
            } else if (currentView === 'departments' || currentView === 'search') {
                renderPrograms();
            }
        }

        function setViewHTML(html) {
            mainView.innerHTML = `<div class="view-animate">${html}</div>`;
        }

        // --- BREADCRUMB SYSTEM ---
        function updateNavState() {
            btnBack.disabled = (currentView === 'programs'); // Disable back button at root
            
            let html = `<span class="breadcrumb-item ${currentView === 'programs' ? 'active' : ''}" onclick="renderPrograms()"><i class="fas fa-hdd"></i> Programs</span>`;
            
            if (stateProgram) {
                html += `<i class="fas fa-chevron-right breadcrumb-separator"></i>`;
                html += `<span class="breadcrumb-item ${currentView === 'departments' ? 'active' : ''}" onclick="renderDepartments('${stateProgram}')">${stateProgram}</span>`;
            }
            if (stateDept) {
                html += `<i class="fas fa-chevron-right breadcrumb-separator"></i>`;
                html += `<span class="breadcrumb-item ${currentView === 'batches' ? 'active' : ''}" onclick="renderBatches('${stateProgram}', '${stateDept}')">${stateDept}</span>`;
            }
            if (stateBatch) {
                html += `<i class="fas fa-chevron-right breadcrumb-separator"></i>`;
                html += `<span class="breadcrumb-item active">${stateBatch}</span>`;
            }
            if (currentView === 'search') {
                html = `<span class="breadcrumb-item" onclick="renderPrograms()"><i class="fas fa-hdd"></i> Programs</span> <i class="fas fa-chevron-right breadcrumb-separator"></i> <span class="breadcrumb-item active"><i class="fas fa-search"></i> Search Results</span>`;
            }
            breadcrumb.innerHTML = html;
        }

        // --- LEVEL 1: RENDER PROGRAMS (BS, MS, PHD) ---
        function renderPrograms() {
            currentView = 'programs';
            stateProgram = null; stateDept = null; stateBatch = null;
            document.getElementById('globalSearch').value = '';
            updateNavState();

            let html = `<div class="grid-container">`;
            for (const [progName, progData] of Object.entries(dataHierarchy)) {
                const deptCount = Object.keys(progData.departments).length;
                const emptyClass = progData.total_students === 0 ? 'empty-folder' : '';
                
                html += `
                    <div class="block-card ${emptyClass}" onclick="renderDepartments('${progName}')">
                        <div class="block-icon icon-program"><i class="fas fa-graduation-cap"></i></div>
                        <div class="block-content">
                            <h3 class="block-title">${progName} Program</h3>
                            <div class="block-meta"><i class="fas fa-building"></i> ${deptCount} Departments &bull; <i class="fas fa-users"></i> ${progData.total_students} Students</div>
                        </div>
                    </div>
                `;
            }
            setViewHTML(html + `</div>`);
        }

        // --- LEVEL 2: RENDER DEPARTMENTS (CS, AI, MTH...) ---
        function renderDepartments(progName) {
            currentView = 'departments';
            stateProgram = progName; stateDept = null; stateBatch = null;
            updateNavState();

            const depts = dataHierarchy[progName].departments;
            let html = `<div class="grid-container">`;
            for (const [deptName, deptData] of Object.entries(depts)) {
                const batchCount = Object.keys(deptData.batches).length;
                const emptyClass = deptData.total_students === 0 ? 'empty-folder' : '';

                html += `
                    <div class="block-card ${emptyClass}" onclick="renderBatches('${progName}', '${deptName}')">
                        <div class="block-icon icon-dept"><i class="fas fa-network-wired"></i></div>
                        <div class="block-content">
                            <h3 class="block-title">${deptName}</h3>
                            <div class="block-meta"><i class="fas fa-calendar-check"></i> ${batchCount} Batches &bull; <i class="fas fa-user-graduate"></i> ${deptData.total_students} Students</div>
                        </div>
                    </div>
                `;
            }
            setViewHTML(html + `</div>`);
        }

        // --- LEVEL 3: RENDER BATCHES ('22, '23...) ---
        function renderBatches(progName, deptName) {
            currentView = 'batches';
            stateProgram = progName; stateDept = deptName; stateBatch = null;
            updateNavState();

            const batches = dataHierarchy[progName].departments[deptName].batches;
            
            // If there are no batches generated for this department yet
            if (Object.keys(batches).length === 0) {
                setViewHTML(`<div class="empty-state"><i class="fas fa-box-open"></i><p>No batches registered in ${deptName} (${progName}) yet.</p></div>`);
                return;
            }

            let html = `<div class="grid-container">`;
            for (const [batchName, students] of Object.entries(batches)) {
                html += `
                    <div class="block-card" onclick="renderStudents('${progName}', '${deptName}', '${batchName}')">
                        <div class="block-icon icon-batch"><i class="fas fa-users-rectangle"></i></div>
                        <div class="block-content">
                            <h3 class="block-title">${batchName}</h3>
                            <div class="block-meta"><i class="fas fa-address-card"></i> ${students.length} Enrolled Students</div>
                        </div>
                    </div>
                `;
            }
            setViewHTML(html + `</div>`);
        }

        // --- LEVEL 4: RENDER STUDENTS TABLE ---
        function renderStudents(progName, deptName, batchName) {
            currentView = 'students';
            stateProgram = progName; stateDept = deptName; stateBatch = batchName;
            updateNavState();

            const students = dataHierarchy[progName].departments[deptName].batches[batchName];
            setViewHTML(generateTableHTML(students, false));
        }

        // --- GLOBAL FUZZY SEARCH ---
        function handleSearch() {
            const term = document.getElementById('globalSearch').value.toLowerCase().trim();
            if(term === '') {
                if(stateBatch) renderStudents(stateProgram, stateDept, stateBatch);
                else if(stateDept) renderBatches(stateProgram, stateDept);
                else if(stateProgram) renderDepartments(stateProgram);
                else renderPrograms();
                return;
            }

            currentView = 'search';
            updateNavState();

            let matchedStudents = [];
            for (const prog in dataHierarchy) {
                for (const dept in dataHierarchy[prog].departments) {
                    for (const batch in dataHierarchy[prog].departments[dept].batches) {
                        dataHierarchy[prog].departments[dept].batches[batch].forEach(student => {
                            const searchStr = `${student.registration_no} ${student.full_name || ''} ${dept} ${prog} ${batch}`.toLowerCase();
                            if (searchStr.includes(term)) {
                                matchedStudents.push({...student, _context: `${prog} > ${dept} > ${batch}`});
                            }
                        });
                    }
                }
            }

            if(matchedStudents.length === 0) {
                setViewHTML(`<div class="empty-state"><i class="fas fa-search-minus"></i><p>No student data found matching "<b>${term}</b>"</p></div>`);
            } else {
                setViewHTML(generateTableHTML(matchedStudents, true));
            }
        }

        // --- HELPER: GENERATE DATA TABLE (REMOVED DELETE BUTTON) ---
        function generateTableHTML(studentsArray, isSearchResult = false) {
            if (studentsArray.length === 0) {
                return `<div class="empty-state"><i class="fas fa-user-slash"></i><p>No students found.</p></div>`;
            }

            let html = `
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Registration ID</th>
                            <th>Student Name</th>
                            ${isSearchResult ? '<th>Location Path</th>' : ''}
                            <th>Account Status</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            studentsArray.forEach(student => {
                const name = student.full_name || '<span class="text-muted">Profile Incomplete</span>';
                const status = student.status_badge || 'Active';
                const statusClass = status.toLowerCase().includes('defaulter') || status.toLowerCase().includes('probation') ? 'status-warning' : 'status-active';

                html += `
                    <tr>
                        <td class="fw-bold" style="color: var(--primary);">${student.registration_no}</td>
                        <td class="fw-bold">${name}</td>
                        ${isSearchResult ? `<td><span class="context-tag">${student._context}</span></td>` : ''}
                        <td><span class="status-badge ${statusClass}">&bull; ${status}</span></td>
                    </tr>
                `;
            });
            return html + `</tbody></table></div>`;
        }
    </script>
</body>
</html>