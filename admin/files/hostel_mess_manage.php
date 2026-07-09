<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

// Handle marking as paid
if (isset($_GET['mark_paid'])) {
    $id = intval($_GET['mark_paid']);
    $conn->query("UPDATE hostel_mess_vouchers SET status = 'Paid', paid_date = NOW() WHERE id = $id");
    header("Location: hostel_mess_manage.php");
    exit();
}

$vouchers = $conn->query("SELECT * FROM hostel_mess_vouchers ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Mess Vouchers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <h3 class="fw-bold mb-4">Hostel Mess Administration</h3>
        
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Reg No</th>
                            <th>Name</th>
                            <th>Month</th>
                            <th>Challan No</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $vouchers->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['registration_no']); ?></td>
                            <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['billing_month']); ?></td>
                            <td><?php echo htmlspecialchars($row['challan_no']); ?></td>
                            <td>PKR <?php echo number_format($row['total_payable']); ?></td>
                            <td>
                                <?php if($row['status'] == 'Paid'): ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['status'] == 'Unpaid'): ?>
                                    <a href="?mark_paid=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">Mark Paid</a>
                                <?php else: ?>
                                    <span class="text-muted small">Cleared</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>