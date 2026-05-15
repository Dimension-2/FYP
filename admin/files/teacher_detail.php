<?php
include('db_config.php');

// Handle Search Query
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

/** * SQL Logic:
 * 1. LEFT JOIN 'users' with 'teachers' on the registration_no/employee_id link.
 * 2. Filter for Faculty (Registration NOs that don't follow the long student pattern).
 * 3. Fetch Name, Dept, Email, and Phone directly from the 'teachers' table.
 */
$query = "SELECT u.id, u.registration_no, t.full_name, t.department, t.email, t.phone 
          FROM users u 
          LEFT JOIN teachers t ON u.registration_no = t.employee_id 
          WHERE u.registration_no NOT LIKE 'UW-%-%-%-%'";

if (!empty($search)) {
    $query .= " AND (u.registration_no LIKE '%$search%' 
                OR t.full_name LIKE '%$search%' 
                OR t.department LIKE '%$search%')";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Faculty Directory | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --danger: #ef4444;
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

        /* Search Bar Styles */
        .search-box {
            position: relative;
            display: flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 5px 15px;
            width: 320px;
            transition: 0.3s;
        }

        .search-box:focus-within {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .search-box input {
            border: none;
            outline: none;
            padding: 8px;
            width: 100%;
            background: transparent;
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
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 18px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            color: #1e293b;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: 0.2s;
        }

        .btn-back:hover {
            color: var(--primary);
        }

        .btn-delete {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-delete:hover {
            background: #be123c;
            color: white;
        }

        .faculty-id {
            background: #f5f3ff;
            color: #6d28d9;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 700;
            font-family: monospace;
            border: 1px solid #ddd6fe;
        }

        .contact-pill {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>

<body>

    <div class="main-wrapper">
        <?php include('sidebar.php'); ?>
        <div class="content">
            <?php include('header.php'); ?>
            <div class="inner-content">

                <a href="dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>

                <div class="glass-card">
                    <div class="table-header">
                        <div>
                            <h2 style="margin:0;"><i class="fas fa-chalkboard-teacher"
                                    style="color: var(--primary);"></i> Faculty Management</h2>
                            <p style="margin:5px 0 0; color:#94a3b8; font-size: 13px;">Total Teachers:
                                <?php echo mysqli_num_rows($result); ?>
                            </p>
                        </div>

                        <form method="GET" action="">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" name="search" placeholder="Search ID, Name or Dept..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </form>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Teacher ID</th>
                                <th>Faculty Name</th>
                                <th>Department</th>
                                <th>Contact Info</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <tr>
                                        <td><span
                                                class="faculty-id"><?php echo htmlspecialchars($row['registration_no']); ?></span>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($row['full_name'] ?? 'Not Set'); ?></strong>
                                        </td>
                                        <td>
                                            <span style="color: var(--primary); font-weight: 600;">
                                                <?php echo htmlspecialchars($row['department'] ?? 'Not Set'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="contact-pill">
                                                <span><i class="fas fa-envelope" style="width:15px;"></i>
                                                    <?php echo htmlspecialchars($row['email'] ?? 'N/A'); ?></span>
                                                <span><i class="fas fa-phone" style="width:15px;"></i>
                                                    <?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn-delete"
                                                onclick="if(confirm('Are you sure you want to delete this teacher account?')) window.location.href='delete_user.php?id=<?php echo $row['id']; ?>&type=teacher';">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding: 50px; color: #94a3b8;">
                                        <i class="fas fa-search-minus fa-2x" style="display:block; margin-bottom:10px;"></i>
                                        No teachers found matching your search.
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
<link rel="stylesheet" href="../css/admin_style.css">

</html>