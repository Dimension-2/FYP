<?php
session_start();
// 1. Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "fyp"; // Consistent with your screenshot

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // FIX 1: Corrected function name (removed 'with') and made voucher_no dynamic
    $tid = mysqli_real_escape_string($conn, $_POST['tid']);
    $voucher_no = mysqli_real_escape_string($conn, $_POST['voucher_no']); 
    
    // FIX 2: Security - Ensure voucher_no is not empty
    if (empty($voucher_no)) {
        die("Error: Voucher number is missing.");
    }

    // 2. Handle File Upload
    $target_dir = "uploads/slips/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = $_FILES["slip"]["name"];
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // FIX 3: Unique filename to prevent overwriting
    $new_filename = "slip_" . $voucher_no . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;

    // FIX 4: Simple file type validation
    $allowed_types = array("jpg", "jpeg", "png", "pdf");
    if (in_array($file_extension, $allowed_types)) {
        
        if (move_uploaded_file($_FILES["slip"]["tmp_name"], $target_file)) {
            // 3. Update Database Record
            // Logic: Setting status to 'Pending' allows Admin to see and edit/verify in future
            $sql = "UPDATE vouchers SET 
                    transaction_id = '$tid', 
                    slip_path = '$target_file', 
                    status = 'Pending' 
                    WHERE voucher_no = '$voucher_no'";

            if ($conn->query($sql) === TRUE) {
                // Redirect back to main page with success message
                header("Location: feevoucher.php?success=1");
                exit();
            } else {
                echo "Error updating record: " . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        echo "Error: Only JPG, JPEG, PNG, and PDF files are allowed.";
    }
}

$conn->close();
?>