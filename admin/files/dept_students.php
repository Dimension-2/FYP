<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

// Get department from URL (e.g., dept_students.php?dept=CS)
$dept_filter = isset($_GET['dept']) ? $_GET['dept'] : '';

// Fetch only students belonging to this department
$query = $conn->prepare("SELECT * FROM profile WHERE department = ?");
$query->bind_param("s", $dept_filter);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $dept_filter; ?> Student Directory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@200;300;400;600&display=swap');
        body { background-color: #f8fafc; font-family: 'Montserrat', sans-serif; }
        .sharp-card { border-radius: 0; border: 1px solid #1a1a1a; background: #fff; box-shadow: 10px 10px 0px #10b981; }
        .header-strip { background: #1a1a1a; color: #fff; padding: 20px; border-bottom: 4px solid #10b981; }
        .table thead { background: #f1f5f9; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px; }
        .student-row:hover { background-color: #f0fff4; transition: 0.3s; }
        .btn-view { border-radius: 0; font-size: 0.7rem; letter-spacing: 1px; text-transform: uppercase; font-weight: 600; }
    </style>
</head>
<body>

<div class="header-strip mb-5">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h2 class="m-0" style="font-weight: 200; letter-spacing: 4px;">
            <?php echo strtoupper($dept_filter); ?> <span style="font-weight: 600; color: #10b981;">STUDENTS</span>
        </h2>
        <a href="admin_dashboard.php" class="btn btn-outline-light btn-sm btn-view">Back to Hub</a>
    </div>
</div>

<div class="container-fluid px-5">
    <div class="card sharp-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Photo</th>
                            <th>Registration No</th>
                            <th>Full Name</th>
                            <th>Program</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="student-row align-middle">
                            <td class="ps-4">
                                <img src="../../uploads/students/<?php echo $row['photo']; ?>" 
                                     onerror="this.src='../../uploads/students/default.png'" 
                                     class="rounded-circle" width="35" height="35" style="object-fit: cover;">
                            </td>
                            <td class="fw-bold"><?php echo $row['registration_no']; ?></td>
                            <td><?php echo strtoupper($row['full_name']); ?></td>
                            <td><span class="badge bg-light text-dark border"><?php echo $row['program']; ?></span></td>
                            <td><?php echo $row['semester']; ?></td>
                            <td>
                                <span class="badge" style="background:#d1fae5; color:#065f46;">
                                    <?php echo $row['status_badge']; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="view_full_profile.php?id=<?php echo $row['registration_no']; ?>" 
                                   class="btn btn-dark btn-sm btn-view">
                                   Full Detail
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if($result->num_rows == 0): ?>
                        <tr><td colspan="7" class="text-center py-5">No students found in this department.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>