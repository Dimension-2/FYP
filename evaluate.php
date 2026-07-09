<?php
session_start();
$page_title = "Faculty Evaluation";
$current_page = 'faculty.php';

// Authentication Check
if (!isset($_SESSION['registration_no'])) {
    header("Location: login.php");
    exit();
}

// --- DATABASE CONNECTION ---
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get Course Info from URL correctly mapping to the buttons link format
$course_name = isset($_GET['course_title']) ? htmlspecialchars($_GET['course_title']) : "Selected Course";
$course_code = isset($_GET['course_code']) ? htmlspecialchars($_GET['course_code']) : "N/A";
$teacher_name = isset($_GET['instructor']) ? htmlspecialchars($_GET['instructor']) : "";

// --- SUBMISSION HANDLING ---
$submitted = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reg_no = $_SESSION['registration_no'];
    $answers = [];

    for ($i = 0; $i < 20; $i++) {
        $answers["q" . $i] = isset($_POST["q" . $i]) ? $_POST["q" . $i] : 0;
    }

    $scores_json = json_encode($answers);
    $comments = $conn->real_escape_string($_POST['comments'] ?? '');
    $sub_date = date('Y-m-d');

    // Consolidated Query NOW INCLUDES teacher_name
    $stmt = $conn->prepare("INSERT INTO faculty_feedback (registration_no, course_code, course_title, teacher_name, scores, comments, submission_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $reg_no, $course_code, $course_name, $teacher_name, $scores_json, $comments, $sub_date);

    if ($stmt->execute()) {
        $submitted = true;
    }
    $stmt->close();
}

$questions = [
    "The instructor is prepared for each class.",
    "The instructor demonstrates knowledge of the subject matter.",
    "The instructor provides clear learning objectives.",
    "The instructor communicates effectively.",
    "The instructor encourages student participation.",
    "The instructor is available during office hours.",
    "The instructor provides timely feedback on assignments.",
    "The grading criteria are clearly defined.",
    "The course material is well-organized.",
    "The instructor uses diverse teaching methods.",
    "The instructor treats students with respect.",
    "The instructor links theory with practical examples.",
    "The instructor maintains a professional environment.",
    "The workload of the course is manageable.",
    "The instructor stimulates interest in the subject.",
    "The instructor follows the course outline.",
    "The instructor uses modern tools/technology effectively.",
    "The assignments/exams reflect the course content.",
    "The instructor addresses student queries effectively.",
    "Overall, I am satisfied with this instructor's performance."
];
$totalQuestions = count($questions);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Feedback - <?php echo $course_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <link rel="stylesheet" href="assets/faculty.css">
    <link rel="stylesheet" href="assets/evaluation.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --teal-color: #00cba9;
        }

        body,
        html {
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        .main-wrapper {
            display: flex;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
        }

        .content-area {
            flex-grow: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width));
        }

        .progress-sticky-container {
            position: sticky;
            top: 0;
            z-index: 1050;
            background: white;
            padding: 20px 30px;
            border-bottom: 2px solid #edf2f7;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .bg-teal {
            background-color: var(--teal-color) !important;
        }

        .text-teal {
            color: var(--teal-color) !important;
        }

        .btn-submit-feedback {
            background-color: var(--teal-color);
            border: none;
            transition: transform 0.2s, background-color 0.2s;
        }

        .btn-submit-feedback:hover {
            background-color: #00a88d;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>

    <div class="main-wrapper">
        <div id="sidebar">
            <?php include('includes/navbar.php'); ?>
        </div>

        <div class="content-area">
            <?php include('includes/header.php'); ?>

            <div class="container-fluid px-4">

                <div class="progress-sticky-container">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="small fw-bold text-muted text-uppercase">Evaluation Progress</span>
                            <h5 id="progress-text" class="fw-bold text-teal mb-0">0% Complete</h5>
                        </div>
                        <i class="bi bi-bar-chart-line text-teal fs-4"></i>
                    </div>
                    <div class="progress" style="height: 12px; border-radius: 20px; background-color: #e9ecef;">
                        <div id="progress-bar" class="progress-bar bg-teal progress-bar-striped progress-bar-animated"
                            role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0"
                            aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-5" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <div class="header-section d-flex align-items-center gap-3 mb-4">
                            <i class="bi bi-pencil-square fs-3 text-teal"></i>
                            <h4 class="fw-bold m-0" style="color: #2d3748;">Faculty Evaluation Form</h4>
                        </div>

                        <div class="evaluation-info mb-4 p-3 bg-light rounded border-start border-4 border-teal">
                            <p class="mb-1 text-muted small text-uppercase fw-bold">Current Course</p>
                            <h6 class="mb-0 fw-bold"><?php echo $course_code; ?> - <?php echo $course_name; ?></h6>
                        </div>

                        <form action="" method="POST" id="evaluationForm">
                            <div class="row g-4">
                                <?php foreach ($questions as $index => $q): ?>
                                    <div class="col-md-6">
                                        <div class="question-row border p-4 rounded h-100 bg-white shadow-sm">
                                            <p class="question-text fw-semibold mb-3">
                                                <?php echo ($index + 1) . ". " . $q; ?>
                                            </p>
                                            <div class="options-group">
                                                <?php
                                                $opts = [5 => "Strongly Agree", 4 => "Agree", 3 => "Neutral", 2 => "Disagree", 1 => "Strongly Disagree"];
                                                foreach ($opts as $val => $label): ?>
                                                    <label class="option-item d-block mb-1">
                                                        <input type="radio" name="q<?php echo $index; ?>"
                                                            value="<?php echo $val; ?>" required>
                                                        <span class="ms-2"><?php echo $label; ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="comment-section mt-5 p-4 bg-white rounded shadow-sm border">
                                <label class="fw-bold mb-3">Additional Comments (Optional)</label>
                                <textarea name="comments" class="form-control" rows="4"
                                    placeholder="Share your honest feedback here..."></textarea>
                            </div>

                            <div class="text-center mt-5">
                                <button type="submit"
                                    class="btn-submit-feedback px-5 py-3 text-white fw-bold rounded-pill shadow">
                                    <i class="bi bi-send-check me-2"></i> Submit Feedback
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-body text-center p-5">
                    <div class="checkmark-wrapper mb-4"
                        style="width:80px; height:80px; background:#e0f7f4; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin: 0 auto;">
                        <i class="bi bi-check-lg" style="font-size: 40px; color: #00cba9;"></i>
                    </div>
                    <h4 class="fw-bold">Feedback Submitted!</h4>
                    <p class="text-muted">Your response has been saved. Redirecting...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('evaluationForm');
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            const totalQuestions = <?php echo $totalQuestions; ?>;

            form.addEventListener('change', function () {
                const checkedRadios = form.querySelectorAll('input[type="radio"]:checked').length;
                const percentage = Math.round((checkedRadios / totalQuestions) * 100);

                progressBar.style.width = percentage + '%';
                progressBar.setAttribute('aria-valuenow', percentage);
                progressText.innerText = percentage + '% Complete';

                if (percentage === 100) {
                    progressText.style.color = '#198754';
                }
            });

            <?php if ($submitted): ?>
                const myModal = new bootstrap.Modal(document.getElementById('successModal'));
                myModal.show();
                setTimeout(() => { window.location.href = 'faculty.php'; }, 2000);
            <?php endif; ?>
        });
    </script>
</body>

</html>