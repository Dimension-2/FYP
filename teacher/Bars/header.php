<nav class="navbar navbar-expand-lg navbar-light bg-white py-3 px-4 shadow-sm custom-header">
    <div class="container-fluid">
        <button class="btn btn-link text-dark me-3" id="menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <span class="text-muted">Welcome, <strong class="text-primary"><?php echo strtoupper($data['full_name']); ?></strong></span>
        
        <div class="ms-auto d-flex align-items-center">
            <div class="dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                    <img src="../../uploads/teachers/default.png" class="rounded-circle me-2 border" width="35" height="35">
                    <span class="d-none d-md-inline">My Account</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li><a class="dropdown-item py-2" href="#"><i class="fas fa-user-cog me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 text-danger" href="#"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>