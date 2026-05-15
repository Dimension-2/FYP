<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_id = $_POST['emp_id'];
    $email = $_POST['email'];
    $pass = "teacher123"; // Default Password

    // Prepare Teacher Insert
    $sql = "INSERT INTO teachers (employee_id, full_name, email, phone, cnic, dob, gender, address, designation, department, joining_date, employment_type, role, status, salary, highest_degree, specialization, experience_years, research_interests, bio) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssdssiss",
        $emp_id,
        $_POST['name'],
        $email,
        $_POST['phone'],
        $_POST['cnic'],
        $_POST['dob'],
        $_POST['gender'],
        $_POST['address'],
        $_POST['desig'],
        $_POST['dept'],
        $_POST['j_date'],
        $_POST['type'],
        $_POST['role'],
        $_POST['status'],
        $_POST['salary'],
        $_POST['degree'],
        $_POST['spec'],
        $_POST['exp'],
        $_POST['research'],
        $_POST['bio']
    );

    if ($stmt->execute()) {
        // Also create login account in users table
        $conn->query("INSERT INTO users (registration_no, password) VALUES ('$emp_id', '$pass')");
        $msg = "success|Teacher Registered! ID: $emp_id";
    } else {
        $msg = "error|Registration Failed: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin - Teacher Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="../css/reg_teacher.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <style>
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
    </style>
    <div class="container">
        <div class="card border-0">
            <div class="card-header text-center">
                <h3>Faculty Recruitment</h3>
                <br> <a href="dept_manager.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to
                    Dashboard</a>

            </div>
            <div class="card-body p-5">
                <form method="POST">

                    <div class="section-title">1. System & Account Setup</div>
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Employee ID</label><input type="text"
                                name="emp_id" class="form-control" placeholder="T-2024-001" required></div>
                        <div class="col-md-3">
                            <label class="form-label">Portal Role</label>
                            <select name="role" class="form-select">
                                <option value="Teacher">Teacher</option>
                                <option value="HOD">HOD</option>
                                <option value="Coordinator">Coordinator</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Account Status</label>
                            <select name="status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Official Email</label><input type="email"
                                name="email" class="form-control" placeholder="name@uni.edu" required></div>
                    </div>

                    <div class="section-title">2. Personal Profile</div>
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Full Name</label><input type="text" name="name"
                                class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">CNIC</label><input type="text" name="cnic"
                                class="form-control" placeholder="00000-0000000-0" required></div>
                        <div class="col-md-4"><label class="form-label">Mobile</label><input type="number" name="phone"
                                class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">DOB</label><input type="date" name="dob"
                                class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">Gender</label><select name="gender"
                                class="form-select">
                                <option>Male</option>
                                <option>Female</option>
                            </select></div>
                        <div class="col-md-4"><label class="form-label">Current Address</label><input type="text"
                                name="address" class="form-control" required></div>
                    </div>

                    <div class="section-title">3. Employment & Academic</div>
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Designation</label><input type="text"
                                name="desig" class="form-control" placeholder="Assistant Professor" required></div>
                        <div class="col-md-3">
                            <label class="form-label">Department</label>
                            <select name="dept" class="form-select">
                                <option value="CS">Computer Science</option>
                                <option value="AI">AI</option>
                                <option value="PHY">Physics</option>
                                <option value="MTH">Maths</option>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Joining Date</label><input type="date"
                                name="j_date" class="form-control" required></div>
                        <div class="col-md-3"><label class="form-label">Type</label><select name="type"
                                class="form-select">
                                <option>Permanent</option>
                                <option>Contract</option>
                                <option>Visiting</option>
                            </select></div>

                        <div class="col-md-3"><label class="form-label">Highest Degree</label><input type="text"
                                name="degree" class="form-control" placeholder="PhD Computer Science" required></div>
                        <div class="col-md-3"><label class="form-label">Specialization</label><input type="text"
                                name="spec" class="form-control" placeholder="Machine Learning" required></div>
                        <div class="col-md-3"><label class="form-label">Exp (Years)</label><input type="number"
                                name="exp" class="form-control" required></div>
                        <div class="col-md-3"><label class="form-label">Monthly Salary</label><input type="number"
                                name="salary" class="form-control" required></div>
                    </div>

                    <div class="section-title">4. Expertise & Research</div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Research Interests</label><textarea
                                name="research" class="form-control" rows="3"
                                placeholder="Cloud Computing, AI Ethics..."></textarea></div>
                        <div class="col-md-6"><label class="form-label">Short Bio</label><textarea name="bio"
                                class="form-control" rows="3" placeholder="Professional background..."></textarea></div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Complete Recruitment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        <?php if ($msg):
            $res = explode('|', $msg); ?>
            Swal.fire('<?php echo $res[0]; ?>', '<?php echo $res[1]; ?>', '<?php echo $res[0]; ?>');
        <?php endif; ?>
    </script>
</body>

</html>