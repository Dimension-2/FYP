<?php
include('db_config.php');

// 1. PRE-INITIALIZE THE COMPLETE FOLDER STRUCTURE
// This ensures all Departments exist even with 0 teachers registered.
$allowed_departments = [
    'Computer Science', 'Artificial Intelligence', 'Cyber Security', 
    'Data Science', 'Physics', 'Mathematics', 'Psychology', 'English', 'Other Departments'
];

$hierarchy = [];
foreach ($allowed_departments as $dept) {
    $hierarchy[$dept] = ['total_teachers' => 0, 'teachers' => []];
}

// Department Name Normalizer (Maps abbreviations to pre-initialized full names)
function normalizeDepartment($raw_dept) {
    if (empty($raw_dept)) return 'Other Departments';
    
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
    
    // Check if it matches an abbreviation, or if it's already a full name
    if (isset($map[$code])) {
        return $map[$code];
    }
    
    // Case-insensitive search in allowed departments
    foreach ($GLOBALS['allowed_departments'] as $allowed) {
        if (strcasecmp($raw_dept, $allowed) == 0) {
            return $allowed;
        }
    }
    
    return 'Other Departments';
}

// 2. FETCH AND AGGREGATE DATA
// We fetch users and link to teachers. We filter out the long student registration pattern.
$query = "SELECT u.id, u.registration_no, t.full_name, t.department, t.email, t.phone 
          FROM users u 
          JOIN teachers t ON u.registration_no = t.employee_id 
          ORDER BY t.full_name ASC";

$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Smart normalize the department
        $dept = normalizeDepartment($row['department']);

        // Ensure the dynamic key exists just in case
        if (!isset($hierarchy[$dept])) {
            $hierarchy[$dept] = ['total_teachers' => 0, 'teachers' => []];
        }

        // Assign Teacher
        $hierarchy[$dept]['teachers'][] = $row;
        
        // Increment Counter
        $hierarchy[$dept]['total_teachers']++;
    }
}

$json_data = json_encode($hierarchy);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Directory | Academic Hub</title>
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
            --accent: #8b5cf6; /* Distinct purple accent for Faculty */
            
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
        .breadcrumb-item:hover { color: var(--accent); background: #f3f0ff; }
        .breadcrumb-item.active { color: var(--primary); font-weight: 700; pointer-events: none; background: #e2e8f0; }
        .breadcrumb-separator { color: var(--border-hover); font-size: 11px; }

        /* Search Bar */
        .search-wrapper { position: relative; width: 400px; }
        .search-wrapper input {
            width: 100%; padding: 12px 16px 12px 45px; border: 2px solid var(--border); border-radius: var(--radius-lg);
            font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 500; color: var(--text-main);
            background: var(--bg-surface); box-sizing: border-box; transition: all 0.2s; box-shadow: var(--shadow-sm);
        }
        .search-wrapper input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1); }
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
        
        /* Visually dim folders with 0 teachers to make active ones pop */
        .block-card.empty-folder { opacity: 0.65; background: #f8fafc; }
        .block-card.empty-folder:hover { opacity: 1; background: var(--bg-surface); }

        .block-icon { width: 54px; height: 54px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
        
        .icon-dept { background: #f3f0ff; color: #8b5cf6; } /* Purple theme for faculty */

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
        
        .context-tag { font-size: 12px; color: var(--text-muted); background: #f1f5f9; padding: 4px 8px; border-radius: 6px; border: 1px solid var(--border); }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-state i { font-size: 40px; margin-bottom: 15px; opacity: 0.5; }

        /* Teacher info styles */
        .contact-info { display: flex; flex-direction: column; gap: 4px; }
        .contact-info small { color: var(--text-muted); font-size: 13px; display: flex; align-items: center; gap: 6px; }
        .contact-info i { width: 14px; color: var(--border-hover); }
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
                        <h1 class="page-title"><i class="fas fa-chalkboard-teacher" style="color: var(--primary);"></i> Faculty Records Directory</h1>
                        
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
                        <input type="text" id="globalSearch" placeholder="Global Search (Name, ID, Dept...)" onkeyup="handleSearch()">
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
        let currentView = 'departments'; // 'departments', 'teachers', 'search'
        let stateDept = null;

        const mainView = document.getElementById('main-view');
        const breadcrumb = document.getElementById('breadcrumb');
        const btnBack = document.getElementById('btnBack');

        // Initial Load
        renderDepartments();

        // --- BACK BUTTON LOGIC ---
        function goBack() {
            if (currentView === 'teachers' || currentView === 'search') {
                renderDepartments();
            }
        }

        function setViewHTML(html) {
            mainView.innerHTML = `<div class="view-animate">${html}</div>`;
        }

        // --- BREADCRUMB SYSTEM ---
        function updateNavState() {
            btnBack.disabled = (currentView === 'departments'); // Disable back button at root
            
            let html = `<span class="breadcrumb-item ${currentView === 'departments' ? 'active' : ''}" onclick="renderDepartments()"><i class="fas fa-sitemap"></i> Departments</span>`;
            
            if (stateDept) {
                html += `<i class="fas fa-chevron-right breadcrumb-separator"></i>`;
                html += `<span class="breadcrumb-item active">${stateDept}</span>`;
            }
            if (currentView === 'search') {
                html = `<span class="breadcrumb-item" onclick="renderDepartments()"><i class="fas fa-sitemap"></i> Departments</span> <i class="fas fa-chevron-right breadcrumb-separator"></i> <span class="breadcrumb-item active"><i class="fas fa-search"></i> Search Results</span>`;
            }
            breadcrumb.innerHTML = html;
        }

        // --- LEVEL 1: RENDER DEPARTMENTS (CS, AI, MTH...) ---
        function renderDepartments() {
            currentView = 'departments';
            stateDept = null;
            document.getElementById('globalSearch').value = '';
            updateNavState();

            let html = `<div class="grid-container">`;
            for (const [deptName, deptData] of Object.entries(dataHierarchy)) {
                // If it's the "Other Departments" folder and it's empty, skip rendering it for a cleaner look
                if (deptName === 'Other Departments' && deptData.total_teachers === 0) continue;

                const emptyClass = deptData.total_teachers === 0 ? 'empty-folder' : '';

                html += `
                    <div class="block-card ${emptyClass}" onclick="renderTeachers('${deptName}')">
                        <div class="block-icon icon-dept"><i class="fas fa-building"></i></div>
                        <div class="block-content">
                            <h3 class="block-title">${deptName}</h3>
                            <div class="block-meta"><i class="fas fa-user-tie"></i> ${deptData.total_teachers} Faculty Members</div>
                        </div>
                    </div>
                `;
            }
            setViewHTML(html + `</div>`);
        }

        // --- LEVEL 2: RENDER TEACHERS TABLE ---
        function renderTeachers(deptName) {
            currentView = 'teachers';
            stateDept = deptName;
            updateNavState();

            const teachers = dataHierarchy[deptName].teachers;
            setViewHTML(generateTableHTML(teachers, false));
        }

        // --- GLOBAL FUZZY SEARCH ---
        function handleSearch() {
            const term = document.getElementById('globalSearch').value.toLowerCase().trim();
            if(term === '') {
                if(stateDept) renderTeachers(stateDept);
                else renderDepartments();
                return;
            }

            currentView = 'search';
            updateNavState();

            let matchedTeachers = [];
            for (const dept in dataHierarchy) {
                dataHierarchy[dept].teachers.forEach(teacher => {
                    const searchStr = `${teacher.registration_no} ${teacher.full_name || ''} ${teacher.email || ''} ${dept}`.toLowerCase();
                    if (searchStr.includes(term)) {
                        matchedTeachers.push({...teacher, _context: `${dept}`});
                    }
                });
            }

            if(matchedTeachers.length === 0) {
                setViewHTML(`<div class="empty-state"><i class="fas fa-search-minus"></i><p>No faculty data found matching "<b>${term}</b>"</p></div>`);
            } else {
                setViewHTML(generateTableHTML(matchedTeachers, true));
            }
        }

        // --- HELPER: GENERATE DATA TABLE (REMOVED DELETE BUTTON) ---
        function generateTableHTML(teachersArray, isSearchResult = false) {
            if (teachersArray.length === 0) {
                return `<div class="empty-state"><i class="fas fa-user-slash"></i><p>No faculty members assigned to this department yet.</p></div>`;
            }

            let html = `
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Faculty Name</th>
                            <th>Contact Information</th>
                            ${isSearchResult ? '<th>Department</th>' : ''}
                        </tr>
                    </thead>
                    <tbody>
            `;

            teachersArray.forEach(teacher => {
                const name = teacher.full_name || '<span class="text-muted">Profile Incomplete</span>';
                const email = teacher.email || 'N/A';
                const phone = teacher.phone || 'N/A';

                html += `
                    <tr>
                        <td class="fw-bold" style="color: var(--primary);">${teacher.registration_no}</td>
                        <td class="fw-bold">${name}</td>
                        <td>
                            <div class="contact-info">
                                <small><i class="fas fa-envelope"></i> ${email}</small>
                                <small><i class="fas fa-phone-alt"></i> ${phone}</small>
                            </div>
                        </td>
                        ${isSearchResult ? `<td><span class="context-tag">${teacher._context}</span></td>` : ''}
                    </tr>
                `;
            });
            return html + `</tbody></table></div>`;
        }
    </script>
</body>
</html>