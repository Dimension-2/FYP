<?php
// Detect the current filename (e.g., profile.php or attendance.php)
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="d-flex flex-column flex-shrink-0 p-3 text-white sidebar" id="sidebar">
    <div class="d-flex align-items-center justify-content-between mb-4 header-container">
        <div class="logo-wrapper">
            <img src="images/uw.webp" alt="Logo" class="img-fluid logo-img" style="max-width: 140px;">
        </div>
        <button id="toggleBtn" class="btn text-white p-0 border-0 shadow-none">
            <i class="bi bi-list fs-3"></i>
        </button>
    </div>

    <p class="text-uppercase small fw-bold text-secondary px-3 nav-label">Navigation</p>
    
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="profile.php" class="nav-link <?php echo ($current_page == 'profile.php') ? 'active' : 'text-white'; ?>">
                <i class="bi bi-person-fill"></i> <span class="link-text ms-2">Profile</span>
            </a>
        </li>
        <li>
            <a href="attendance.php" class="nav-link <?php echo ($current_page == 'attendance.php') ? 'active' : 'text-white'; ?>">
                <i class="bi bi-file-earmark-text"></i> <span class="link-text ms-2">Attendance</span>
            </a>
        </li>
      <li>
    <a href="faculty.php" class="nav-link <?php echo ($current_page == 'faculty.php') ? 'active' : 'text-white'; ?>">
        <i class="bi bi-chat-right-dots"></i> <span class="link-text ms-2">Faculty Feedback</span>
    </a>
</li>  
<!-- <li><a href="datesheet.php" class="nav-link text-white"><i class="bi bi-calendar3"></i> <span class="link-text ms-2">Datesheet</span></a></li> -->
        <li><a href="gradebook.php" class="nav-link text-white"><i class="bi bi-book"></i> <span class="link-text ms-2">Grade Book</span></a></li>
        <li><a href="feevoucher.php" class="nav-link text-white"><i class="bi bi-gear"></i> <span class="link-text ms-2">Fee Voucher</span></a></li>
        <li><a href="hostel_sec_voucher.php" class="nav-link text-white"><i class="bi bi-gear"></i> <span class="link-text ms-2">Hostel Sec Voucher</span></a></li>
        <li><a href="hostel_mess_voucher.php" class="nav-link text-white"><i class="bi bi-gear"></i> <span class="link-text ms-2">Hostel Mess Voucher</span></a></li>
        <li><a href="research_review.php" class="nav-link text-white"><i class="bi bi-pencil-square"></i> <span class="link-text ms-2">Research Review</span></a></li>
        <li><a href="transport_voucher.php" class="nav-link text-white"><i class="bi bi-gear"></i> <span class="link-text ms-2">Transport Voucher</span></a></li>
        <li><a href="complaints.php" class="nav-link text-white"><i class="bi bi-files"></i> <span class="link-text ms-2">Complaints</span></a></li>
        <li><a href="student_survey.php" class="nav-link text-white">
            <i class="bi bi-pencil-square"></i> <span class="link-text ms-2">HEC Survey</span> <span class="badge bg-danger ms-1 nav-badge" style="font-size: 0.6rem;">NEW</span>
        </a></li>
        <li><a href="semester_result.php" class="nav-link text-white"><i class="bi bi-layout-sidebar-inset"></i> <span class="link-text ms-2">Semester Result</span></a></li>
        <li><a href="course.php" class="nav-link text-white"><i class="bi bi-folder2"></i> <span class="link-text ms-2">Semester Courses</span></a></li>        
        <li><a href="#" class="nav-link text-white"><i class="bi bi-gear"></i> <span class="link-text ms-2">Settings</span></a></li>
    </ul>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const toggleBtn = document.getElementById('toggleBtn');
    const sidebar = document.getElementById('sidebar');

    if(toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            // This is the "Switch" that tells the rest of the page to stretch
            document.body.classList.toggle('sidebar-closed');
        });
    }
});
</script>