<?php
require_once "../bootstrap/index.php";

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$otp = $_POST['otp'] ?? '';

if (isset($_SESSION['registration_otp']) && $_SESSION['registration_otp'] === $otp) {
    $_SESSION['registration_verified'] = true;
    echo json_encode(['status' => 'success', 'message' => 'Verified']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid code']);
}
