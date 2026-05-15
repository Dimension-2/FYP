<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// 1. Fetch ALL data at once into an array
$timetable_data = [];
$result = $conn->query("SELECT * FROM timetable");
while ($row = $result->fetch_assoc()) {
    // Key by day and start_time (e.g., ['Monday']['08:00:00'] = row_data)
    $timetable_data[$row['day_name']][$row['start_time']] = $row;
}

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$start_hour = 8; $end_hour = 16;
$time_slots = [];
for ($i = $start_hour; $i < $end_hour; $i++) {
    $time_key = str_pad($i, 2, "0", STR_PAD_LEFT) . ":00:00";
    $label = date("h:i A", strtotime($time_key)) . " - " . date("h:i A", strtotime("+1 hour", strtotime($time_key)));
    $time_slots[$time_key] = $label;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/header.css">
    <link rel="stylesheet" href="assets/timetable.css">
</head>
<body class="bg-light">

<div class="main-wrapper d-flex">
    <div class="no-print"><?php include('includes/navbar.php'); ?></div>

    <div class="content-area flex-grow-1">
        <div class="no-print"><?php include('includes/header.php'); ?></div>

        <div class="container-fluid px-4 mt-4 mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <div>
                    <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar3-range me-2 text-teal"></i>UNIVERSITY TIME TABLE</h4>
                    <p class="text-muted small mb-0">Wah Campus | Session 2026</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-white border shadow-sm btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#prefBox">
                        <i class="bi bi-sliders me-1"></i> Preferences
                    </button>
                    <button class="btn btn-dark shadow-sm btn-sm" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print Schedule
                    </button>
                </div>
            </div>

            <div class="collapse no-print mb-4" id="prefBox">
                <div class="card card-body border-0 shadow-sm bg-white rounded-4">
                    <h6 class="fw-bold mb-3 small">UI SETTINGS</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="small text-muted mb-2 d-block">Display Density</label>
                            <div class="btn-group btn-group-sm w-100">
                                <button type="button" class="btn btn-outline-secondary active" id="viewSpacious">Spacious</button>
                                <button type="button" class="btn btn-outline-secondary" id="viewCompact">Compact</button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted mb-2 d-block">Highlight Type</label>
                            <select class="form-select form-select-sm" id="typeFilter">
                                <option value="all">Show All</option>
                                <option value="Lab">Labs Only</option>
                                <option value="Class">Lectures Only</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="timetable-container border-0" id="timetableMain">
                <div class="timetable-grid" id="gridMatrix">
                    <div class="grid-header">DAYS / TIME</div>
                    <?php foreach($time_slots as $label): ?>
                        <div class="grid-header"><?php echo $label; ?></div>
                    <?php endforeach; ?>

                    <?php foreach($days as $day): ?>
                        <div class="day-label"><?php echo strtoupper(substr($day, 0, 3)); ?></div>
                        
                        <?php 
                        $skip_slots = 0; 
                        foreach($time_slots as $start_time => $label): 
                            if ($skip_slots > 0) { $skip_slots--; continue; }
                        ?>
                            <div class="slot">
                                <div class="ruler-container">
                                    <div class="mark"></div>
                                    <div class="mark mark-30"></div>
                                    <div class="mark"></div>
                                </div>

<?php 
if ($row = ($timetable_data[$day][$start_time] ?? null)):
    $start = strtotime($row['start_time']);
    $end = strtotime($row['end_time']);
    $hours = round(($end - $start) / 3600);
    $span_class = ($hours > 1) ? "span-" . $hours : "";
    $skip_slots = $hours - 1;
?>
    <div class="class-card <?php echo $span_class; ?> filter-item" 
         data-type="<?php echo $row['type']; ?>"
         style="background: <?php echo $row['color_code']; ?>;">
        
        <span class="subject text-truncate"><?php echo $row['subject_name']; ?></span>
        <span class="meta text-truncate"><i class="bi bi-person-badge"></i> <?php echo $row['instructor_name']; ?></span>
        <span class="meta text-truncate"><i class="bi bi-geo-alt"></i> <?php echo $row['location']; ?></span>
        
        <div class="time-footer">
            <?php echo date('h:i', $start) . " - " . date('h:i A', $end); ?>
        </div>
    </div>
<?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Preference Logic
    const gridMatrix = document.getElementById('gridMatrix');
    const viewSpacious = document.getElementById('viewSpacious');
    const viewCompact = document.getElementById('viewCompact');
    const typeFilter = document.getElementById('typeFilter');

    // Switch View Density
    viewCompact.addEventListener('click', () => {
        gridMatrix.classList.add('compact-view');
        viewCompact.classList.add('active');
        viewSpacious.classList.remove('active');
    });

    viewSpacious.addEventListener('click', () => {
        gridMatrix.classList.remove('compact-view');
        viewSpacious.classList.add('active');
        viewCompact.classList.remove('active');
    });

    // Live Filtering
    typeFilter.addEventListener('change', (e) => {
        const selected = e.target.value;
        document.querySelectorAll('.filter-item').forEach(card => {
            if (selected === 'all' || card.getAttribute('data-type') === selected) {
                card.style.display = 'flex';
                card.style.opacity = '1';
            } else {
                card.style.opacity = '0.1';
            }
        });
    });
</script>
</body>
</html>
<?php $conn->close(); ?>