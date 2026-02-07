<?php
require_once "../bootstrap/index.php";
require_once "../helpers/mailer.php";

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$email = $_POST['email'] ?? '';

if (empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Email is required']);
    exit;
}

// Generate 6-digit OTP
$otp = sprintf("%06d", mt_rand(1, 999999));

// Store in session for verification
$_SESSION['registration_otp'] = $otp;
$_SESSION['registration_email'] = $email;

if (sendOTP($email, $otp)) {
    echo json_encode(['status' => 'success', 'message' => 'Verification code sent to ' . $email]);
} else {
    $error = $_SESSION['email_error'] ?? 'Unknown error';
    unset($_SESSION['email_error']);
    echo json_encode(['status' => 'error', 'message' => 'Failed to send email: ' . $error]);
}
