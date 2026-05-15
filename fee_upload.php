<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if (!isset($_SESSION['registration_no'])) { header("Location: login.php"); exit(); }
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $voucher_no = $_POST['voucher_no'];
    $reg_no = $_SESSION['registration_no'];
    $amount = $_POST['amount'];
    $tid = $_POST['tid'];
    
    // 1. Check if record already exists in fee_vouchers, otherwise Insert
    $check_sql = "SELECT * FROM fee_vouchers WHERE voucher_no = '$voucher_no'";
    $check_res = $conn->query($check_sql);
    
    if($check_res->num_rows == 0) {
        $sql_v = "INSERT INTO fee_vouchers (voucher_no, registration_no, semester_label, issue_date, due_date, tuition_fee, status) 
                  VALUES ('$voucher_no', '$reg_no', 'Semester 7', '2025-10-20', '2025-11-05', '$amount', 'Pending Verification')";
        $conn->query($sql_v);
    } else {
        // Update status if it exists
        $conn->query("UPDATE fee_vouchers SET status='Pending Verification' WHERE voucher_no='$voucher_no'");
    }

    // 2. Handle File Upload
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_ext = pathinfo($_FILES["slip"]["name"], PATHINFO_EXTENSION);
    $file_name = "slip_" . $voucher_no . "_" . time() . "." . $file_ext;
    $target_path = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["slip"]["tmp_name"], $target_path)) {
        // 3. Save to fee_uploads table
        $sql_u = "INSERT INTO fee_uploads (voucher_id, transaction_id, slip_path, upload_time) 
                  VALUES ('$voucher_no', '$tid', '$target_path', NOW())";

        if ($conn->query($sql_u) === TRUE) {
            header("Location: index.php?success=1");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
$conn->close();
?>