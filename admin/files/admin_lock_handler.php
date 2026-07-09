<?php
session_start();
// Admin authentication check here

$conn = new mysqli("localhost", "root", "", "fyp");

$action = $_POST['action'] ?? '';
$state = (int)($_POST['state'] ?? 0);

if ($action === 'toggle') {
    $teacher = $conn->real_escape_string($_POST['teacher']);
    // Upsert the lock state
    $sql = "INSERT INTO teacher_feedback_locks (teacher_name, is_locked) 
            VALUES ('$teacher', $state) 
            ON DUPLICATE KEY UPDATE is_locked = $state";
    $conn->query($sql);
} elseif ($action === 'global') {
    // Insert/Update all existing teachers
    $teachers = $conn->query("SELECT DISTINCT teacher_name FROM course_assignments WHERE teacher_name != ''");
    while($row = $teachers->fetch_assoc()) {
        $t = $conn->real_escape_string($row['teacher_name']);
        $conn->query("INSERT INTO teacher_feedback_locks (teacher_name, is_locked) VALUES ('$t', $state) ON DUPLICATE KEY UPDATE is_locked = $state");
    }
}
echo "Success";
?>