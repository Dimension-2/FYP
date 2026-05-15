<?php 
    session_start();
    $conn = new mysqli("localhost", "root", "", "fyp");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $page_title = "HEC Student Survey"; 
    $success_msg = false;

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_survey'])) {
        // Collect and Sanitize
        $full_name = $_POST['full_name'];
        $father_name = $_POST['father_name'];
        $cnic = $_POST['cnic'];
        $whatsapp = $_POST['whatsapp'];
        $university = "University of Wah";
        $reg_no = $_POST['reg_no']; // Matches registration_no in DB
        $degree = $_POST['degree'];
        $discipline = $_POST['discipline'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $contact_pref = $_POST['contact_pref'];
        $internet_access = $_POST['internet_access'];
        $load_shedding = $_POST['load_shedding'];
        $satisfaction = $_POST['satisfaction'];
        $suggestions = $_POST['suggestions'];

        // Secure Prepared Statement
        $sql = "INSERT INTO student_surveys (
                    full_name, father_name, cnic, whatsapp, university, 
                    registration_no, degree, discipline, email, address, 
                    contact_pref, internet_access, load_shedding, satisfaction, suggestions
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssssss", 
            $full_name, $father_name, $cnic, $whatsapp, $university, 
            $reg_no, $degree, $discipline, $email, $address, 
            $contact_pref, $internet_access, $load_shedding, $satisfaction, $suggestions
        );

        if ($stmt->execute()) {
            $success_msg = true;
        }
        $stmt->close();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HEC Student Survey - University of Wah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <link rel="stylesheet" href="assets/student_survey.css">
    <style>
        .btn-teal-lg { background-color: #008080; color: white; border: none; font-weight: bold; transition: 0.3s; }
        .btn-teal-lg:hover { background-color: #006666; color: white; transform: translateY(-2px); }
        .text-teal { color: #008080; }
        .survey-check:hover { background-color: #f8f9fa; border-radius: 5px; }
        .alert-custom { background-color: #e6f4f4; color: #006666; border: 1px solid #b2dfdf; }
    </style>
</head>
<body>

<div class="main-wrapper d-flex">
    <div class="no-print"><?php include('includes/navbar.php'); ?></div>

    <div class="content-area flex-grow-1">
        <div class="no-print"><?php include('includes/header.php'); ?></div>

        <div class="container-fluid px-4 mt-4 mb-5">
            <div class="survey-brand text-center mb-4">
                <img src="https://upload.wikimedia.org/wikipedia/en/b/b5/Higher_Education_Commission_of_Pakistan_logo.svg" alt="HEC Logo" class="mb-2" style="height: 70px;">
                <h3 class="fw-bold mb-1 text-uppercase">HEC Student Survey</h3>
                <p class="text-muted small mb-3">Availability of Internet, Connectivity Issues, and Quality of Online Education</p>
                
                <?php if($success_msg): ?>
                    <div class="alert alert-success shadow-sm d-inline-block px-5">
                        <i class="bi bi-check-circle-fill me-2"></i> Thank you! Your survey response has been saved successfully.
                    </div>
                <?php else: ?>
                    <div class="alert alert-custom py-2 d-inline-block small shadow-sm">
                        <i class="bi bi-shield-check me-2"></i> Your inputs are confidential and will help improve the quality of education.
                    </div>
                <?php endif; ?>
            </div>

            <form action="" method="POST" class="needs-validation" novalidate>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                        <h5 class="fw-bold text-teal mb-0"><i class="bi bi-person-badge-fill me-2"></i>Personal & Academic Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" placeholder="Enter Full Name" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Father's Name *</label>
                                <input type="text" name="father_name" class="form-control" placeholder="Enter Father's Name" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">CNIC Number *</label>
                                <input type="text" name="cnic" class="form-control" placeholder="xxxxx-xxxxxxx-x" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">WhatsApp No</label>
                                <input type="tel" name="whatsapp" class="form-control" placeholder="03xx-xxxxxxx">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">University Name</label>
                                <input type="text" name="university" class="form-control" value="University of Wah" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Registration Number *</label>
                                <input type="text" name="reg_no" class="form-control" placeholder="Reg No" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Degree *</label>
                                <select name="degree" class="form-select" required>
                                    <option value="">Select Degree</option>
                                    <option>BS (Undergraduate)</option>
                                    <option>MS / MPhil</option>
                                    <option>PhD</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Discipline *</label>
                                <input type="text" name="discipline" class="form-control" placeholder="e.g. Computer Science" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Personal Email *</label>
                                <input type="email" name="email" class="form-control" placeholder="example@mail.com" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Residential Address *</label>
                                <input type="text" name="address" class="form-control" placeholder="Current Permanent Address" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                        <h5 class="fw-bold text-teal mb-0"><i class="bi bi-question-circle-fill me-2"></i>Connectivity & Feedback</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="fw-bold mb-3 small">1. What is the best way of contacting you?</label>
                                <?php $contacts = ['Email', 'Mobile Phone', 'WhatsApp', 'Landline', 'Postal Mail']; 
                                foreach($contacts as $c): ?>
                                <div class="form-check survey-check mb-2 p-2 ps-5">
                                    <input class="form-check-input" type="radio" name="contact_pref" id="c_<?= $c ?>" value="<?= $c ?>" required>
                                    <label class="form-check-label w-100" for="c_<?= $c ?>"><?= $c ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="fw-bold mb-3 small">2. How do you USUALLY access the Internet?</label>
                                <?php $access = ['Broadband (home)', 'Mobile Package', 'Internet Cafe', 'Friend\'s house', 'No access']; 
                                foreach($access as $a): ?>
                                <div class="form-check survey-check mb-2 p-2 ps-5">
                                    <input class="form-check-input" type="radio" name="internet_access" id="a_<?= $a ?>" value="<?= $a ?>" required>
                                    <label class="form-check-label w-100" for="a_<?= $a ?>"><?= $a ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <hr class="my-3 opacity-5">

                            <div class="col-md-6 mb-4">
                                <label class="fw-bold mb-3 small">3. Load-shedding in your area?</label>
                                <select name="load_shedding" class="form-select" required>
                                    <option value="">Select duration</option>
                                    <option>None</option>
                                    <option>0-6 hours per day</option>
                                    <option>6-12 hours per day</option>
                                    <option>More than 12 hours per day</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="fw-bold mb-3 small">4. Satisfaction with online teaching?</label>
                                <div class="d-flex gap-3 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="satisfaction" id="sat1" value="Satisfied" required>
                                        <label class="form-check-label" for="sat1">Satisfied</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="satisfaction" id="sat2" value="Somewhat">
                                        <label class="form-check-label" for="sat2">Somewhat</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="satisfaction" id="sat3" value="Not Satisfied">
                                        <label class="form-check-label" for="sat3">Not Satisfied</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold small">5. Suggestions for improvement?</label>
                            <textarea name="suggestions" class="form-control" rows="4" placeholder="Your suggestions here..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" name="submit_survey" class="btn btn-teal-lg shadow-sm px-5 py-3">
                        <i class="bi bi-send-fill me-2"></i> SUBMIT RESPONSE TO HEC
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>
</body>
</html>
<?php $conn->close(); ?>