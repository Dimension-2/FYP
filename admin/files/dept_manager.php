<?php
// Get department name from URL (e.g., dept_manager.php?dept=Computer Science)
$dept = isset($_GET['dept']) ? $_GET['dept'] : 'Department';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $dept; ?> | Management</title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .dept-container {
            padding: 30px;
            animation: fadeIn 0.5s ease;
        }

        .dept-header {
            margin-bottom: 30px;
        }

        .dept-header h1 {
            font-size: 28px;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .dept-header h1 span {
            color: #3b82f6;
        }

        /* The 11 Boxes Grid */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .action-box {
            background: #fff;
            padding: 25px;
            border-radius: 16px;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .action-box:hover {
            transform: translateY(-5px);
            border-color: #3b82f6;
            box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.1);
        }

        .action-box i {
            font-size: 32px;
            margin-bottom: 15px;
            color: #3b82f6;
            transition: 0.3s;
        }

        .action-box h3 {
            font-size: 16px;
            color: #1e293b;
            margin: 0;
        }

        .action-box p {
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
        }

        /* Student Service (Full Width Box) */
        .full-width {
            grid-column: 1 / -1;
            flex-direction: row;
            gap: 20px;
            justify-content: flex-start;
            padding: 30px;
        }

        /* Specific Icon Colors for differentiation */
        .box-finance i {
            color: #10b981;
        }

        /* Green */
        .box-data i {
            color: #f59e0b;
        }

        /* Orange */
        .box-hec i {
            color: #8b5cf6;
        }

        /* Purple */

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
</head>

<body>

    <div class="main-wrapper">
        <?php include('sidebar.php'); ?>

        <div class="content-area">
            <?php include('header.php'); ?>

            <main class="dept-container">
                <div class="dept-header">
                    <h1><i class="fas fa-university"></i> <span><?php echo $dept; ?></span> Hub</h1>
                    <p style="color: #64748b;">Manage students, faculty, and finances for this department.</p>
                </div>

                <div class="action-grid">
                    <a href="register_student.php?dept=<?php echo $dept; ?>" class="action-box">
                        <i class="fas fa-user-plus"></i>
                        <h3>Register Student</h3>
                        <p>Add new entry to database</p>
                    </a>

                    <a href="register_teacher.php?dept=<?php echo $dept; ?>" class="action-box">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <h3>Register Teacher</h3>
                        <p>Assign new teacher</p>
                    </a>

                    <a href="student_detail.php?dept=<?php echo $dept; ?>" class="action-box">
                        <i class="fas fa-user-graduate"></i>
                        <h3>Student Details</h3>
                        <p>View & Manage Records</p>
                    </a>

                    <a href="teacher_detail.php?dept=<?php echo $dept; ?>" class="action-box">
                        <i class="fas fa-address-book"></i>
                        <h3>Teacher Details</h3>
                        <p>Faculty Profiles</p>
                    </a>

                    <!-- <a href="finance_student.php?dept=<?php echo $dept; ?>" class="action-box box-finance">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <h3>Student Finance</h3>
                        <p>Fee & Ledger</p>
                    </a>

                    <a href="finance_teacher.php?dept=<?php echo $dept; ?>" class="action-box box-finance">
                        <i class="fas fa-wallet"></i>
                        <h3>Teacher Finance</h3>
                        <p>Salary & Payroll</p>
                    </a>

                    <a href="finance_hostel.php?dept=<?php echo $dept; ?>" class="action-box box-finance">
                        <i class="fas fa-hotel"></i>
                        <h3>Hostel Finance</h3>
                        <p>Accommodation dues</p>
                    </a>

                    <a href="finance_transport.php?dept=<?php echo $dept; ?>" class="action-box box-finance">
                        <i class="fas fa-bus"></i>
                        <h3>Transport Finance</h3>
                        <p>Routes & Fees</p>
                    </a>

                    <a href="data_sheet.php?dept=<?php echo $dept; ?>" class="action-box box-data">
                        <i class="fas fa-file-csv"></i>
                        <h3>Data Sheet</h3>
                        <p>Department Analytics</p>
                    </a> -->

                    <a href="hec_survey.php?dept=<?php echo $dept; ?>" class="action-box box-hec">
                        <i class="fas fa-poll-h"></i>
                        <h3>HEC Survey</h3>
                        <p>Compliance & Feedback</p>
                    </a>

                    <div style="grid-column: 1 / -1; margin-top: 20px;">
                        <h3 style="font-size: 18px; color: #1e293b; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-headset" style="color: #ef4444;"></i> Student Service Hub
                        </h3>
                        <p style="font-size: 13px; color: #64748b; margin-bottom: 15px;">Process department-specific
                            applications and forms.</p>
                    </div>

                    <a href="student_service.php?type=Transcript&dept=<?php echo $dept; ?>" class="action-box">
                        <i class="fas fa-file-invoice" style="color: #3b82f6;"></i>
                        <h3>Transcript</h3>
                        <p>Degree & Results</p>
                    </a>

                    <a href="student_service.php?type=Hostel&dept=<?php echo $dept; ?>" class="action-box">
                        <i class="fas fa-hotel" style="color: #f59e0b;"></i>
                        <h3>Hostel</h3>
                        <p>Allotment & Dues</p>
                    </a>

                    <a href="student_service.php?type=Transport&dept=<?php echo $dept; ?>" class="action-box">
                        <i class="fas fa-bus" style="color: #10b981;"></i>
                        <h3>Transport</h3>
                        <p>Routes & Cards</p>
                    </a>

                    <a href="student_service.php?type=Clearance&dept=<?php echo $dept; ?>" class="action-box">
                        <i class="fas fa-clipboard-check" style="color: #8b5cf6;"></i>
                        <h3>Clearance</h3>
                        <p>Final No Dues</p>
                    </a>
                </div>
            </main>
        </div>
    </div>

</body>

</html>