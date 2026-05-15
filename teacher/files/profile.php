<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: ../../login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = $conn->prepare("SELECT * FROM teachers WHERE employee_id = ?");
$query->bind_param("s", $teacher_id);
$query->execute();
$result = $query->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Teacher profile not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile - <?php echo $data['full_name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/profile_style.css">
</head>

<body>

    <div class="d-flex" id="wrapper">
        <?php include('../Bars/sidebar.php'); ?>

        <div id="page-content-wrapper">
            <?php include('../Bars/header.php'); ?>

            <div class="container-fluid p-4">
                <div class="profile-hero-card mb-4 shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="profile-img-container">
                                <img src="../../Images/uw.webp.png" alt="Teacher" class="profile-main-img">
                            </div>
                        </div>
                        <div class="col">
                            <h1 class="display-6 fw-bold text-dark mb-1">
                                <?php echo strtoupper($data['full_name']); ?>
                                <span class="badge status-badge-green ms-2"><?php echo $data['status']; ?></span>
                            </h1>
                            <p class="text-muted mb-3"><i class="fas fa-id-badge me-2"></i><strong>Employee ID:</strong>
                                <?php echo $data['employee_id']; ?></p>
                            <div class="row short-details g-2">
                                <div class="col-md-4">
                                    <div class="p-2 border rounded bg-light"><strong>Department:</strong>
                                        <?php echo $data['department']; ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 border rounded bg-light"><strong>Designation:</strong>
                                        <?php echo $data['designation']; ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 border rounded bg-light"><strong>Role:</strong>
                                        <?php echo $data['role']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card info-hub-card shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-id-card me-2"></i> Unified Information
                            Hub</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0 info-table">
                            <thead class="table-light">
                                <tr>
                                    <th colspan="4" class="py-2 ps-4 small text-uppercase fw-bold text-secondary">
                                        Personal & Employment Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="label-cell">Joining Date</td>
                                    <td><?php echo $data['joining_date']; ?></td>
                                    <td class="label-cell">Employment Type</td>
                                    <td><?php echo $data['employment_type']; ?></td>
                                </tr>
                                <tr>
                                    <td class="label-cell">Date of Birth</td>
                                    <td><?php echo $data['dob']; ?></td>
                                    <td class="label-cell">CNIC</td>
                                    <td><?php echo $data['cnic']; ?></td>
                                </tr>
                                <tr>
                                    <td class="label-cell">Nationality</td>
                                    <td>Pakistani</td>
                                    <td class="label-cell">Gender</td>
                                    <td><?php echo $data['gender']; ?></td>
                                </tr>
                            </tbody>
                            <thead class="table-light border-top">
                                <tr>
                                    <th colspan="4" class="py-2 ps-4 small text-uppercase fw-bold text-secondary">
                                        Academic & Expertise</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="label-cell">Highest Degree</td>
                                    <td><?php echo $data['highest_degree']; ?></td>
                                    <td class="label-cell">Specialization</td>
                                    <td><?php echo $data['specialization']; ?></td>
                                </tr>
                                <tr>
                                    <td class="label-cell">Experience</td>
                                    <td><?php echo $data['experience_years']; ?> Years</td>
                                    <td class="label-cell">Salary</td>
                                    <td>PKR <?php echo number_format($data['salary']); ?></td>
                                </tr>
                                <tr>
                                    <td class="label-cell">Research Interests</td>
                                    <td colspan="3"><?php echo $data['research_interests']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>