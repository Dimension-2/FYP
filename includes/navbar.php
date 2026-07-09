<?php
// Detect the current running filename to dynamically drive active state highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="d-flex flex-column flex-shrink-0 p-3 text-white sidebar" id="sidebar">
    <style>
        /* Modern Font Integration */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        /* High-End Sidebar Container Base */
        .sidebar {
            width: 270px;
            background: #1e293b !important; /* Deep Slate Professional Palette */
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 100;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), padding 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-x: hidden;
        }

        /* Brand & Logo Header Layout */
        .header-container {
            padding: 10px 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            margin-bottom: 25px !important;
        }

        .logo-wrapper {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .logo-wrapper:hover {
            transform: scale(1.04);
        }

        .logo-img {
            max-width: 130px;
            filter: drop-shadow(0px 4px 10px rgba(0, 0, 0, 0.25));
        }

        /* Sidebar Toggle Button */
        #toggleBtn {
            background: transparent;
            border: none;
            color: #94a3b8 !important;
            transition: color 0.2s ease, transform 0.3s ease;
        }

        #toggleBtn:hover {
            color: #3b82f6 !important;
            transform: scale(1.1);
        }

        /* Group Navigation Header Title */
        .nav-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: #64748b !important;
            margin-bottom: 12px;
            text-transform: uppercase;
            transition: opacity 0.2s ease;
        }

        /* Nav List Wrapper Configuration */
        .nav-pills {
            gap: 5px;
            padding-left: 0;
            list-style: none;
        }

        /* High-End Interactive Nav Link Elements */
        .nav-pills .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 10px;
            color: #94a3b8 !important;
            position: relative;
            overflow: hidden;
            background: transparent;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Icon Micro-Animations */
        .nav-pills .nav-link i {
            font-size: 17px;
            width: 24px;
            text-align: center;
            color: #64748b;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Text Element Wrapper Movement Styling */
        .nav-pills .nav-link .link-text {
            display: inline-block;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease;
        }

        /* Sharp Left-Accent Visual Indicator Snap Bar */
        .nav-pills .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            height: 60%;
            width: 4px;
            background-color: #3b82f6;
            border-radius: 0 4px 4px 0;
            transform: scaleY(0);
            opacity: 0;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease;
        }

        /* High-End Trailing Pointed Arrow Chevron (Right-Side Indicator) */
        .nav-pills .nav-link::after {
            content: '\F285'; /* Bootstrap Icons code for chevron-right */
            font-family: 'bootstrap-icons';
            position: absolute;
            right: 18px;
            font-size: 11px;
            color: #3b82f6;
            opacity: 0;
            transform: translateX(-12px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Premium Hover States */
        .nav-pills .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.03) !important;
            color: #f1f5f9 !important;
        }

        .nav-pills .nav-link:hover i {
            color: #3b82f6;
            transform: scale(1.12);
        }

        .nav-pills .nav-link:hover .link-text {
            transform: translateX(4px);
        }

        .nav-pills .nav-link:hover::after {
            opacity: 0.4;
            transform: translateX(-3px);
        }

        /* Ultimate Premium Active Page Highlights */
        .nav-pills .nav-link.active {
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.09) 0%, rgba(59, 130, 246, 0.01) 100%) !important;
            color: #3b82f6 !important;
            font-weight: 600;
            box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.15) !important;
        }

        .nav-pills .nav-link.active i {
            color: #3b82f6;
            filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.45));
        }

        .nav-pills .nav-link.active .link-text {
            transform: translateX(4px);
        }

        /* Make active pointers structure slide and stick into layout view */
        .nav-pills .nav-link.active::before {
            transform: scaleY(1);
            opacity: 1;
        }

        .nav-pills .nav-link.active::after {
            opacity: 1;
            transform: translateX(0);
        }

        /* Notification Badges Custom Formatting */
        .nav-badge {
            font-size: 0.65rem !important;
            padding: 3px 6px !important;
            border-radius: 6px;
            font-weight: 700;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 10px rgba(220, 38, 38, 0.3);
        }

        /* Elegant Collapse Mechanics Supporting Your Structural Script */
        .sidebar.collapsed {
            width: 78px;
            padding: 15px 10px !important;
        }

        .sidebar.collapsed .logo-wrapper,
        .sidebar.collapsed .nav-label,
        .sidebar.collapsed .link-text,
        .sidebar.collapsed .nav-badge,
        .sidebar.collapsed .nav-link::after {
            opacity: 0;
            pointer-events: none;
            width: 0;
            display: none !important;
        }

        .sidebar.collapsed .header-container {
            justify-content: center !important;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 12px 0;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0 !important;
            font-size: 19px;
        }
    </style>

    <div class="d-flex align-items-center justify-content-between mb-4 header-container">
        <div class="logo-wrapper">
            <img src="images/uw.webp" alt="Logo" class="img-fluid logo-img">
        </div>
        <button id="toggleBtn" class="btn text-white p-0 border-0 shadow-none">
            <i class="bi bi-list fs-3"></i>
        </button>
    </div>

    <p class="text-uppercase small fw-bold text-secondary px-3 nav-label">Navigation</p>
    
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="profile.php" class="nav-link <?= ($current_page == 'profile.php') ? 'active' : ''; ?>">
                <i class="bi bi-person-fill"></i> <span class="link-text ms-2">Profile</span>
            </a>
        </li>
        <li>
            <a href="attendance.php" class="nav-link <?= ($current_page == 'attendance.php') ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-text"></i> <span class="link-text ms-2">Attendance</span>
            </a>
        </li>
        <li>
            <a href="faculty.php" class="nav-link <?= ($current_page == 'faculty.php') ? 'active' : ''; ?>">
                <i class="bi bi-chat-right-dots"></i> <span class="link-text ms-2">Faculty Feedback</span>
            </a>
        </li>  
        <li>
            <a href="gradebook.php" class="nav-link <?= ($current_page == 'gradebook.php') ? 'active' : ''; ?>">
                <i class="bi bi-book"></i> <span class="link-text ms-2">Grade Book</span>
            </a>
        </li>
        <li>
            <a href="student_fee_voucher.php" class="nav-link <?= ($current_page == 'student_fee_voucher.php') ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i> <span class="link-text ms-2">STU Fee Voucher</span>
            </a>
        </li>
        <li>
            <a href="hostel_sec_voucher.php" class="nav-link <?= ($current_page == 'hostel_sec_voucher.php') ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i> <span class="link-text ms-2">Hostel Sec Voucher</span>
            </a>
        </li>
        <li>
            <a href="research_review.php" class="nav-link <?= ($current_page == 'research_review.php') ? 'active' : ''; ?>">
                <i class="bi bi-pencil-square"></i> <span class="link-text ms-2">Research Review</span>
            </a>
        </li>
        <li>
            <a href="transport_voucher.php" class="nav-link <?= ($current_page == 'transport_voucher.php') ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i> <span class="link-text ms-2">Transport Voucher</span>
            </a>
        </li>
        <li>
            <a href="hostel_apply.php" class="nav-link <?= ($current_page == 'hostel_apply.php') ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i> <span class="link-text ms-2">Hostel Apply</span>
            </a>
        </li>
        <li>
            <a href="transport_apply.php" class="nav-link <?= ($current_page == 'transport_apply.php') ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i> <span class="link-text ms-2">Transport Apply</span>
            </a>
        </li>
        <li>
            <a href="complaints.php" class="nav-link <?= ($current_page == 'complaints.php') ? 'active' : ''; ?>">
                <i class="bi bi-files"></i> <span class="link-text ms-2">Complaints</span>
            </a>
        </li>
        <li>
            <a href="student_survey.php" class="nav-link <?= ($current_page == 'student_survey.php') ? 'active' : ''; ?>">
                <i class="bi bi-pencil-square"></i> 
                <span class="link-text ms-2">HEC Survey</span> 
                <span class="badge bg-danger ms-1 nav-badge">NEW</span>
            </a>
        </li>
        <li>
            <a href="semester_result.php" class="nav-link <?= ($current_page == 'semester_result.php') ? 'active' : ''; ?>">
                <i class="bi bi-layout-sidebar-inset"></i> <span class="link-text ms-2">Semester Result</span>
            </a>
        </li>
        <li>
            <a href="course.php" class="nav-link <?= ($current_page == 'course.php') ? 'active' : ''; ?>">
                <i class="bi bi-folder2"></i> <span class="link-text ms-2">Semester Courses</span>
            </a>
        </li>        
        <li>
            <a href="#" class="nav-link <?= ($current_page == '#') ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i> <span class="link-text ms-2">Settings</span>
            </a>
        </li>
    </ul>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const toggleBtn = document.getElementById('toggleBtn');
    const sidebar = document.getElementById('sidebar');

    if(toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            // Tells the parent wrapper content viewport area to adapt layout dimensions dynamically
            document.body.classList.toggle('sidebar-closed');
        });
    }
});
</script>