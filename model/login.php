<?php
include "../bootstrap/index.php";
include "../helpers/mailer.php";

$username = $conn->real_escape_string($_POST["username"]);
$password = $conn->real_escape_string($_POST["password"]);

if (!$username || !$password) {
	$_SESSION["message"] = "Username or password is empty!";
	$_SESSION["status"] = "danger";

	header("location: ../login.php");
	return $conn->close();
}

$hash = sha1($password);
$result = $conn->query("SELECT * FROM users WHERE username = '$username' AND password = '$hash'");
$fetchedData = $result->fetch_assoc();

if (!$fetchedData) {
	$_SESSION["message"] = "Username or Password is incorrect!";
	$_SESSION["status"] = "danger";

	header("location: ../login.php");
	return $conn->close();
}



$_SESSION["id"] = $fetchedData["id"];
$_SESSION["username"] = $fetchedData["username"];
$_SESSION["role"] = $fetchedData["user_type"];
$_SESSION["avatar"] = $fetchedData["avatar"];
$_SESSION["email"] = $fetchedData["email"];

// Check if user has email for 2FA
if (!empty($fetchedData["email"])) {
    // Generate 6-digit OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    // Save to DB
    $conn->query("UPDATE users SET otp_code = '$otp', otp_expires = '$expires' WHERE id = " . $fetchedData["id"]);
    
    // Store temporary data for verification
    $_SESSION["pending_2fa"] = true;
    
    // Send OTP email
    sendOTP($fetchedData["email"], $otp);
    
    // Redirect to OTP verification page
    header("Location: ../otp_verify.php");
    return $conn->close();
}

$_SESSION["message"] = "You have successfully logged in to Automated Barangay Services Management System!";
$_SESSION["status"] = "success";

header("location: ../dashboard.php");
return $conn->close();
