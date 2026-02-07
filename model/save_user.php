<?php
include "../bootstrap/index.php";

if (!isset($_SESSION["username"])) {
	header("Location: ../login.php");
	exit;
}

$user = $conn->real_escape_string($_POST["username"]);
$email = $conn->real_escape_string($_POST["email"]);
$pass = sha1($conn->real_escape_string($_POST["pass"]));
$usertype = "administrator";
$profile = isset($_POST["profileimg"]) ? $conn->real_escape_string($_POST["profileimg"]) : ""; // base 64 image
$profile2 = isset($_FILES["img"]["name"]) ? $_FILES["img"]["name"] : "";
// change profile2 name
$newName = !empty($profile2) ? date("dmYHis") . str_replace(" ", "", $profile2) : "";

// image file directory
$target = "../assets/uploads/" . basename($newName);

if (!empty($user) && !empty($pass) && !empty($usertype)) {
	// Security Check: Ensure email was verified via registration OTP
	if (!isset($_SESSION['registration_verified'])) {
		$_SESSION["message"] = "Email verification not initiated. Please verify your email!";
		$_SESSION["status"] = "danger";
		header("Location: ../users.php");
		exit;
	}
	if ($_SESSION['registration_verified'] !== true) {
		$_SESSION["message"] = "Email NOT verified. Please enter the code sent to your Gmail!";
		$_SESSION["status"] = "danger";
		header("Location: ../users.php");
		exit;
	}
	if (strtolower($_SESSION['registration_email']) !== strtolower($email)) {
		$_SESSION["message"] = "Email mismatch! You verified " . $_SESSION['registration_email'] . " but tried to save " . $email;
		$_SESSION["status"] = "danger";
		header("Location: ../users.php");
		exit;
	}

	$query = "SELECT * FROM users WHERE username='$user'";
	$res = $conn->query($query);

	if ($res->num_rows) {
		$_SESSION["message"] = "Please enter a unique username!";
		$_SESSION["status"] = "danger";
	} else {
		$success = false;
		if (!empty($profile) && !empty($profile2)) {
			$insert = "INSERT INTO users (`username`, `email`, `password`, user_type, avatar, is_verified) VALUES ('$user', '$email', '$pass', '$usertype','$profile', 0)";
			$result = $conn->query($insert);
			if ($result === true) {
				$success = true;
				logActivity($conn, "ADD", "USER", $user, "Added new $usertype user with avatar (pending verification)");
			} else {
				$_SESSION["message"] = "Database Error: " . $conn->error;
				$_SESSION["status"] = "danger";
			}
		} elseif (!empty($profile) && empty($profile2)) {
			$insert = "INSERT INTO users (`username`, `email`, `password`, user_type, avatar, is_verified) VALUES ('$user', '$email', '$pass', '$usertype','$profile', 0)";
			$result = $conn->query($insert);
			if ($result === true) {
				$success = true;
				logActivity($conn, "ADD", "USER", $user, "Added new $usertype user with camera avatar (pending verification)");
			} else {
				$_SESSION["message"] = "Database Error: " . $conn->error;
				$_SESSION["status"] = "danger";
			}
		} elseif (empty($profile) && !empty($profile2)) {
			$insert = "INSERT INTO users (`username`, `email`, `password`, user_type, avatar, is_verified) VALUES ('$user', '$email', '$pass', '$usertype','$newName', 0)";
			$result = $conn->query($insert);
			move_uploaded_file($_FILES["img"]["tmp_name"], $target);
			if ($result === true) {
				$success = true;
				logActivity($conn, "ADD", "USER", $user, "Added new $usertype user with uploaded avatar (pending verification)");
			} else {
				$_SESSION["message"] = "Database Error: " . $conn->error;
				$_SESSION["status"] = "danger";
			}
		} else {
			$insert = "INSERT INTO users (`username`, `email`, `password`, user_type, is_verified) VALUES ('$user', '$email', '$pass', '$usertype', 0)";
			$result = $conn->query($insert);
			if ($result === true) {
				$success = true;
				logActivity($conn, "ADD", "USER", $user, "Added new $usertype user (pending verification)");
			} else {
				$_SESSION["message"] = "Database Error: " . $conn->error;
				$_SESSION["status"] = "danger";
			}
		}

		if ($success) {
			$_SESSION["message"] = "User added! They will need to verify their email on first login.";
			$_SESSION["status"] = "success";
			// Clear registration verification data ONLY on success
			unset($_SESSION['registration_otp']);
			unset($_SESSION['registration_email']);
			unset($_SESSION['registration_verified']);
		}
	}
} else {
	$_SESSION["message"] = "Please fill up the form completely!";
	$_SESSION["status"] = "danger";
}

header("Location: ../users.php");
$conn->close();
