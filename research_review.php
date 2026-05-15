<?php 
session_start();
$page_title = "Research Review"; 

// Database connection
$conn = mysqli_connect("localhost", "root", "", "fyp");
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

// Ensure user is logged in
if (!isset($_SESSION['registration_no'])) {
    header("Location: login.php");
    exit();
}

$student_reg = $_SESSION['registration_no'];

// Fetch total count for the badge (Filtered by student)
$stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM research_submissions WHERE registration_no = ?");
$stmt_count->bind_param("s", $student_reg);
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$count_data = $count_result->fetch_assoc();
$total_submissions = $count_data['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Review - University of Wah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <link rel="stylesheet" href="assets/research_review.css">
    <style>
        .count-badge {
            font-size: 0.7rem;
            vertical-align: middle;
            margin-left: 8px;
            background: #e7f1ff;
            color: #0d6efd;
            padding: 4px 10px;
            border-radius: 50px;
        }
    </style>
</head>
<body>

<div class="main-wrapper d-flex">
    <div class="no-print">
        <?php include('includes/navbar.php'); ?>
    </div>

    <div class="content-area flex-grow-1">
        <div class="no-print">
            <?php include('includes/header.php'); ?>
        </div>

        <div class="container-fluid px-4 mt-4">
            <div class="row align-items-center mb-4 no-print">
                <div class="col-md-6">
                    <h3 class="fw-bold text-dark m-0">Research Review</h3>
                    <p class="text-muted small">Home / Academic / Research Review</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <button class="btn btn-primary shadow-sm me-2" data-bs-toggle="modal" data-bs-target="#submitReviewModal">
                        <i class="bi bi-plus-circle me-1"></i> New Submission
                    </button>
                    <button class="btn btn-dark shadow-sm" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print Report
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h6 class="m-0 fw-bold text-primary">Your Research Submissions</h6>
                        <span class="count-badge fw-bold"><?php echo $total_submissions; ?> Total</span>
                    </div>
                    <div class="no-print" style="width: 250px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" id="researchSearch" class="form-control border-start-0" placeholder="Search by title...">
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="researchTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Project Title</th>
                                    <th>Submission Date</th>
                                    <th>Review Status</th>
                                    <th>Supervisor</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <?php
// Fetch only this student's submissions
$stmt = $conn->prepare("SELECT * FROM research_submissions WHERE registration_no = ? ORDER BY id DESC");
$stmt->bind_param("s", $student_reg);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $status_class = ($row['status'] == 'Accepted') ? 'bg-success' : 
                       (($row['status'] == 'Rejected') ? 'bg-danger' : 'bg-warning text-dark');
        ?>
        <tr>
            <td class="ps-4">
                <div class="fw-bold search-target"><?php echo htmlspecialchars($row['title']); ?></div>
                <span class="text-muted small">ID: RES-<?php echo $row['id']; ?></span>
            </td>
            <td><?php echo date('d-M-Y', strtotime($row['submission_date'])); ?></td>
            <td><span class="badge <?php echo $status_class; ?>"><?php echo $row['status']; ?></span></td>
            <td><?php echo htmlspecialchars($row['supervisor'] ?? 'Pending Assignment'); ?></td>
            <td class="pe-4 text-end">
                <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary me-1">
                    <i class="bi bi-eye"></i>
                </a>
                <form action="delete_research.php" method="POST" style="display:inline;">
    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
<input type="hidden" name="registration_no" value="<?php echo $student_reg; ?>">
    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this submission?')">
        <i class="bi bi-trash"></i>
    </button>
</form>
            </td>
        </tr>
        <?php
    }
} else {
    echo "<tr><td colspan='5' class='text-center py-5 text-muted'>No submissions found.</td></tr>";
}
?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="submitReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Submit Research for Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="researchForm" action="research_handler.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Research Title</label>
                        <input type="text" name="research_title" class="form-control" placeholder="Enter title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Abstract</label>
                        <textarea name="abstract" class="form-control" rows="4" placeholder="Brief summary of your research..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Upload Document (PDF only)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-cloud-upload"></i></span>
                            <input type="file" name="research_file" class="form-control" accept=".pdf" required>
                        </div>
                    </div>
                    <div class="modal-footer px-0 pb-0 border-0 mt-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary px-4">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('researchSearch').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#researchTable tbody tr');

        rows.forEach(row => {
            const titleElement = row.querySelector('.search-target');
            if(titleElement) {
                const titleText = titleElement.textContent.toLowerCase();
                row.style.display = titleText.includes(filter) ? "" : "none";
            }
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>