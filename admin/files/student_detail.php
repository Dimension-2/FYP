<?php
include('db_config.php');

// Handle Search Query
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

/**
 * SQL LOGIC:
 * 1. LEFT JOIN 'users' with 'profile' on registration_no.
 * 2. Filter for registration numbers starting with 'UW-'.
 * 3. Handle search by Roll No, Name, or Department.
 */
$query = "SELECT u.id, u.registration_no, p.full_name, p.department, p.program, p.semester, p.status_badge 
          FROM users u 
          LEFT JOIN profile p ON u.registration_no = p.registration_no 
          WHERE u.registration_no LIKE 'UW-%'";

// Append search conditions if search is performed
if (!empty($search)) {
    $query .= " AND (u.registration_no LIKE '%$search%' 
                OR p.full_name LIKE '%$search%' 
                OR p.department LIKE '%$search%')";
}

// Order by registration number
$query .= " ORDER BY u.registration_no ASC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Directory | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    
    <style>
        :root {
            --primary: #3b82f6;
            --danger: #ef4444;
            --success: #15803d;
            --bg: #f1f5f9;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            margin: 0;
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

        /* Search Bar UI */
        .search-box {
            position: relative;
            display: flex;
            align-items: center;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 5px 15px;
            width: 350px;
        }

        .search-box input {
            border: none;
            outline: none;
            padding: 8px;
            width: 100%;
            font-size: 14px;
        }

        .search-box i {
            color: #94a3b8;
        }

        /* Glass Card UI */
        .glass-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .table-header {
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            padding: 15px 20px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 1px;
        }

        td {
            padding: 18px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            color: #1e293b;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
        }

        .user-dept {
            font-size: 12px;
            color: #64748b;
        }

        .reg-pill {
            background: #eff6ff;
            color: #1d4ed8;
            padding: 6px 12px;
            border-radius: 6px;
            font-family: monospace;
            font-weight: 600;
            font-size: 13px;
            border: 1px solid #dbeafe;
        }

        /* Status Badge Logic */
        .status-indicator {
            font-weight: 600;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .status-active {
            color: var(--success);
        }

        .status-inactive {
            color: var(--danger);
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .btn-back:hover {
            color: var(--primary);
        }

        .btn-delete {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
            font-weight: 600;
        }

        .btn-delete:hover {
            background: #be123c;
            color: white;
        }
    </style>
</head>

<body>

    <div class="main-wrapper">
        <?php include('sidebar.php'); ?>
        <div class="content">
            <?php include('header.php'); ?>
            <div class="inner-content">

                <a href="admin_dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>

                <div class="glass-card">
                    <div class="table-header">
                        <div>
                            <h2 style="margin:0;">Student Directory</h2>
                            <p style="margin:5px 0 0; color:#64748b; font-size: 14px;">
                                Total Found: <b><?php echo mysqli_num_rows($result); ?></b>
                            </p>
                        </div>

                        <form method="GET" action="">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" name="search" placeholder="Search Roll No, Name, or Dept..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </form>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Registration No</th>
                                <th>Student Details</th>
                                <th>Academic Info</th>
                                <th>Status</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Logic to determine status color
                                    $status_text = $row['status_badge'] ?? 'Active';
                                    $status_class = (strtolower($status_text) == 'active') ? 'status-active' : 'status-inactive';
                                    ?>
                                    <tr>
                                        <td><span
                                                class="reg-pill"><?php echo htmlspecialchars($row['registration_no']); ?></span>
                                        </td>
                                        <td>
                                            <div class="user-info">
                                                <span
                                                    class="user-name"><?php echo htmlspecialchars($row['full_name'] ?? 'No Name'); ?></span>
                                                <span
                                                    class="user-dept"><?php echo htmlspecialchars($row['department'] ?? 'Not Assigned'); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: #334155;">
                                                <?php echo htmlspecialchars($row['program'] ?? 'N/A'); ?>
                                            </div>
                                            <div style="font-size: 11px; color:#64748b; margin-top: 2px;">
                                                Semester: <?php echo htmlspecialchars($row['semester'] ?? '-'); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-indicator <?php echo $status_class; ?>">
                                                ● <?php echo htmlspecialchars($status_text); ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <button class="btn-delete"
                                                onclick="if(confirm('Are you sure you want to permanently delete this student record?')) window.location.href='delete_user.php?id=<?php echo $row['id']; ?>&type=student';">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding: 60px; color: #94a3b8;">
                                        <i class="fas fa-user-slash fa-2x" style="display:block; margin-bottom:10px;"></i>
                                        No students found matching your criteria.
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>

</html>