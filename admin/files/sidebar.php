<aside class="sidebar">
    <style>
        /* Sidebar Base */
        .sidebar {
            width: 260px;
            background-color: #1e293b;
            min-height: 100vh;
            color: #cbd5e1;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
        }

        .logo-section {
            padding: 24px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .main-logo {
            max-width: 140px;
            height: auto;
            margin-bottom: 15px;
        }

        .nav-title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.2px;
            color: #64748b;
            margin: 20px 0 10px 15px;
            text-align: left;
            text-transform: uppercase;
        }

        /* Nav Links Styling */
        .nav-links {
            display: flex;
            flex-direction: column;
            padding: 10px 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 14px;
            border-left: 3px solid transparent;
        }

        .nav-item i {
            width: 25px;
            margin-right: 12px;
            font-size: 16px;
            text-align: center;
        }

        .nav-item:hover {
            background-color: #334155;
            color: #fff;
        }

        .nav-item.active {
            background-color: #0f172a;
            color: #fff;
            border-left-color: #3b82f6;
        }

        /* Dropdown logic removed as it is no longer needed for a single item */
    </style>

    <div class="logo-section">
        <img src="../../Images/uw.webp" alt="navigation" class="main-logo">
        <h3 class="nav-title">Navigation</h3>
    </div>

    <nav class="nav-links">
        <a href="admin_dashboard.php" class="nav-item active">
            <i class="fas fa-user-circle"></i> <span>Profile</span>
        </a>

        <a href="dept_manager.php" class="nav-item ">
            <i class="fas fa-building"></i> <span>Department</span>
        </a>

        <a href="admin_feedback_view.php" class="nav-item"><i class="fas fa-desktop"></i> <span>Faculty
                Feedback</span></a>
        <a href="course_hub.php" class="nav-item"><i class="fas fa-book-open"></i> <span>My Course</span></a>
        <!-- <a href="#" class="nav-item"><i class="fas fa-download"></i> <span>Downloads</span></a> -->
        <!-- <a href="#" class="nav-item"><i class="fas fa-calendar-check"></i> <span>Leave Management</span></a> -->
        <!-- <a href="#" class="nav-item"><i class="fas fa-user-check"></i> <span>Attendance</span></a> -->
        <a href="student_service.php" class="nav-item"><i class="fas fa-user-check"></i> <span>Student
                Services</span></a>
        <!-- <a href="#" class="nav-item"><i class="fas fa-cog"></i> <span>Setting</span></a> -->
    </nav>
</aside>

<script>
    /* Note: The toggleDeptMenu function is no longer required
       since the Department section is now a direct link.
    */
</script>