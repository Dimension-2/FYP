<?php
include('db_config.php');

// Handle Search
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

/**
 * SQL Logic:
 * Joins student_surveys (s) with profile (p) using registration_no.
 */
$query = "SELECT s.*, p.full_name as profile_name, p.department as profile_dept 
          FROM student_surveys s
          LEFT JOIN profile p ON s.registration_no = p.registration_no";

if (!empty($search)) {
    $query .= " WHERE s.registration_no LIKE '%$search%' 
                OR p.full_name LIKE '%$search%' 
                OR s.discipline LIKE '%$search%'";
}

$query .= " ORDER BY s.submitted_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HEC Survey Responses | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #eef2ff;
            --bg: #f8fafc;
            --glass: #ffffff;
            --border: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            margin: 0;
            color: var(--text-main);
            overflow-x: hidden;
        }

        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            /* Prevents flex items from overflowing */
        }

        .inner-content {
            padding: 2rem;
            width: 100%;
            box-sizing: border-box;
            max-width: 1600px;
            /* Limits ultra-wide stretching while staying spacious */
            margin: 0 auto;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: color 0.2s;
        }

        .btn-back:hover {
            color: var(--primary);
        }

        /* Glass Card Design */
        .glass-card {
            background: var(--glass);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            min-height: 70vh;
            /* Ensures the box has a significant presence */
        }

        .header-section {
            padding: 24px 30px;
            background: #fff;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Search Bar */
        .search-box {
            display: flex;
            align-items: center;
            background: #f1f5f9;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 8px 16px;
            width: 350px;
            transition: all 0.3s ease;
        }

        .search-box:focus-within {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .search-box input {
            border: none;
            outline: none;
            padding: 8px;
            background: transparent;
            width: 100%;
            font-size: 14px;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            flex-grow: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        th {
            background: #f8fafc;
            padding: 16px 24px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            position: sticky;
            top: 0;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        td {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            vertical-align: top;
            line-height: 1.6;
        }

        tr:hover {
            background-color: #fcfdfe;
        }

        .reg-badge {
            background: var(--primary-light);
            color: var(--primary);
            padding: 4px 10px;
            border-radius: 8px;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
        }

        .survey-detail {
            font-size: 13px;
            color: var(--text-muted);
        }

        .survey-detail i {
            width: 20px;
            color: var(--primary);
            margin-right: 5px;
        }

        .satisfaction {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }

        .sat-high {
            background: #dcfce7;
            color: #166534;
        }

        .sat-low {
            background: #fee2e2;
            color: #991b1b;
        }

        .suggestion-text {
            margin-top: 10px;
            font-style: italic;
            color: #475569;
            display: block;
            max-width: 300px;
            word-wrap: break-word;
        }

        .date-col {
            white-space: nowrap;
            color: var(--text-muted);
            font-size: 12px;
        }

        /* Scrollbar Styling */
        .table-container::-webkit-scrollbar {
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
    </style>
</head>

<body>

    <div class="main-wrapper">
        <?php include('sidebar.php'); ?>

        <div class="content">
            <?php include('header.php'); ?>

            <div class="inner-content">
                <a href="dept_manager.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Department Hub
                </a>

                <div class="glass-card">
                    <div class="header-section">
                        <div>
                            <h2 style="margin:0; font-weight: 700; letter-spacing: -0.02em;">
                                <i class="fas fa-poll-h" style="color:var(--primary); margin-right: 10px;"></i>HEC
                                Survey Analysis
                            </h2>
                            <p style="margin:5px 0 0; color:var(--text-muted); font-size: 14px;">
                                Reviewing comprehensive student feedback and infrastructure compliance.
                            </p>
                        </div>

                        <form method="GET">
                            <div class="search-box">
                                <i class="fas fa-search" style="color:#94a3b8"></i>
                                <input type="text" name="search" placeholder="Search Registration No or Name..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </form>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student Identity</th>
                                    <th>Contact & Bio</th>
                                    <th>Academic Info</th>
                                    <th>Digital Access</th>
                                    <th>Feedback & Suggestions</th>
                                    <th>Submitted On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td>
                                                <div style="font-weight:600; color: #0f172a; font-size: 15px;">
                                                    <?php echo htmlspecialchars($row['profile_name'] ?? $row['full_name']); ?>
                                                </div>
                                                <div style="margin-top:8px;">
                                                    <span
                                                        class="reg-badge"><?php echo htmlspecialchars($row['registration_no']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="survey-detail">
                                                    <i class="fas fa-phone"></i>
                                                    <?php echo htmlspecialchars($row['whatsapp']); ?><br>
                                                    <i class="fas fa-envelope"></i>
                                                    <?php echo htmlspecialchars($row['email']); ?><br>
                                                    <i class="fas fa-id-card"></i> <?php echo htmlspecialchars($row['cnic']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="survey-detail">
                                                    <strong style="color: #475569;">Degree:</strong>
                                                    <?php echo htmlspecialchars($row['degree']); ?><br>
                                                    <strong style="color: #475569;">Dept:</strong>
                                                    <?php echo htmlspecialchars($row['discipline']); ?><br>
                                                    <strong style="color: #475569;">Uni:</strong>
                                                    <?php echo htmlspecialchars($row['university']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="survey-detail">
                                                    <i class="fas fa-wifi"></i>
                                                    <?php echo htmlspecialchars($row['internet_access']); ?><br>
                                                    <i class="fas fa-bolt"></i>
                                                    <?php echo htmlspecialchars($row['load_shedding']); ?><br>
                                                    <i class="fas fa-user-check"></i>
                                                    <?php echo htmlspecialchars($row['contact_pref']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $isSatisfied = (stripos($row['satisfaction'], 'Satisfied') !== false);
                                                ?>
                                                <span class="satisfaction <?php echo $isSatisfied ? 'sat-high' : 'sat-low'; ?>">
                                                    <i
                                                        class="fas <?php echo $isSatisfied ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                                                    <?php echo htmlspecialchars($row['satisfaction']); ?>
                                                </span>
                                                <span class="suggestion-text">
                                                    "<?php echo htmlspecialchars($row['suggestions']); ?>"
                                                </span>
                                            </td>
                                            <td class="date-col">
                                                <div style="font-weight: 600; color: var(--text-main);">
                                                    <?php echo date('d M, Y', strtotime($row['submitted_at'])); ?>
                                                </div>
                                                <div><?php echo date('h:i A', strtotime($row['submitted_at'])); ?></div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding:120px 0; color:#94a3b8;">
                                            <i class="fas fa-folder-open fa-4x"
                                                style="opacity: 0.3; margin-bottom: 20px;"></i><br>
                                            <span style="font-size: 18px; font-weight: 500;">No survey records
                                                found.</span><br>
                                            <small>Try adjusting your search criteria.</small>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
<link rel="stylesheet" href="../css/admin_style.css">

</html>