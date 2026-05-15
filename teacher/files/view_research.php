<?php
session_start();

// 1. DATABASE CONNECTION
$conn = mysqli_connect("localhost", "root", "", "fyp");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 2. TEACHER DATA SETUP
$teacher_name = "";
if (isset($_SESSION['teacher_id'])) {
    $emp_id = $_SESSION['teacher_id'];
    $user_query = mysqli_query($conn, "SELECT full_name FROM teachers WHERE employee_id = '$emp_id'");
    if ($user_row = mysqli_fetch_assoc($user_query)) {
        $teacher_name = $user_row['full_name'];
    }
}
$data = ['full_name' => $teacher_name];

// 3. HANDLE STATUS UPDATES
$update_success = false;
$new_status_val = "";
if (isset($_POST['update_status'])) {
    $sub_id = $_POST['submission_id'];
    $new_status = $_POST['status'];

    $update_stmt = $conn->prepare("UPDATE research_submissions SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_status, $sub_id);
    if ($update_stmt->execute()) {
        $update_success = true;
        $new_status_val = $new_status;
    }
}

// // 4. INCLUDE BARS (Paths adjusted for being inside 'files' folder)
// include_once('../Bars/header.php');
// include_once('../Bars/sidebar.php');
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/profile_style.css">
<link rel="stylesheet" href="../css/teacher_course.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* CSS Conflict & UI Perfection */
    .research-view-wrapper {
        padding: 25px;
        background: #f8f9fa;
        min-height: 100vh;
        /* Ensures content doesn't hide behind sidebar if using absolute positioning */
        margin-left: 0;
    }

    .res-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        background: #ffffff;
    }

    .table thead th {
        background: #1e293b !important;
        color: #ffffff !important;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.025em;
        padding: 15px;
        border: none;
    }

    .table tbody td {
        padding: 15px;
        color: #475569;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    .status-badge {
        font-weight: 600;
        padding: 6px 12px;
        font-size: 0.75rem;
    }

    .btn-pdf-view {
        background-color: #fff1f2;
        color: #e11d48;
        border: 1px solid #fecdd3;
        transition: all 0.2s;
    }

    .btn-pdf-view:hover {
        background-color: #e11d48;
        color: #ffffff;
    }

    .status-select-custom {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        font-size: 0.85rem;
        border-radius: 6px;
        padding: 5px;
        cursor: pointer;
    }
</style>

<div class="d-flex" id="wrapper">
    <?php include('../Bars/sidebar.php'); ?>

    <div id="page-content-wrapper">
        <?php include('../Bars/header.php'); ?>

        <div class="content-wrapper">
            <div class="research-view-wrapper">
                <div class="container-fluid">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 style="font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.025em;">
                                Research Submissions
                            </h3>
                            <p class="text-muted mb-0">Review, Approve, or Reject student research work.</p>
                        </div>
                    </div>

                    <div class="card res-card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Registration No</th>
                                            <th>Project Title</th>
                                            <th>Submitted On</th>
                                            <th>Status</th>
                                            <th class="text-center">Review Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT * FROM research_submissions ORDER BY id DESC";
                                        $result = mysqli_query($conn, $query);

                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                // Dynamic Status Colors
                                                $badge_style = 'bg-warning text-dark';
                                                if ($row['status'] == 'Accepted')
                                                    $badge_style = 'bg-success text-white';
                                                if ($row['status'] == 'Rejected')
                                                    $badge_style = 'bg-danger text-white';

                                                /** * PATH FIX LOGIC:
                                                 * Your file is in teacher/files/view_research.php
                                                 * Your PDF is in teacher/uploads/research/
                                                 * We go UP one level with ../ to reach teacher/
                                                 **/
                                                // This goes out of 'files' and into the 'uploads' folder
                                                // This explicitly points to the folder where PDFs are kept
                                                // This uses the root of your project. 
// Change 'fyp' to the exact name of your folder in htdocs if it is different.
                                                // Use rawurlencode to handle spaces and special characters in the filename
                                                // Go UP one level from 'files' folder then into uploads
                                                $clean_path = str_replace(' ', '%20', $row['file_path']);
                                                $pdf_url = "../" . $clean_path;

                                                ?>
                                                <tr>
                                                    <td class="ps-4 fw-bold text-dark"><?php echo $row['registration_no']; ?>
                                                    </td>
                                                    <td>
                                                        <div class="fw-semibold text-truncate" style="max-width: 300px;">
                                                            <?php echo htmlspecialchars($row['title']); ?>
                                                        </div>
                                                    </td>
                                                    <td><?php echo date('d M, Y', strtotime($row['submission_date'])); ?></td>
                                                    <td>
                                                        <span
                                                            class="badge rounded-pill status-badge <?php echo $badge_style; ?>">
                                                            <?php echo $row['status']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center align-items-center gap-2">
                                                            <a href="<?php echo $pdf_url; ?>" target="_blank"
                                                                class="btn btn-sm btn-pdf-view">
                                                                <i class="bi bi-file-earmark-pdf-fill me-1"></i> View PDF
                                                            </a>

                                                            <form method="POST" class="m-0">
                                                                <input type="hidden" name="submission_id"
                                                                    value="<?php echo $row['id']; ?>">
                                                                <input type="hidden" name="update_status" value="1">
                                                                <select name="status" class="status-select-custom"
                                                                    onchange="confirmStatusChange(this)">
                                                                    <option disabled selected>Action</option>
                                                                    <option value="Under Review">Under Review</option>
                                                                    <option value="Accepted">Accept</option>
                                                                    <option value="Rejected">Reject</option>
                                                                </select>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center py-5 text-muted'>No submissions to display.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Success Notification
    <?php if ($update_success): ?>
        Swal.fire({
            icon: 'success',
            title: 'Updated!',
            text: 'Submission status changed to <?php echo $new_status_val; ?>',
            confirmButtonColor: '#1e293b'
        });
    <?php endif; ?>

    // Confirmation Logic
    function confirmStatusChange(selectElement) {
        const status = selectElement.value;
        const form = selectElement.closest('form');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are changing the status to ${status}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: status === 'Accepted' ? '#10b981' : (status === 'Rejected' ? '#ef4444' : '#f59e0b'),
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            } else {
                selectElement.selectedIndex = 0; // Reset
            }
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>