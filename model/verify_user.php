<?php
include '../bootstrap/index.php';

if (!isAdmin()) {
	if (isset($_SERVER["HTTP_REFERER"])) {
		header("Location: " . $_SERVER["HTTP_REFERER"]);
	}
}

$id 	= $conn->real_escape_string($_GET['id']);
$status = $conn->real_escape_string($_GET['status']);

if ($id != '') {
	$query 		= "UPDATE users SET is_verified = '$status' WHERE id = '$id'";

	$result 	= $conn->query($query);

	if ($result === true) {
		$_SESSION['message'] = $status ? 'User has been verified!' : 'User verification has been revoked!';
		$_SESSION['status'] = 'success';
	} else {
		$_SESSION['message'] = 'Something went wrong!';
		$_SESSION['status'] = 'danger';
	}
} else {

	$_SESSION['message'] = 'Missing User ID!';
	$_SESSION['status'] = 'danger';
}

header("Location: ../users.php");
$conn->close();
