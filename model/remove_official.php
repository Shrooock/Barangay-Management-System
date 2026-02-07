<?php
include '../bootstrap/index.php';

if (!isset($_SESSION['username']) && $_SESSION['role'] != 'administrator') {
	if (isset($_SERVER["HTTP_REFERER"])) {
		header("Location: " . $_SERVER["HTTP_REFERER"]);
		return $conn->close();
	}
}

$id 	= $conn->real_escape_string($_GET['id']);

if ($id != '') {
	$off_query = $conn->query("SELECT name FROM tblofficials WHERE id = '$id'");
	$off_data = $off_query->fetch_assoc();
	$off_name = $off_data ? $off_data['name'] : "ID: $id";

	$query 		= "DELETE FROM tblofficials WHERE id = '$id'";

	$result 	= $conn->query($query);

	if ($result === true) {
		$_SESSION['message'] = 'Official has been removed!';
		$_SESSION['status'] = 'danger';
		logActivity($conn, "DELETE", "OFFICIAL", $off_name, "Permanently removed official from database");
	} else {
		$_SESSION['message'] = 'Something went wrong!';
		$_SESSION['status'] = 'danger';
	}
} else {

	$_SESSION['message'] = 'Missing Official ID!';
	$_SESSION['status'] = 'danger';
}

header("Location: ../officials.php");
$conn->close();
