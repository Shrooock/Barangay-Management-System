<?php
require_once "bootstrap/index.php";

if (!isset($_SESSION["pending_2fa"])) {
    header("Location: login.php");
    exit;
}

$error = '';
if (isset($_POST['verify_otp'])) {
    $id = $_SESSION["id"];
    $otp = $_POST['otp'];
    $now = date('Y-m-d H:i:s');
    
    // Verify OTP and check expiration
    $query = "SELECT * FROM users WHERE id = $id AND otp_code = '$otp' AND otp_expires > '$now'";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        // Correct OTP
        unset($_SESSION["pending_2fa"]);
        $conn->query("UPDATE users SET otp_code = NULL, otp_expires = NULL, is_verified = 1 WHERE id = $id");
        
        $_SESSION["message"] = "2FA Verified! Welcome to the system.";
        $_SESSION["status"] = "success";
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid or expired verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "templates/header.php"; ?>
    <title>Two-Factor Authentication</title>
    <style>
        .otp-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .login .wrapper.wrapper-login .container-login {
            background: #fff !important;
            color: #333 !important;
        }
        .login .wrapper.wrapper-login .container-login .form-control {
            background: #f8f9fa !important;
            color: #333 !important;
            border: 1px solid #ebedf2 !important;
        }
    </style>
</head>
<body class="login">
    <div class="wrapper wrapper-login">
        <div class="container container-login animated fadeIn">
            <h3 class="text-center">Two-Factor Authentication</h3>
            <div class="login-form">
                <p class="text-center text-muted">A verification code has been sent to <strong><?= $_SESSION['email'] ?></strong>.</p>
                
                
                

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group pb-0">
                        <label for="otp" class="mb-1">Verification Code</label>
                        <input id="otp" name="otp" type="text" class="form-control" required placeholder="6-digit code">
                    </div>
                    <div class="form-action mb-3">
                        <button type="submit" name="verify_otp" class="btn btn-primary btn-rounded btn-login btn-block">Verify</button>
                    </div>
                    <div class="text-center">
                        <a href="model/logout.php" class="link">Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include "templates/footer.php"; ?>
</body>
</html>
