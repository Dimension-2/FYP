<?php
session_start();

// 1. --- DATABASE CONNECTION ---
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. --- IDENTITY & DIRECTORY SETUP ---
// Ensure roll_no is safe for filenames (remove slashes/dots)
$raw_roll = $_SESSION['roll_no'] ?? 'guest';
$registration_no = preg_replace('/[^A-Za-z0-9_\-]/', '_', $raw_roll);
$student_name = $_SESSION['student_name'] ?? 'Student';

$upload_dir = 'uploads/transcripts/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/**
 * Scalable File Upload Handler
 */
function handleUpload($file_key, $reg_no, $prefix, $target_dir)
{
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $ext = strtolower(pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png']))
        return "ERROR";
    if ($_FILES[$file_key]['size'] > 2 * 1024 * 1024)
        return "ERROR";

    $filename = $reg_no . "_" . $prefix . "_" . time() . "." . $ext;
    if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $target_dir . $filename)) {
        return $filename;
    }
    return "ERROR";
}

// 3. --- DYNAMIC FORM PROCESSING ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['service_type'])) {

    $type = $_POST['service_type'];
    $data = [
        'registration_no' => $registration_no,
        'service_type' => $type,
        'status' => 'Pending'
    ];

    // --- MAP FORM FIELDS TO DB COLUMNS ---
    // This allows you to add new modals/fields without changing the INSERT logic
    // --- MAP FORM FIELDS TO DB COLUMNS ---
    $field_map = [
        'student_name' => 'student_name',
        'department_hod' => 'department_hod',
        'clearance_type' => 'clearance_type',
        'library_book_return' => 'library_book_return',
        'sports_kit_return' => 'sports_kit_return',
        'outstanding_dues' => 'outstanding_dues',
        'reason_for_leaving' => 'reason_for_leaving',
        'cnic' => 'student_cnic',
        'gender' => 'gender',
        'student_cnic' => 'student_cnic',
        'degree_level' => 'degree_level',
        'academic_session' => 'academic_session',
        'transcript_type' => 'transcript_type',
        'processing_speed' => 'processing_type', // Match DB 'processing_type'
        'selected_semester' => 'selected_semester',
        'father_name' => 'father_name',
        'guardian_contact' => 'guardian_contact',
        'blood_group' => 'blood_group',
        'hostel_room_type' => 'hostel_type',    // Match DB 'hostel_type'
        'mess_preference' => 'mess_preference',
        'transport_route' => 'transport_route',
        'pickup_point' => 'pickup_point',
        'bus_number' => 'bus_number'
    ];

    foreach ($field_map as $form_key => $db_col) {
        if (isset($_POST[$form_key])) {
            $data[$db_col] = $_POST[$form_key];
        }
    }

    // --- HANDLE DYNAMIC FILE UPLOADS ---
    $files_to_process = [
        'doc_cnic' => 'doc_cnic',
        'doc_matric' => 'doc_matric',
        'doc_inter' => 'doc_inter',
        'doc_clearance' => 'doc_clearance',
        'doc_hostel_voucher' => 'doc_hostel_voucher',
        // FIX: Ensure the key and the column match your HTML input name
        'doc_transport_voucher' => 'doc_transport_voucher'
    ];

    foreach ($files_to_process as $file_key => $db_col) {
        $result = handleUpload($file_key, $registration_no, $file_key, $upload_dir);
        if ($result === "ERROR") {
            $error_msg = "File upload failed for $file_key. Check size/format.";
            break;
        } elseif ($result) {
            $data[$db_col] = $result;
        }
    }

    // --- CALCULATE FEE ---
    if (in_array($type, ['Transcript', 'Degree'])) {
        $speed = $_POST['processing_speed'] ?? 'Regular';
        $p_stmt = $conn->prepare("SELECT regular_fee, urgent_fee FROM service_prices WHERE service_type = ?");
        $p_stmt->bind_param("s", $type);
        $p_stmt->execute();
        $res = $p_stmt->get_result()->fetch_assoc();
        $data['total_calculated_fee'] = ($speed === 'Urgent') ? ($res['urgent_fee'] ?? 4000) : ($res['regular_fee'] ?? 2000);
    }

    // --- SCALABLE INSERT ---
    if (!isset($error_msg)) {
        $cols = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $types = str_repeat("s", count($data)); // Simplification: treat all as strings for bind

        $sql = "INSERT INTO student_services ($cols) VALUES ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...array_values($data));

        if ($stmt->execute()) {
            $success_msg = "Successfully submitted your $type request!";
        } else {
            $error_msg = "DB Error: " . $conn->error;
        }
    }
}

// Handle Record Deletion
// Handle Record Deletion
if (isset($_GET['delete_id'])) {
    // 1. Sanitize input
    $id = intval($_GET['delete_id']);
    $reg_no = $registration_no; // Ensure this is defined after session/DB setup

    if ($stmt = $conn->prepare("DELETE FROM student_services WHERE id = ? AND registration_no = ?")) {
        $stmt->bind_param("is", $id, $reg_no);

        if ($stmt->execute()) {
            $status = ($stmt->affected_rows > 0) ? "deleted" : "error";
            $stmt->close();
            $conn->close(); // Close connection before redirecting

            // 2. CRITICAL: Redirect to the EXACT same filename you are currently using
            // If your file is named student_services.php, use that here.
            header("Location: student_services_2.php?msg=" . $status);
            exit(); 
        } else {
            error_log("Delete failed: " . $stmt->error);
        }
        $stmt->close();
    }
}

$history = $conn->query("SELECT * FROM student_services WHERE registration_no = '{$registration_no}' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Services | University of Wah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <link rel="stylesheet" href="assets/service2.css">

</head>

<body class="bg-light">

    <div class="main-wrapper d-flex">
        <div class="no-print"><?php include('includes/navbar.php'); ?></div>

        <div class="content-area flex-grow-1">
            <div class="no-print"><?php include('includes/header.php'); ?></div>
            <div class="content-area">
                <div class="container-fluid px-4">
                    <h3 class="fw-bold mb-4"><i class="bi bi-ui-checks-grid me-2 text-primary"></i>Service Portal</h3>

                    <?php if (isset($success_msg)): ?>
                        <div class="alert alert-success mx-0 rounded-3 shadow-sm border-0 alert-dismissible fade show">
                            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="service-grid">
                        <div class="service-card" data-bs-toggle="modal" data-bs-target="#transcriptModal">
                            <div class="icon-box bg-blue-grad"><i class="bi bi-file-earmark-text"></i></div>
                            <h5 class="fw-bold">Transcript</h5>
                            <p class="text-muted small">Official or Interim Academic Records</p>
                        </div>

                        <div class="service-card" data-bs-toggle="modal" data-bs-target="#clearanceModal">
                            <div class="icon-box bg-teal-grad"><i class="bi bi-shield-check"></i></div>
                            <h5 class="fw-bold">Clearance</h5>
                            <p class="text-muted small">Final Departmental Exit Form</p>
                        </div>

                        <div class="service-card" data-bs-toggle="modal" data-bs-target="#hostelModal">
                            <div class="icon-box bg-pink-grad"><i class="bi bi-house-door"></i></div>
                            <h5 class="fw-bold">Hostel</h5>
                            <p class="text-muted small">Enrollment & Mess Vouchers</p>
                        </div>

                        <div class="service-card" data-bs-toggle="modal" data-bs-target="#transportModal">
                            <div class="icon-box bg-indigo-grad"><i class="bi bi-bus-front"></i></div>
                            <h5 class="fw-bold">Transport</h5>
                            <p class="text-muted small">Routes & Bus Pass Registration</p>
                        </div>
                    </div>

                    <div class="status-table-card mt-5">
                        <h5 class="fw-bold mb-4"><i class="bi bi-clock-history me-2"></i>History & Status</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Service Type</th>
                                        <th>Submitted On</th>
                                        <th>Financials</th>
                                        <th>Admin Status</th>
                                        <th>Collection Date</th>
                                        <th>Voucher</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $history->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold text-primary">
                                                    <?php echo htmlspecialchars($row['service_type']); ?>
                                                </span>
                                            </td>

                                            <td class="small">
                                                <?php echo !empty($row['applied_on']) ? date('M d, Y', strtotime($row['applied_on'])) : 'Recently'; ?>
                                            </td>

                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?php echo !empty($row['total_calculated_fee']) ? 'PKR ' . $row['total_calculated_fee'] : 'N/A'; ?>
                                                </span>
                                            </td>

                                            <td>
                                                <?php
                                                $status = $row['status'] ?? 'Pending';
                                                $badge_class = match ($status) {
                                                    'Approved', 'Completed' => 'bg-success',
                                                    'Rejected' => 'bg-danger',
                                                    'In Progress' => 'bg-info',
                                                    default => 'bg-warning'
                                                };
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?> rounded-pill px-3">
                                                    <?php echo $status; ?>
                                                </span>
                                            </td>

                                            <td>
                                                <?php if ($row['service_type'] === 'Clearance'): ?>
                                                    <span
                                                        class="badge <?php echo ($row['library_clearance'] ?? '') === 'Cleared' ? 'bg-success' : 'bg-info'; ?> border-0 small">
                                                        Admin:
                                                        <?php echo htmlspecialchars($row['library_clearance'] ?? 'Pending'); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted small">
                                                        <?php echo !empty($row['collection_date']) ? date('M d, Y', strtotime($row['collection_date'])) : '---'; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?php
                                                $file_to_show = '';
                                                $service = $row['service_type'];

                                                // Explicit logic based on service type to avoid column confusion
                                                if ($service === 'Transport') {
                                                    $file_to_show = $row['doc_transport_voucher'] ?? '';
                                                } elseif ($service === 'Hostel') {
                                                    $file_to_show = $row['doc_hostel_voucher'] ?? '';
                                                } elseif ($service === 'Clearance') {
                                                    $file_to_show = $row['doc_clearance'] ?? '';
                                                } else {
                                                    // Fallback for Transcripts or Degree
                                                    $file_to_show = $row['doc_matric'] ?? $row['doc_cnic'] ?? $row['doc_inter'] ?? '';
                                                }

                                                if (!empty($file_to_show)): ?>
                                                    <a href="uploads/transcripts/<?php echo htmlspecialchars($file_to_show); ?>"
                                                        target="_blank" class="btn btn-sm btn-outline-primary py-0 shadow-sm">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small italic">No File</span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <a href="student_services_2.php?delete_id=<?php echo $row['id']; ?>"
                                                    onclick="return confirm('Are you sure you want to delete this request?')"
                                                    class="btn btn-link text-danger p-0">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="transcriptModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content border-0 shadow-lg" method="POST" enctype="multipart/form-data"
                    style="border-radius: 25px;">
                    <div class="modal-header bg-primary text-white p-4" style="border-radius: 25px 25px 0 0;">
                        <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-text-fill me-2"></i>Official Transcript
                            Application</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <input type="hidden" name="service_type" value="Transcript">

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="fw-bold text-muted text-uppercase small border-bottom pb-2">Identification
                                    Details</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Full Name (As per Matric)</label>
                                <input type="text" name="student_name" class="form-control bg-light"
                                    value="<?php echo htmlspecialchars($student_name); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">CNIC Number</label>
                                <input type="text" name="cnic" class="form-control" placeholder="00000-0000000-0"
                                    required>
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="fw-bold text-muted text-uppercase small border-bottom pb-2">Academic Record
                                </h6>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Degree Level</label>
                                <select name="degree_level" class="form-select border-primary-subtle">
                                    <option>BS (Bachelors)</option>
                                    <option>MS (Masters)</option>
                                    <option>PhD (Doctorate)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Gender</label>
                                <select name="gender" class="form-select" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Academic Session</label>
                                <input type="text" name="academic_session" class="form-control"
                                    placeholder="e.g., 2020-2024" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Transcript Type</label>
                                <select name="transcript_type" class="form-select border-primary-subtle">
                                    <option value="Official">Official (Final)</option>
                                    <option value="Interim">Interim (Semester-wise)</option>
                                    <option value="Revised">Revised Transcript</option>
                                    <option value="Duplicate">Duplicate (Lost Original)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Processing Speed</label>
                                <select name="processing_speed"
                                    class="form-select border-primary-subtle bg-light-primary">
                                    <option value="Regular">Regular (PKR 2,000 - 15 Days)</option>
                                    <option value="Urgent">Urgent (PKR 4,000 - 03 Days)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Specify Semester (For Interim Only)</label>
                                <select name="selected_semester" class="form-select">
                                    <option value="0">Not Applicable (Full Degree)</option>
                                    <?php for ($i = 1; $i <= 8; $i++)
                                        echo "<option value='$i'>Up to Semester $i</option>"; ?>
                                </select>
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="fw-bold text-muted text-uppercase small border-bottom pb-2">Document Uploads
                                    (Attested Scans, PDF/JPG)</h6>
                            </div>

                            <div class="col-md-12">
                                <div class="row g-3 p-3 bg-light rounded-3 border">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold"><i
                                                class="bi bi-card-image me-1"></i>Student
                                            CNIC (Front)</label>
                                        <input type="file" name="doc_cnic" class="form-control"
                                            accept=".pdf,.jpg,.jpeg,.png" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold"><i
                                                class="bi bi-file-earmark-check me-1"></i>Matric Certificate</label>
                                        <input type="file" name="doc_matric" class="form-control"
                                            accept=".pdf,.jpg,.jpeg,.png" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold"><i
                                                class="bi bi-file-earmark-check me-1"></i>Intermediate
                                            Certificate</label>
                                        <input type="file" name="doc_inter" class="form-control"
                                            accept=".pdf,.jpg,.jpeg,.png" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold"><i
                                                class="bi bi-shield-check me-1"></i>Student Clearance Form</label>
                                        <input type="file" name="doc_clearance" class="form-control"
                                            accept=".pdf,.jpg,.jpeg,.png" required>
                                    </div>
                                    <div class="col-md-12">
                                        <span class="text-muted x-small">Maximum file size: 2MB per document. Attested
                                            copies are mandatory.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-4">
                        <button type="submit" name="submit_transcript_application"
                            class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow">
                            Submit Application & Upload Documents
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="clearanceModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content border-0 shadow-lg" method="POST" enctype="multipart/form-data"
                    style="border-radius: 25px;">
                    <div class="modal-header bg-success text-white p-4" style="border-radius: 25px 25px 0 0;">
                        <h5 class="fw-bold mb-0"><i class="bi bi-shield-check me-2"></i>Final Departmental Clearance
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <input type="hidden" name="service_type" value="Clearance">

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="fw-bold text-muted text-uppercase small border-bottom pb-2">Academic Profile
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Full Name</label>
                                <input type="text" name="student_name" class="form-control bg-light"
                                    value="<?php echo $student_name; ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Registration / Roll No</label>
                                <input type="text" class="form-control bg-light" value="<?php echo $raw_roll; ?>"
                                    readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Department HOD Name</label>
                                <input type="text" name="department_hod" class="form-control"
                                    placeholder="Enter HOD Name" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Gender</label>
                                <select name="gender" class="form-select" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Female">Others</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">CNIC Number</label>
                                <input type="text" name="cnic" class="form-control" placeholder="00000-0000000-0"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Clearance Type</label>
                                <select name="clearance_type" class="form-select">
                                    <option value="Final">Degree Completion (Final)</option>
                                    <option value="Migration">Migration / Transfer</option>
                                    <option value="Semester-wise">Interim Semester Clearance</option>
                                </select>
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="fw-bold text-muted text-uppercase small border-bottom pb-2">Self-Declaration
                                    Checklist</h6>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Library Books Returned?</label>
                                <select name="library_book_return" class="form-select">
                                    <option value="Yes">Yes</option>
                                    <option value="No">No / Not Applicable</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Sports Equipment Returned?</label>
                                <select name="sports_kit_return" class="form-select">
                                    <option value="Yes">Yes</option>
                                    <option value="No">No / Not Applicable</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Any Outstanding Dues?</label>
                                <input type="text" name="outstanding_dues" class="form-control"
                                    placeholder="e.g. None or Amount">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Reason for Requesting Clearance</label>
                                <textarea name="reason_for_leaving" class="form-control bg-light" rows="2"
                                    placeholder="Describe why you are initiating clearance..."></textarea>
                            </div>

                            <div class="col-12" id="clearance_upload_container" style="display: block !important;">
                                <label class="form-label small fw-bold">Upload Clearance Voucher / Slip</label>
                                <input type="file" name="doc_clearance" class="form-control form-control-sm" required>
                                <div class="form-text">Please upload a scanned copy or image of your clearance slip.
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-3 border-start border-4 border-success bg-light rounded-3">
                                    <p class="small text-muted mb-0">
                                        <i class="bi bi-info-circle-fill text-success me-2"></i>
                                        <strong>Note:</strong> Once submitted, your request will move to the Admin
                                        dashboard
                                        for final verification.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-4">
                        <button type="submit" name="submit_service"
                            class="btn btn-success w-100 py-3 rounded-pill fw-bold shadow">
                            Initiate Digital Clearance Workflow
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="hostelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg shadow">
            <form class="modal-content border-0" method="POST" style="border-radius: 25px;"
                enctype="multipart/form-data">
                <div class="modal-header bg-danger text-white p-4" style="border-radius: 25px 25px 0 0;">
                    <h5 class="fw-bold mb-0"><i class="bi bi-house-door-fill me-2"></i>Hostel Enrollment Form</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <input type="hidden" name="service_type" value="Hostel">

                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="fw-bold text-muted text-uppercase small border-bottom pb-2">Personal Information
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Full Name</label>
                            <input type="text" name="student_name" class="form-control bg-light"
                                value="<?php echo htmlspecialchars($student_name); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Student CNIC</label>
                            <input type="text" name="student_cnic" class="form-control bg-light"
                                placeholder="00000-0000000-0" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Father's Name</label>
                            <input type="text" name="father_name" class="form-control bg-light"
                                placeholder="As per CNIC" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Gender</label>
                            <select name="gender" class="form-select bg-light" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Female">Others</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Blood Group</label>
                            <select name="blood_group" class="form-select bg-light">
                                <option value="">Select</option>
                                <option>A+</option>
                                <option>A-</option>
                                <option>B+</option>
                                <option>B-</option>
                                <option>O+</option>
                                <option>O-</option>
                                <option>AB+</option>
                                <option>AB-</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Guardian Emergency Contact</label>
                            <input type="tel" name="guardian_contact" class="form-control bg-light"
                                placeholder="+92 3XX XXXXXXX" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Medical Conditions / Allergies</label>
                            <input type="text" name="medical_condition" class="form-control bg-light"
                                placeholder="Any allergies or chronic conditions?">
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-muted text-uppercase small border-bottom pb-2">Hostel & Mess
                                Preferences</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Room Category (Occupancy)</label>
                            <select name="hostel_room_type" class="form-select border-danger-subtle" required>
                                <option value="Standard">Standard (Triple Occupancy - Economy)</option>
                                <option value="Premium">Premium (Double Occupancy - Shared)</option>
                                <option value="Luxury">Luxury (Single Occupancy - Private)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mess Preference</label>
                            <select name="mess_preference" class="form-select border-danger-subtle" required>
                                <option value="Both">Standard (Veg + Non-Veg)</option>
                                <option value="Veg">Strictly Vegetarian</option>
                            </select>
                        </div>

                        <div class="col-md-12 mt-3">
                            <label class="form-label small fw-bold">Upload Hostel Fee Voucher (Scan)</label>
                            <input type="file" name="doc_hostel_voucher" class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-4">
                    <button type="submit" name="submit_service"
                        class="btn btn-danger w-100 py-3 rounded-pill fw-bold shadow-sm">
                        Generate Enrollment Request
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="transportModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content border-0 shadow-lg" method="POST" enctype="multipart/form-data"
                style="border-radius: 25px;">
                <div class="modal-header bg-indigo-grad text-white p-4"
                    style="border-radius: 25px 25px 0 0; background: linear-gradient(135deg, #818cf8, #4f46e5);">
                    <h5 class="fw-bold mb-0"><i class="bi bi-bus-front-fill me-2"></i>Bus Routes & Pass Registration
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <input type="hidden" name="service_type" value="Transport">

                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="fw-bold text-muted text-uppercase small border-bottom pb-2">Student Information
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Full Name</label>
                            <input type="text" name="student_name" class="form-control"
                                value="<?php echo htmlspecialchars($student_name); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold">CNIC Number</label>
                            <input type="text" name="cnic" class="form-control" placeholder="00000-0000000-0" required>
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-muted text-uppercase small border-bottom pb-2">Route Information
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Select Route</label>
                            <select name="transport_route" class="form-select border-primary-subtle" required>
                                <option value="">Choose a Route...</option>
                                <option value="Route A - Rawalpindi">Route A - Rawalpindi</option>
                                <option value="Route B - Islamabad">Route B - Islamabad</option>
                                <option value="Route C - Taxila/Wah Cantt">Route C - Taxila/Wah Cantt</option>
                                <option value="Route D - Hassan Abdal">Route D - Hassan Abdal</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Pickup Point / Stop</label>
                            <input type="text" name="pickup_point" class="form-control"
                                placeholder="Enter your specific stop name" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Bus Number (If known)</label>
                            <input type="text" name="bus_number" class="form-control" placeholder="e.g., BUS-01">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Shift Timing</label>
                            <select name="shift_timing" class="form-select">
                                <option value="Morning">Morning</option>
                                <option value="Evening">Evening</option>
                            </select>
                        </div>

                        <div class="col-md-12 mt-3">
                            <label class="form-label small fw-bold">Upload Transport Voucher (Scan)</label>
                            <input type="file" name="doc_transport_voucher" class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="submit" name="submit_service"
                        class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow">
                        Submit Transport Registration
                    </button>
                </div>
            </form>
        </div>


        //before footer
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const clearanceContainer = document.getElementById('clearance_upload_container');

            // Use the select dropdown ID instead of querySelectorAll for better accuracy
            const serviceSelect = document.querySelector('select[name="service_type"]');

            if (serviceSelect) {
                serviceSelect.addEventListener('change', function () {
                    // If service is Clearance, show it. Otherwise hide it.
                    if (clearanceContainer) {
                        clearanceContainer.style.display = (this.value === 'Clearance') ? 'block' : 'none';
                    }
                });
            }
        });
    </script>
</body>

</html>