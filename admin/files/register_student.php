<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

$message = "";

// AJAX endpoint to get the next roll number without refreshing page
if (isset($_GET['get_next_roll'])) {
    $y = $_GET['y'];
    $m = $_GET['m'] == 'true' ? 'M' : '';
    $d = $_GET['d'];
    $deg = $_GET['deg'];
    $prefix = "UW-" . $y . $m . "-" . $d . "-" . $deg . "-%";

    $query = $conn->prepare("SELECT registration_no FROM profile WHERE registration_no LIKE ? ORDER BY registration_no DESC LIMIT 1");
    $query->bind_param("s", $prefix);
    $query->execute();
    $result = $query->get_result();

    if ($row = $result->fetch_assoc()) {
        $last_roll = (int) substr($row['registration_no'], -3);
        echo str_pad($last_roll + 1, 3, '0', STR_PAD_LEFT);
    } else {
        echo "001";
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Logic for Registration Number
    $year = $_POST['reg_year'];
    $migrated = isset($_POST['is_migrated']) ? "M" : "";
    $dept_code = strtoupper($_POST['reg_dept_code']);
    $degree = strtoupper($_POST['reg_degree']);
    $roll = str_pad($_POST['reg_roll'], 3, '0', STR_PAD_LEFT);

    $generated_reg_no = "UW-" . $year . $migrated . "-" . $dept_code . "-" . $degree . "-" . $roll;

    // 2. Check for duplicates
    $check = $conn->prepare("SELECT registration_no FROM profile WHERE registration_no = ?");
    $check->bind_param("s", $generated_reg_no);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $message = "error|Registration Number $generated_reg_no already exists!";
    } else {
        // 3. Insert into Profile Table (All 33 profile fields)
        $stmt = $conn->prepare("INSERT INTO profile (registration_no, full_name, status_badge, department, program, semester, session, admission_date, domicile, dob, cnic, nationality, gender, father_name, guardian_name, guardian_phone, family_income, current_address, permanent_address, email, phone, fsc_total, fsc_obtained, fsc_per, fsc_year, fsc_board, fsc_major, ssc_total, ssc_obtained, ssc_per, ssc_year, ssc_board, ssc_major) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        $stmt->bind_param(
            "sssssssssssssssssssssssssssssssss",
            $generated_reg_no,
            $_POST['full_name'],
            $_POST['status_badge'],
            $_POST['department'],
            $_POST['reg_degree'],
            $_POST['semester'],
            $_POST['session'],
            $_POST['admission_date'],
            $_POST['domicile'],
            $_POST['dob'],
            $_POST['cnic'],
            $_POST['nationality'],
            $_POST['gender'],
            $_POST['father_name'],
            $_POST['guardian_name'],
            $_POST['guardian_phone'],
            $_POST['family_income'],
            $_POST['current_address'],
            $_POST['permanent_address'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['fsc_total'],
            $_POST['fsc_obtained'],
            $_POST['fsc_per'],
            $_POST['fsc_year'],
            $_POST['fsc_board'],
            $_POST['fsc_major'],
            $_POST['ssc_total'],
            $_POST['ssc_obtained'],
            $_POST['ssc_per'],
            $_POST['ssc_year'],
            $_POST['ssc_board'],
            $_POST['ssc_major']
        );

        if ($stmt->execute()) {
            // 4. Create User Account with default password 123456
            $pass = "123456";
            $l_stmt = $conn->prepare("INSERT INTO users (registration_no, password) VALUES (?, ?)");
            $l_stmt->bind_param("ss", $generated_reg_no, $pass);
            $l_stmt->execute();

            $message = "success|Student Registered Successfully! ID: $generated_reg_no";
        } else {
            $message = "error|Database Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin - Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/reg_student.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">


    <div class="container py-5">
        <div class="card border-0 shadow-lg mb-5">
            <div class="card-header bg-primary text-white p-4">
                <h3 class="mb-0"><i class="fas fa-user-shield me-2"></i> Admin: Student Enrollment</h3>
<br>                <a href="dept_manager.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to
                    Dashboard</a>

            </div>
            <div class="card-body p-4">
                <form method="POST" id="regForm">

                    <h5 class="text-primary fw-bold border-bottom pb-2 mb-3">1. Identity & Registration Logic
                    </h5>
                    <div class="row g-3 mb-4 bg-white p-3 rounded border">
                        <div class="col-md-2">
                            <label class="form-label">Year (Session)</label>
                            <input type="text" name="reg_year" id="ry" class="form-control" placeholder="e.g., 22"
                                required>
                        </div>
                        <div class="col-md-2 text-center">
                            <label class="form-label d-block">Migration?</label>
                            <input type="checkbox" name="is_migrated" id="im" class="form-check-input"
                                style="width: 25px; height: 25px;">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Dept Code</label>
                            <select name="reg_dept_code" id="rdc" class="form-select" required>
                                <option value="">Select</option>
                                <option value="CS">CS</option>
                                <option value="AI">AI</option>
                                <option value="CYS">CYS</option>
                                <option value="DS">DS</option>
                                <option value="PHY">PHY</option>
                                <option value="MTH">MTH</option>
                                <option value="PSG">PSG</option>
                                <option value="ENG">ENG</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Program Level</label>
                            <select name="reg_degree" id="rd" class="form-select" required>
                                <option value="">Select</option>
                                <option value="BS">BS</option>
                                <option value="MS">MS</option>
                                <option value="MPHIL">MPhil</option>
                                <option value="PHD">PHD</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Roll No</label>
                            <div class="input-group">
                                <input type="number" name="reg_roll" id="rr" class="form-control" placeholder="001"
                                    required min="1" max="999">
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="suggestRoll()">Suggest</button>
                            </div>
                        </div>
                    </div>

                    <h5 class="text-primary fw-bold border-bottom pb-2 mb-3">2. Personal Information</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" placeholder="Enter Student Name"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status_badge" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Regular">Regular</option>
                                <option value="Medical Leave">Medical Leave</option>
                                <option value="Fee Defaulter">Fee Defaulter</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Department Name</label>
                            <input type="text" name="department" class="form-control"
                                placeholder="e.g., Computer Science">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Semester</label>
                            <input type="text" name="semester" class="form-control" placeholder="e.g., 1st">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Session</label>
                            <input type="text" name="session" class="form-control" placeholder="e.g., 2022-2026">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Admission Date</label>
                            <input type="date" name="admission_date" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="dob" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">CNIC</label>
                            <input type="text" name="cnic" class="form-control" placeholder="35201-XXXXXXX-X">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Nationality</label>
                            <input type="text" name="nationality" class="form-control" value="Pakistani">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Domicile</label>
                            <input type="text" name="domicile" class="form-control" placeholder="e.g., Punjab">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="example@university.com"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="03XXXXXXXXX">
                        </div>
                    </div>

                    <h5 class="text-primary fw-bold border-bottom pb-2 mb-3">3. Guardian & Family Information
                    </h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4"><label class="form-label">Father Name</label><input type="text"
                                name="father_name" class="form-control" placeholder="Father's Name"></div>
                        <div class="col-md-4"><label class="form-label">Guardian Name</label><input type="text"
                                name="guardian_name" class="form-control" placeholder="Guardian's Name"></div>
                        <div class="col-md-4"><label class="form-label">Guardian Phone</label><input type="text"
                                name="guardian_phone" class="form-control" placeholder="Guardian's Phone"></div>
                        <div class="col-md-4"><label class="form-label">Family Income</label><input type="text"
                                name="family_income" class="form-control" placeholder="Monthly Income"></div>
                        <div class="col-md-4"><label class="form-label">Current Address</label><input type="text"
                                name="current_address" class="form-control" placeholder="Street, City"></div>
                        <div class="col-md-4"><label class="form-label">Permanent Address</label><input type="text"
                                name="permanent_address" class="form-control" placeholder="Home Town Address"></div>
                    </div>

                    <h5 class="text-primary fw-bold border-bottom pb-2 mb-3">4. Academic History (FSc & SSC)
                    </h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-2"><label class="form-label">FSc Total</label><input type="number"
                                name="fsc_total" class="form-control" placeholder="1100"></div>
                        <div class="col-md-2"><label class="form-label">FSc Obtained</label><input type="number"
                                name="fsc_obtained" class="form-control" placeholder="900"></div>
                        <div class="col-md-2"><label class="form-label">FSc %</label><input type="text" name="fsc_per"
                                class="form-control" placeholder="85%"></div>
                        <div class="col-md-2"><label class="form-label">FSc Year</label><input type="text"
                                name="fsc_year" class="form-control" placeholder="2022"></div>
                        <div class="col-md-2"><label class="form-label">FSc Board</label><input type="text"
                                name="fsc_board" class="form-control" placeholder="BISE Rawalpindi"></div>
                        <div class="col-md-2"><label class="form-label">FSc Major</label><input type="text"
                                name="fsc_major" class="form-control" placeholder="Pre-Engineering"></div>
                        <div class="col-md-2"><label class="form-label">SSC Total</label><input type="number"
                                name="ssc_total" class="form-control" placeholder="1100"></div>
                        <div class="col-md-2"><label class="form-label">SSC Obtained</label><input type="number"
                                name="ssc_obtained" class="form-control" placeholder="1000"></div>
                        <div class="col-md-2"><label class="form-label">SSC %</label><input type="text" name="ssc_per"
                                class="form-control" placeholder="90%"></div>
                        <div class="col-md-2"><label class="form-label">SSC Year</label><input type="text"
                                name="ssc_year" class="form-control" placeholder="2020"></div>
                        <div class="col-md-2"><label class="form-label">SSC Board</label><input type="text"
                                name="ssc_board" class="form-control" placeholder="BISE Lahore"></div>
                        <div class="col-md-2"><label class="form-label">SSC Major</label><input type="text"
                                name="ssc_major" class="form-control" placeholder="Science"></div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow"><i
                                class="fas fa-check-circle me-2"></i> Register Student</button>
                        <!-- <button  type="submit" class="btn btn-primary btn-lg px-5 shadow"><i
                                class="fas fa-check-circle me-2"></i></button> -->

                    </div>
                </form>
            </div>
        </div>
    </div>
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
    <script>
        function suggestRoll() {
            let y = document.getElementById('ry').value;
            let m = document.getElementById('im').checked;
            let d = document.getElementById('rdc').value;
            let deg = document.getElementById('rd').value;

            if (!y || !d || !deg) {
                Swal.fire('Wait!', 'Please select Year, Dept, and Degree first.', 'warning');
                return;
            }

            fetch(`register_student.php?get_next_roll=1&y=${y}&m=${m}&d=${d}&deg=${deg}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('rr').value = data;
                });
        }

        <?php if ($message):
            $res = explode('|', $message); ?>
            Swal.fire({
                title: '<?php echo $res[0] == "success" ? "Done!" : "Error!"; ?>',
                text: '<?php echo $res[1]; ?>',
                icon: '<?php echo $res[0]; ?>'
            });
        <?php endif; ?>
    </script>
</body>

</html>