
<div class="top-header-nav">
    <div class="header-inner">
        <div class="breadcrumb-area">
            <span class="breadcrumb-label">YOU ARE HERE:</span>
            <a href="dashboard.php" class="breadcrumb-item">Home</a>
            
            <?php if(isset($page_title) && $page_title !== 'Home'): ?>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-active"><?php echo htmlspecialchars($page_title); ?></span>
            <?php endif; ?>
        </div>
        <div class="user-area d-flex align-items-center">
    <div class="notification-dropdown me-3">
        <button class="btn-icon position-relative" id="bellBtn">
            <i class="bi bi-bell fs-5 text-secondary"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                2
            </span>
        </button>
        <div class="dropdown-content notification-menu" id="bellMenu">
            <h6 class="dropdown-header border-bottom pb-2">Notifications</h6>
            <a href="#" class="notification-item">
                <small class="d-block fw-bold">Pending Evaluation</small>
                <small class="text-muted">CS-451 needs your feedback.</small>
            </a>
            <a href="#" class="notification-item">
                <small class="d-block fw-bold">System Update</small>
                <small class="text-muted">New semester courses added.</small>
            </a>
        </div>
    </div>

    <span class="welcome-msg">Welcome <span class="user-name"><?php echo htmlspecialchars($student['full_name'] ?? 'Student'); ?>.</span></span>
</div>
        <div class="user-area">
            <span class="welcome-msg">Welcome<span class="user-name"><?php echo htmlspecialchars($student['full_name'] ?? 'Student'); ?>.</span></span>
            <div class="account-dropdown">
                <button class="account-btn" id="accountBtn">
                    <i class="bi bi-person-circle fs-5"></i> My Account <i class="bi bi-chevron-down small"></i>
                </button>
                <div class="dropdown-content" id="accountMenu">
                    <a href="profile.php"><i class="bi bi-person me-2"></i> Profile</a>
                    <a href="settings.php"><i class="bi bi-gear me-2"></i> Settings</a>
                    <hr>
                    <a href="logout.php" class="text-danger fw-bold"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Dropdown Toggle Script
    const accBtn = document.getElementById('accountBtn');
    const accMenu = document.getElementById('accountMenu');

    accBtn.onclick = function(e) {
        accMenu.classList.toggle('show');
        e.stopPropagation();
    }

    window.onclick = function() {
        if (accMenu.classList.contains('show')) {
            accMenu.classList.remove('show');
        }
    }
</script>