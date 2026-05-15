<?php
include('db_config.php');

// 1. Get variables from URL
$type = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : 'Transcript';
$dept = isset($_GET['dept']) ? mysqli_real_escape_string($conn, $_GET['dept']) : 'All';

// 2. Fetch data - Filtered by both Type and Department
// Added department filtering to make it truly functional for the Admin
$query = "SELECT * FROM student_services 
          WHERE service_type = '$type' 
          AND status = 'Pending' 
          ORDER BY applied_on DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo $type; ?> Management | Admin Panel</title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #3b82f6;
            --bg: #f1f5f9;
            --text: #1e293b;
            --white: #ffffff;
            --border: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            margin: 0;
            color: var(--text);
        }

        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .inner-content {
            padding: 30px;
        }

        /* Header UI */
        .page-title {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            padding: 25px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border-left: 6px solid var(--primary);
        }

        /* Table UI */
        .data-card {
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid var(--border);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            padding: 18px 20px;
            text-align: left;
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 18px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

        .reg-badge {
            background: #eff6ff;
            color: var(--primary);
            padding: 5px 10px;
            border-radius: 8px;
            font-weight: 700;
            border: 1px solid #dbeafe;
        }

        /* Action Buttons */
        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn-v {
            padding: 8px 15px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: 0.2s;
            font-weight: 600;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-approve {
            background: #dcfce7;
            color: #15803d;
        }

        .btn-approve:hover {
            background: #15803d;
            color: white;
        }

        .btn-reject {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-reject:hover {
            background: #991b1b;
            color: white;
        }

        .btn-voucher {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid var(--border);
        }

        .btn-voucher:hover {
            background: var(--border);
        }

        .status-tag {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 12px;
            background: #fef3c7;
            color: #92400e;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="main-wrapper">
        <?php include('sidebar.php'); ?>

        <div class="content">
            <?php include('header.php'); ?>

            <div class="inner-content">
                <div class="page-title">
                    <i class="fas fa-id-card-alt" style="font-size: 30px; color: var(--primary);"></i>
                    <div>
                        <h2><?php echo $type; ?> Verification Desk</h2>
                        <p style="margin: 5px 0 0; color: #64748b; font-size: 14px;">Reviewing requests for:
                            <b><?php echo $dept; ?></b>
                        </p>
                    </div>
                </div>

                <div class="data-card">
                    <table>
                        <thead>
                            <tr>
                                <th>Registration</th>
                                <th>Student Details</th>
                                <th>Service Specifics</th>
                                <th>Applied On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><span class="reg-badge"><?php echo $row['registration_no']; ?></span></td>

                                    <td>
                                        <strong><?php echo $row['student_name']; ?></strong>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                            <span
                                                class="status-tag"><?php echo $row['process_log_types'] ?? 'Regular'; ?></span>
                                        </div>
                                    </td>

                                    <td>
                                        <div style="font-weight: 600; color: #334155;">
                                            <?php
                                            // Safety checks using Null Coalescing (??) to prevent Undefined Key warnings
                                            if ($type == 'Transcript') {
                                                echo "Degree: " . ($row['degree_level'] ?? 'N/A');
                                            } elseif ($type == 'Hostel') {
                                                echo "Pref: " . ($row['room_preference'] ?? 'N/A');
                                            } elseif ($type == 'Transport') {
                                                echo "Bus Route: " . ($row['bus_route'] ?? 'N/A');
                                            } else {
                                                echo $type;
                                            }
                                            ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div style="color: #475569; font-size: 13px;">
                                            <i class="far fa-calendar-alt"></i>
                                            <?php echo date('d M, Y', strtotime($row['applied_on'])); ?>
                                        </div>
                                    </td>

                                    <td class="action-btns">
                                        <?php if (!empty($row['voucher_photo'])): ?>
                                            <a href="../../student/uploads/<?php echo $row['voucher_photo']; ?>" target="_blank"
                                                class="btn-v btn-voucher">
                                                <i class="fas fa-receipt"></i> Voucher
                                            </a>
                                        <?php else: ?>
                                            <span style="font-size: 11px; color: #94a3b8;">No Upload</span>
                                        <?php endif; ?>

                                        <button class="btn-v btn-approve">Approve</button>
                                        <button class="btn-v btn-reject"><i class="fas fa-times"></i></button>
                                    </td>
                                </tr>
                            <?php } ?>

                            <?php if (mysqli_num_rows($result) == 0): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:80px; color:#94a3b8;">
                                        <i class="fas fa-folder-open"
                                            style="font-size: 40px; display: block; margin-bottom: 10px; opacity: 0.5;"></i>
                                        No pending <?php echo $type; ?> applications for this section.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>

</html>