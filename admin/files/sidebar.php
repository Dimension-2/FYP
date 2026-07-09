<?php
// Dynamically track the current active page file name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <style>
        /* Modern Font Import */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        /* Sidebar Base Container */
        .sidebar {
            width: 270px;
            background: #1e293b;
            /* Deep slate professional tone */
            min-height: 100vh;
            color: #cbd5e1;
            font-family: 'Inter', sans-serif;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 100;
        }

        /* Branding Section */
        .logo-section {
            padding: 30px 24px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            background: rgba(15, 23, 42, 0.2);
        }

        .main-logo {
            max-width: 110px;
            height: auto;
            margin-bottom: 14px;
            filter: drop-shadow(0px 4px 8px rgba(0, 0, 0, 0.2));
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .logo-section:hover .main-logo {
            transform: scale(1.05) rotate(2deg);
        }

        .nav-title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: #64748b;
            margin: 0;
            text-transform: uppercase;
        }

        /* Nav Items Layout */
        .nav-links {
            display: flex;
            flex-direction: column;
            padding: 20px 12px;
            gap: 6px;
        }

        /* Next-Level Interactive Navigation Items */
        .nav-item {
            display: flex;
            align-items: center;
            padding: 13px 18px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Icon Customization */
        .nav-item i {
            width: 28px;
            margin-right: 12px;
            font-size: 16px;
            text-align: center;
            color: #64748b;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Text Element Movement wrapper */
        .nav-item span {
            display: inline-block;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Subtle indicator bar on the left inside the item container */
        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 15%;
            height: 70%;
            width: 4px;
            background-color: #3b82f6;
            border-radius: 0 4px 4px 0;
            transform: scaleY(0);
            opacity: 0;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease;
        }

        /* High-End Pointed Arrow Indicator on the Right */
        .nav-item::after {
            content: '\f054';
            /* FontAwesome chevron right */
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 18px;
            font-size: 10px;
            color: #3b82f6;
            opacity: 0;
            transform: translateX(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Subtle Interactive Hover Highlight State */
        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.03);
            color: #f1f5f9;
        }

        .nav-item:hover i {
            color: #3b82f6;
            transform: scale(1.1);
        }

        .nav-item:hover span {
            transform: translateX(4px);
        }

        .nav-item:hover::after {
            opacity: 0.4;
            transform: translateX(-2px);
        }

        /* Premium Current State (Active Page) Highlight */
        .nav-item.active {
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.08) 0%, rgba(59, 130, 246, 0.01) 100%);
            color: #3b82f6;
            font-weight: 600;
            box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.12);
        }

        .nav-item.active i {
            color: #3b82f6;
            filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.4));
        }

        .nav-item.active span {
            transform: translateX(4px);
        }

        /* Lock structural transformations for active layout */
        .nav-item.active::before {
            transform: scaleY(1);
            opacity: 1;
        }

        /* Locking pointed right arrow in place for the active tab */
        .nav-item.active::after {
            opacity: 1;
            transform: translateX(0);
        }
    </style>

    <div class="logo-section">
        <img src="../../Images/uw.webp" alt="navigation" class="main-logo">
        <h3 class="nav-title">Navigation</h3>
    </div>

    <nav class="nav-links">
        <a href="admin_dashboard.php" class="nav-item <?= ($current_page == 'admin_dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-user-circle"></i> <span>Profile</span>
        </a>

        <a href="dept_manager.php" class="nav-item <?= ($current_page == 'dept_manager.php') ? 'active' : '' ?>">
            <i class="fas fa-building"></i> <span>Department</span>
        </a>

        <a href="admin_feedback_view.php"
            class="nav-item <?= ($current_page == 'admin_feedback_view.php') ? 'active' : '' ?>">
            <i class="fas fa-desktop"></i> <span>Faculty Feedback</span>
        </a>

        <a href="course_hub.php" class="nav-item <?= ($current_page == 'course_hub.php') ? 'active' : '' ?>">
            <i class="fas fa-book-open"></i> <span>My Course</span>
        </a>

        <a href="student_service.php" class="nav-item <?= ($current_page == 'student_service.php') ? 'active' : '' ?>">
            <i class="fas fa-user-check"></i> <span>Student Services</span>
        </a>

        <a href="admin_hostel_finance.php"
            class="nav-item <?= ($current_page == 'admin_hostel_finance.php') ? 'active' : '' ?>">
            <i class="fas fa-cog"></i> <span>Hostel Finance</span>
        </a>

        <a href="admin_transport_finance.php"
            class="nav-item <?= ($current_page == 'admin_transport_finance.php') ? 'active' : '' ?>">
            <i class="fas fa-utensils"></i> <span>Transport Finance</span>
        </a>

        <a href="admin_semester_vouchers.php"
            class="nav-item <?= ($current_page == 'admin_semester_vouchers.php') ? 'active' : '' ?>">
            <i class="fas fa-utensils"></i> <span>Semester Finance Management</span>
        </a>
    </nav>
</aside>