<?php
session_start();

// 1. Database connection
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Access Control: Ensure user is logged in
if (!isset($_SESSION['registration_no'])) {
    header("Location: login.php");
    exit();
}

// Get the registration number and clean it up
$reg_no = trim($_SESSION['registration_no']);

// 3. Fetch specific user profile
// We use LOWER and TRIM in SQL to force a match regardless of spaces or casing
$stmt = $conn->prepare("SELECT * FROM profile WHERE LOWER(TRIM(registration_no)) = LOWER(?)");
$stmt->bind_param("s", $reg_no);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    // If we reach here, the session ID doesn't exist in the 'profile' table
    echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>
            <strong>Error:</strong> Profile not found for Registration No: <b>" . htmlspecialchars($reg_no) . "</b>.<br>
            Please make sure your <b>profile</b> table has a row where registration_no is exactly <b>" . htmlspecialchars($reg_no) . "</b>.
          </div>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - Avicenna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/profile.css">
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/profileshade.css">
</head>

<body>

    <div class="d-flex">
        <?php include('includes/navbar.php'); ?>

        <div class="flex-grow-1">
            <div class="top-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <span class="text-secondary small">Welcome
                        <strong><?php echo htmlspecialchars($row['full_name']); ?>.</strong></span>
                </div>
                <div class="dropdown px-3">
                    <button class="btn btn-link text-decoration-none text-dark dropdown-toggle small" type="button"
                        data-bs-toggle="dropdown">
                        <img src="Images/Profile.JPG" width="25" class="rounded-circle me-1"> My Account
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item py-2" href="logout.php"><i
                                    class="fas fa-sign-out-alt me-2 text-danger"></i> Logout</a></li>
                    </ul>
                </div>
            </div>

            <div class="container-fluid px-4">
                <div class="breadcrumb-container d-flex justify-content-between align-items-center mb-2 mt-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-white border rounded-circle p-2 me-3 shadow-sm"
                            style="width: 40px; height: 40px; display: flex; justify-content: center; align-items: center;">
                            <i class="fas fa-user-graduate text-dark"></i>
                        </div>
                        <h4 class="mb-0 fw-bold" style="color: #0654c9ff;">Profile</h4>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item text-secondary">YOU ARE HERE:</li>
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-primary">Home</a>
                            </li>
                            <li class="breadcrumb-item active">Profile</li>
                        </ol>
                    </nav>
                </div>

                <div class="card p-4 border-0 shadow-sm mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-auto">
                            <div class="profile-img-container">
                                <img src="Images/Profile.JPG" alt="Profile" class="img-fluid">
                            </div>
                        </div>
                        <div class="col-md profile-header-info">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <h2 class="mb-0"><?php echo htmlspecialchars($row['full_name']); ?></h2>
                                <span class="status-badge"><?php echo htmlspecialchars($row['status_badge']); ?></span>
                            </div>
                            <p class="mb-1 text-muted"><strong>Registration No:</strong>
                                <?php echo htmlspecialchars($row['registration_no']); ?></p>
                            <p class="mb-1 text-muted"><strong>Department:</strong>
                                <?php echo htmlspecialchars($row['department']); ?></p>
                            <p class="mb-0 text-muted"><strong>Program:</strong>
                                <?php echo htmlspecialchars($row['program']); ?> | <strong>Semester:</strong>
                                <?php echo htmlspecialchars($row['semester']); ?> | <strong>Session:</strong>
                                <?php echo htmlspecialchars($row['session']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header section-title">
                        <i class="fas fa-id-card-alt me-2"></i> Unified Information Hub
                    </div>
                    <div class="card-body p-0">
                        <div class="bg-light px-4 py-1 small fw-bold text-uppercase border-bottom"
                            style="font-size: 0.7rem;">Personal & Admission Details</div>
                        <div class="row g-0">
                            <div class="col-md-2 info-label">Admission Date</div>
                            <div class="col-md-4 info-value"><?php echo htmlspecialchars($row['admission_date']); ?>
                            </div>
                            <div class="col-md-2 info-label">Domicile</div>
                            <div class="col-md-4 info-value"><?php echo htmlspecialchars($row['domicile']); ?></div>
                        </div>
                        <div class="row g-0">
                            <div class="col-md-2 info-label">Date of Birth</div>
                            <div class="col-md-4 info-value"><?php echo htmlspecialchars($row['dob']); ?></div>
                            <div class="col-md-2 info-label">CNIC</div>
                            <div class="col-md-4 info-value"><?php echo htmlspecialchars($row['cnic']); ?></div>
                        </div>
                        <div class="row g-0">
                            <div class="col-md-2 info-label">Nationality</div>
                            <div class="col-md-4 info-value"><?php echo htmlspecialchars($row['nationality']); ?></div>
                            <div class="col-md-2 info-label">Gender</div>
                            <div class="col-md-4 info-value"><?php echo htmlspecialchars($row['gender']); ?></div>
                        </div>

                        <div class="bg-light px-4 py-1 small fw-bold text-uppercase border-bottom border-top"
                            style="font-size: 0.7rem;">Family & Guardian</div>
                        <div class="row g-0">
                            <div class="col-md-2 info-label">Father Name</div>
                            <div class="col-md-4 info-value"><?php echo htmlspecialchars($row['father_name']); ?></div>
                            <div class="col-md-2 info-label">Guardian Name</div>
                            <div class="col-md-4 info-value"><?php echo htmlspecialchars($row['guardian_name']); ?>
                            </div>
                        </div>
                        <div class="row g-0">
                            <div class="col-md-2 info-label">Guardian Phone</div>
                            <div class="col-md-4 info-value"><?php echo htmlspecialchars($row['guardian_phone']); ?>
                            </div>
                            <div class="col-md-2 info-label">Family Income</div>
                            <div class="col-md-4 info-value"><?php echo htmlspecialchars($row['family_income']); ?>
                            </div>
                        </div>

                        <div class="bg-light px-4 py-1 small fw-bold text-uppercase border-bottom border-top"
                            style="font-size: 0.7rem;">Location & Contact</div>
                        <div class="row g-0">
                            <div class="col-md-2 info-label">Current Address</div>
                            <div class="col-md-10 info-value"><?php echo htmlspecialchars($row['current_address']); ?>
                            </div>
                        </div>
                        <div class="row g-0">
                            <div class="col-md-2 info-label">Permanent Address</div>
                            <div class="col-md-10 info-value"><?php echo htmlspecialchars($row['permanent_address']); ?>
                            </div>
                        </div>

                        <div class="quick-contact-bar d-flex align-items-center">
                            <span class="small text-muted me-3">Quick Actions:</span>
                            <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="contact-pill">
                                <i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($row['email']); ?>
                            </a>
                            <a href="tel:<?php echo htmlspecialchars($row['phone']); ?>" class="contact-pill">
                                <i class="fas fa-phone-alt me-2"></i> <?php echo htmlspecialchars($row['phone']); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header section-title">
                        <i class="fas fa-graduation-cap me-2"></i> Academic History
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table academic-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Qualification</th>
                                        <th>Total Marks</th>
                                        <th>Obtained Marks</th>
                                        <th>Percentage</th>
                                        <th>Year</th>
                                        <th>Board/Institute</th>
                                        <th>Majors</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-start ps-3">FSc/A-Level or Equivalent</td>
                                        <td><?php echo htmlspecialchars($row['fsc_total']); ?></td>
                                        <td><?php echo htmlspecialchars($row['fsc_obtained']); ?></td>
                                        <td><?php echo htmlspecialchars($row['fsc_per']); ?></td>
                                        <td><?php echo htmlspecialchars($row['fsc_year']); ?></td>
                                        <td><?php echo htmlspecialchars($row['fsc_board']); ?></td>
                                        <td><?php echo htmlspecialchars($row['fsc_major']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-start ps-3">Matric/SSC or Equivalent</td>
                                        <td><?php echo htmlspecialchars($row['ssc_total']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ssc_obtained']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ssc_per']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ssc_year']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ssc_board']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ssc_major']); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>