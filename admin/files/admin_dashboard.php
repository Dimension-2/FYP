<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | University Portal</title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="main-wrapper">
        <?php include('sidebar.php'); ?>

        <div class="content-area">
            <?php include('header.php'); ?>

            <main class="dashboard-body">
                <style>
                    /* Container Spacing */
                    .dashboard-body {
                        padding: 30px;
                        animation: fadeIn 0.8s ease-out;
                    }

                    /* Modern Notification (Replaces the old marquee feel) */
                    .welcome-banner {
                        background: #fff;
                        border-left: 4px solid #3b82f6;
                        padding: 15px 25px;
                        border-radius: 10px;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
                        margin-bottom: 30px;
                        display: flex;
                        align-items: center;
                        overflow: hidden;
                    }

                    .welcome-banner marquee {
                        font-weight: 500;
                        color: #1e40af;
                    }

                    /* Profile Card - Professional Layout */
                    .profile-card {
                        background: white;
                        border-radius: 20px;
                        padding: 35px;
                        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
                        border: 1px solid #f1f5f9;
                    }

                    .profile-header {
                        display: flex;
                        align-items: center;
                        gap: 25px;
                        padding-bottom: 30px;
                        border-bottom: 1px solid #f1f5f9;
                    }

                    .profile-img {
                        width: 100px;
                        height: 100px;
                        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                        border-radius: 20px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 45px;
                        color: white;
                        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2);
                    }

                    .profile-title h2 {
                        font-size: 24px;
                        color: #0f172a;
                        margin: 0 0 5px 0;
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }

                    .status-badge {
                        background: #dcfce7;
                        color: #15803d;
                        font-size: 11px;
                        padding: 4px 12px;
                        border-radius: 20px;
                        font-weight: 700;
                        text-transform: uppercase;
                    }

                    .profile-title p {
                        color: #64748b;
                        margin: 0;
                        font-size: 14px;
                    }

                    /* Expanded Info Grid */
                    .info-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap: 20px;
                        margin-top: 30px;
                    }

                    .info-item {
                        background: #f8fafc;
                        padding: 15px 20px;
                        border-radius: 12px;
                        border: 1px solid #e2e8f0;
                        transition: all 0.3s ease;
                    }

                    .info-item:hover {
                        border-color: #3b82f6;
                        background: #fff;
                        transform: translateY(-2px);
                    }

                    .info-item strong {
                        display: block;
                        font-size: 11px;
                        color: #64748b;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                        margin-bottom: 5px;
                    }

                    .info-item span {
                        font-size: 15px;
                        color: #1e293b;
                        font-weight: 600;
                    }

                    /* Quick Stats Bar */
                    .stats-row {
                        display: grid;
                        grid-template-columns: repeat(3, 1fr);
                        gap: 20px;
                        margin-top: 25px;
                    }

                    .stat-card {
                        background: #ffffff;
                        padding: 20px;
                        border-radius: 15px;
                        text-align: center;
                        border: 1px solid #f1f5f9;
                    }

                    .stat-card i {
                        font-size: 20px;
                        color: #3b82f6;
                        margin-bottom: 10px;
                    }

                    .stat-card h4 {
                        font-size: 18px;
                        margin: 5px 0;
                        color: #0f172a;
                    }

                    .stat-card p {
                        font-size: 12px;
                        color: #64748b;
                        margin: 0;
                    }

                    @keyframes fadeIn {
                        from {
                            opacity: 0;
                            transform: translateY(10px);
                        }

                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                </style>

                <div class="welcome-banner">
                    <i class="fas fa-bullhorn" style="color: #3b82f6; margin-right: 15px;"></i>
                    <marquee behavior="scroll" direction="left">
                        Hello Admin, Welcome to the Control Panel. System check completed: All modules are operational.
                        Please review the faculty update deadline for the 2026 semester.
                    </marquee>
                </div>

                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-img">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="profile-title">
                            <h2>Hello, Admin <span class="status-badge">Verified Account</span></h2>
                            <p><i class="fas fa-id-badge"></i> System ID: 101010-ADMIN</p>
                            <p><i class="fas fa-envelope"></i> admin.portal@university.edu</p>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Primary Department</strong>
                            <span>Administration Unit</span>
                        </div>
                        <div class="info-item">
                            <strong>Assigned Designation</strong>
                            <span>Super Administrator</span>
                        </div>
                        <div class="info-item">
                            <strong>System Role</strong>
                            <span>Full Access Root</span>
                        </div>
                        <div class="info-item">
                            <strong>Joining Date</strong>
                            <span>January 12, 2024</span>
                        </div>
                    </div>
                </div>

                <div class="stats-row">
                    <div class="stat-card">
                        <i class="fas fa-clock"></i>
                        <h4>09:00 AM</h4>
                        <p>Last Login Time</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-shield-alt"></i>
                        <h4>Secure</h4>
                        <p>Account Security</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-tasks"></i>
                        <h4>12 Pending</h4>
                        <p>Faculty Approvals</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function toggleDropdown() {
            document.getElementById("deptDropdown").classList.toggle("show");
        }
    </script>
</body>

</html>