<?php
include '../bootstrap/index.php';

if (!isset($_SESSION['username']) && $_SESSION['role'] != 'administrator') {
	if (isset($_SERVER["HTTP_REFERER"])) {
		header("Location: " . $_SERVER["HTTP_REFERER"]);
	}
}

$id 	= $conn->real_escape_string($_GET['id']);

if ($id != '') {
	$query 		= "UPDATE residents SET deleted_at = NULL WHERE id = '$id'";

	$result 	= $conn->query($query);

	if ($result === true) {
		$_SESSION['message'] = 'Resident has been restored!';
		$_SESSION['status'] = 'success';

		$res_query = $conn->query("SELECT firstname, lastname FROM residents WHERE id = '$id'");
		$res_data = $res_query->fetch_assoc();
		$res_name = $res_data ? $res_data['firstname'] . ' ' . $res_data['lastname'] : "ID: $id";

		logActivity($conn, "RESTORE", "RESIDENT", $res_name, "Restored resident from archive");
	} else {
		$_SESSION['message'] = 'Something went wrong!';
		$_SESSION['status'] = 'danger';
	}
} else {

	$_SESSION['message'] = 'Missing Resident ID!';
	$_SESSION['status'] = 'danger';
}

header("Location: ../archived_residents.php");
$conn->close();
