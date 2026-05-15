<?php
include('db_config.php');

if (isset($_GET['id']) && isset($_GET['type'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $type = mysqli_real_escape_string($conn, $_GET['type']);

    // Get the registration number to delete related profile/teacher data
    $user_res = mysqli_query($conn, "SELECT registration_no FROM users WHERE id = '$id'");
    $user_row = mysqli_fetch_assoc($user_res);
    $reg_no = $user_row['registration_no'];

    // Delete records from all potential linked tables
    mysqli_query($conn, "DELETE FROM users WHERE id = '$id'");
    mysqli_query($conn, "DELETE FROM profile WHERE registration_no = '$reg_no'");
    mysqli_query($conn, "DELETE FROM teachers WHERE employee_id = '$reg_no'");

    // Redirect back to the correct list
    $redirect = ($type == 'student') ? 'student_details.php' : 'teacher_details.php';
    header("Location: " . $redirect . "?status=deleted");
    exit();
}
?>