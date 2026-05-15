<?php
$conn = new mysqli("localhost", "root", "", "fyp");
$search = $_GET['search'] ?? '';
$query = "SELECT registration_no, photo, full_name, status_badge, email FROM profile WHERE registration_no LIKE '%$search%' OR full_name LIKE '%$search%'";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light p-5">
    <div class="container bg-white p-4 shadow rounded">
        <div class="d-flex justify-content-between mb-4">
            <h2><i class="fas fa-users"></i> Student Database</h2>
            <a href="register_student.php" class="btn btn-primary">Add New Student</a>
        </div>

        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by ID or Name..."
                    value="<?php echo $search; ?>">
                <button class="btn btn-primary">Search</button>
            </div>
        </form>

        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Photo</th>
                    <th>Reg No</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><img src="uploads/<?php echo $row['photo']; ?>" width="50" height="50" class="rounded-circle"
                                style="object-fit: cover;"></td>
                        <td><?php echo $row['registration_no']; ?></td>
                        <td><?php echo $row['full_name']; ?></td>
                        <td>
                            <span
                                class="badge <?php echo ($row['status_badge'] == 'Fee Defaulter') ? 'bg-danger' : 'bg-success'; ?>">
                                <?php echo $row['status_badge']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_student.php?id=<?php echo $row['registration_no']; ?>"
                                class="btn btn-sm btn-warning">Edit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>