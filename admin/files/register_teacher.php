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

    // Use try-catch to gracefully handle duplicate entries (like emails) instead of crashing
    try {
        $stmt->execute();

        // Also create login account in users table (Use ON DUPLICATE KEY to avoid crashes here too)
        $conn->query("INSERT INTO users (registration_no, password) VALUES ('$emp_id', '$pass') ON DUPLICATE KEY UPDATE password='$pass'");

        $msg = "success|Teacher Registered! ID: $emp_id";

    } catch (mysqli_sql_exception $e) {
        // Error Code 1062 means "Duplicate Entry"
        if ($e->getCode() == 1062) {
            if (strpos($e->getMessage(), 'email') !== false) {
                $msg = "error|Registration Failed: The email '$email' is already registered to another teacher.";
            } elseif (strpos($e->getMessage(), 'PRIMARY') !== false || strpos($e->getMessage(), 'employee_id') !== false) {
                $msg = "error|Registration Failed: The Employee ID '$emp_id' is already in use.";
            } else {
                $msg = "error|Registration Failed: A duplicate record was found.";
            }
        } else {
            $msg = "error|Database Error: " . $e->getMessage();
        }
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
            color: #4f46e5;
        }
    </style>
</head>

<body>
    <div class="container mt-4 mb-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header text-center bg-white border-0 pt-4">
                <h3 class="fw-bold">Faculty Recruitment</h3>
                <a href="dept_manager.php" class="btn-back mt-2"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>

            <div class="card-body p-5 pt-2">
                <form method="POST">

                    <div class="section-title fw-bold text-primary border-bottom pb-2 mb-3 mt-4">1. System & Account
                        Setup</div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Employee ID</label>
                            <input type="text" name="emp_id" class="form-control" placeholder="T-2024-001" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Portal Role</label>
                            <select name="role" class="form-select">
                                <option value="Teacher">Teacher</option>
                                <option value="HOD">HOD</option>
                                <option value="Coordinator">Coordinator</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Account Status</label>
                            <select name="status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Official Email</label>
                            <input type="email" name="email" class="form-control" placeholder="name@uni.edu" required>
                        </div>
                    </div>

                    <div class="section-title fw-bold text-primary border-bottom pb-2 mb-3 mt-4">2. Personal Profile
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">CNIC</label>
                            <input type="text" name="cnic" class="form-control" placeholder="00000-0000000-0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Mobile</label>
                            <input type="number" name="phone" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">DOB</label>
                            <input type="date" name="dob" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Gender</label>
                            <select name="gender" class="form-select">
                                <option>Male</option>
                                <option>Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Current Address</label>
                            <input type="text" name="address" class="form-control" required>
                        </div>
                    </div>

                    <div class="section-title fw-bold text-primary border-bottom pb-2 mb-3 mt-4">3. Employment &
                        Academic</div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Designation</label>
                            <input type="text" name="desig" class="form-control" placeholder="Assistant Professor"
                                required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Department</label>
                            <select name="dept" class="form-select" required>
                                <option value="CS">Computer Science</option>
                                <option value="AI">Artificial Intelligence</option>
                                <option value="CYS">Cyber Security</option>
                                <option value="DS">Data Science</option>
                                <option value="PHY">Physics</option>
                                <option value="MTH">Mathematics</option>
                                <option value="PSG">Psychology</option>
                                <option value="ENG">English</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Joining Date</label>
                            <input type="date" name="j_date" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Type</label>
                            <select name="type" class="form-select">
                                <option>Permanent</option>
                                <option>Contract</option>
                                <option>Visiting</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Highest Degree</label>
                            <input type="text" name="degree" class="form-control" placeholder="PhD Computer Science"
                                required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Specialization</label>
                            <input type="text" name="spec" class="form-control" placeholder="Machine Learning" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Exp (Years)</label>
                            <input type="number" name="exp" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Monthly Salary</label>
                            <input type="number" name="salary" class="form-control" required>
                        </div>
                    </div>

                    <div class="section-title fw-bold text-primary border-bottom pb-2 mb-3 mt-4">4. Expertise & Research
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Research Interests</label>
                            <textarea name="research" class="form-control" rows="3"
                                placeholder="Cloud Computing, AI Ethics..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Short Bio</label>
                            <textarea name="bio" class="form-control" rows="3"
                                placeholder="Professional background..."></textarea>
                        </div>
                    </div>

                    <div class="text-center mt-5">
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold" style="font-size: 1.1rem;">
                            <i class="fas fa-check-circle me-2"></i> Complete Recruitment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Check if there's a message from PHP
        <?php if ($msg):
            $res = explode('|', $msg);
            // Change alert icon based on success or error
            $iconType = ($res[0] === 'success') ? 'success' : 'error';
            ?>
            Swal.fire({
                icon: '<?php echo $iconType; ?>',
                title: '<?php echo ($iconType == "success" ? "Success!" : "Action Failed"); ?>',
                text: '<?php echo addslashes($res[1]); ?>',
                confirmButtonColor: '<?php echo ($iconType == "success" ? "#198754" : "#dc3545"); ?>'
            });
        <?php endif; ?>
    </script>
</body>

</html>