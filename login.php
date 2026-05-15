<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_id = $_POST['registration_no']; // This field handles both Reg No and Employee ID
    $pass = $_POST['password'];

    // 1. Check if the ID exists in the users table
    $stmt = $conn->prepare("SELECT password FROM users WHERE registration_no = ?");
    $stmt->bind_param("s", $login_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($pass === $row['password']) {

            // 2. Check if this is a Teacher (IDs starting with 'T-' or existing in teachers table)
            $checkTeacher = $conn->prepare("SELECT employee_id FROM teachers WHERE employee_id = ?");
            $checkTeacher->bind_param("s", $login_id);
            $checkTeacher->execute();
            $isTeacher = $checkTeacher->get_result();

            if ($isTeacher->num_rows > 0) {
                // TEACHER REDIRECT
                $_SESSION['teacher_id'] = $login_id;
                header("Location: teacher/files/profile.php");
            } else {
                // STUDENT REDIRECT
                $_SESSION['registration_no'] = $login_id;
                header("Location: profile.php");
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "ID not found in the system.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>University Portal Login</title>
    <link rel="stylesheet" href="assets/login.css">
    <style>
        /* Minimalist "Next Level" Login Adjustments */
        body {
            background: #fcfcfc;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: white;
            padding: 40px;
            border: 2px solid #1a1a1a;
            box-shadow: 15px 15px 0px #0992d1;
            width: 350px;
            text-align: center;
        }

        h2 {
            text-transform: uppercase;
            letter-spacing: 4px;
            font-weight: 200;
            border-bottom: 3px solid #10b981;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1.5px solid #d1d5db;
            border-radius: 0;
            box-sizing: border-box;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        input:focus {
            border-color: #10b981;
            outline: none;
            background: #f0fff4;
        }

        button {
            width: 100%;
            padding: 15px;
            background: #1a1a1a;
            color: white;
            border: none;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #10b981;
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
        }

        .error {
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>PORTAL LOGIN</h2>
        <?php if (isset($error))
            echo "<p class='error' style='color:#e74c3c;'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="registration_no" placeholder="REG NO / TEACHER ID" required>
            <input type="password" name="password" placeholder="PASSWORD" required>
            <button type="submit">Enter Portal</button>
        </form>
    </div>
</body>

</html>