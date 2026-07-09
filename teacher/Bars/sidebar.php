<?php
// Get the current file name (e.g., 'profile.php')
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
        /* Opt-in to native browser multi-page view transitions */
        @view-transition {
                navigation: auto;
        }

        /* Base styles for sidebar items */
        .list-group-item {
                position: relative;
                z-index: 1;
                transition: color 0.25s ease, background-color 0.25s ease;
                border: none !important;
                border-radius: 8px !important;
                margin-bottom: 4px;
                color: rgba(255, 255, 255, 0.75) !important;
                background: transparent !important;
        }

        /* Hover effect before selecting */
        .list-group-item:hover:not(.active) {
                background-color: rgba(255, 255, 255, 0.05) !important;
                color: #fff !important;
        }

        /* Perfect active state with Glide effect */
        .list-group-item.active {
                background-color: #0d6efd !important;
                /* Premium Blue */
                color: #fff !important;
                /* The magic keyword that tells the browser to glide this background element */
                view-transition-name: active-sidebar-glide;
        }

        /* Customizing the glide physics for a buttery smooth app feel */
        ::view-transition-group(active-sidebar-glide) {
                animation-duration: 0.38s;
                animation-timing-function: cubic-bezier(0.25, 1, 0.5, 1);
        }
</style>

<div class="bg-dark-sidebar" id="sidebar-wrapper">
        <div class="sidebar-heading text-center py-4">
                <div class="logo-container">
                        <img src="../../images/uw.webp"
                                alt="University of Wah herald style emblem with text Quality Education for All and 2005"
                                class="img-fluid px-4 mb-2">
                </div>
                <hr class="mx-4 text-secondary">
                <p class="text-muted small uppercase letter-spacing mb-0 nav-label">Navigation</p>
        </div>
        <div class="list-group list-group-flush px-3">
                <a href="profile.php"
                        class="list-group-item <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>"
                        title="Profile">
                        <i class="fas fa-user-circle"></i> <span class="nav-text">Profile</span>
                </a>
                <a href="view_my_courses.php"
                        class="list-group-item <?php echo ($current_page == 'view_my_courses.php') ? 'active' : ''; ?>"
                        title="My Course">
                        <i class="fas fa-book-open"></i> <span class="nav-text">My Course</span>
                </a>
                <a href="view_research.php"
                        class="list-group-item <?php echo ($current_page == 'view_research.php') ? 'active' : ''; ?>"
                        title="Research Review">
                        <i class="fas fa-microscope"></i> <span class="nav-text">Research Review</span>
                </a>
                <a href="manage_attendance.php"
                        class="list-group-item <?php echo ($current_page == 'manage_attendance.php') ? 'active' : ''; ?>"
                        title="Attendance">
                        <i class="fas fa-user-check"></i> <span class="nav-text">Attendance</span>
                </a>
                <a href="manage_grades.php"
                        class="list-group-item <?php echo ($current_page == 'manage_grades.php') ? 'active' : ''; ?>"
                        title="Manage Grades">
                        <i class="fas fa-poll-h"></i> <span class="nav-text">Manage Grades</span>
                </a>
        </div>
</div>