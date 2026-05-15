<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");
$id = $_GET['id'];

// Fetch the specific student's complete record
$stmt = $conn->prepare("SELECT * FROM profile WHERE registration_no = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile: <?php echo $data['full_name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/reg_student.css"> </head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-sm border-0" style="border-radius: 0; border-top: 5px solid #10b981 !important;">
        <div class="card-header bg-white p-4 d-flex justify-content-between align-items-center">
            <h4 class="m-0" style="font-weight: 300; letter-spacing: 2px;">DETAILED DOSSIER</h4>
            <button onclick="window.history.back()" class="btn btn-dark btn-sm" style="border-radius:0;">RETURN</button>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <tr class="table-light"><th colspan="4" class="py-2 small text-uppercase">1. Identity Details</th></tr>
                <tr>
                    <td class="fw-bold bg-light" width="20%">Reg No</td><td width="30%"><?php echo $data['registration_no']; ?></td>
                    <td class="fw-bold bg-light" width="20%">Admission Date</td><td width="30%"><?php echo $data['admission_date']; ?></td>
                </tr>
                <tr>
                    <td class="fw-bold bg-light">Full Name</td><td><?php echo $data['full_name']; ?></td>
                    <td class="fw-bold bg-light">CNIC</td><td><?php echo $data['cnic']; ?></td>
                </tr>
                
                <tr class="table-light"><th colspan="4" class="py-2 small text-uppercase">2. Academic Background</th></tr>
                <tr>
                    <td class="fw-bold bg-light">SSC Board</td><td><?php echo $data['ssc_board']; ?></td>
                    <td class="fw-bold bg-light">SSC Percentage</td><td><?php echo $data['ssc_per']; ?>%</td>
                </tr>
                <tr>
                    <td class="fw-bold bg-light">FSC Board</td><td><?php echo $data['fsc_board']; ?></td>
                    <td class="fw-bold bg-light">FSC Percentage</td><td><?php echo $data['fsc_per']; ?>%</td>
                </tr>
                
                <tr class="table-light"><th colspan="4" class="py-2 small text-uppercase">3. Contact & Address</th></tr>
                <tr>
                    <td class="fw-bold bg-light">Phone</td><td><?php echo $data['phone']; ?></td>
                    <td class="fw-bold bg-light">Email</td><td><?php echo $data['email']; ?></td>
                </tr>
                <tr>
                    <td class="fw-bold bg-light">Address</td><td colspan="3"><?php echo $data['current_address']; ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

</body>
</html>