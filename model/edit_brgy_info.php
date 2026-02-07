<?php
include '../server/server.php';

if (!isset($_SESSION['username'])) {
	if (isset($_SERVER["HTTP_REFERER"])) {
		header("Location: " . $_SERVER["HTTP_REFERER"]);
	}
	exit;
}

$province = $_POST['province'];
$brgy = $_POST['brgy'];
$town = $_POST['town'];
$number = $_POST['number'];
$address = $_POST['address'];

if (!empty($brgy) && !empty($town)) {
	$updateFields = [
		"`province`='" . $conn->real_escape_string($province) . "'",
		"`town`='" . $conn->real_escape_string($town) . "'",
		"`brgy_name`='" . $conn->real_escape_string($brgy) . "'",
		"`number`='" . $conn->real_escape_string($number) . "'",
		"`address`='" . $conn->real_escape_string($address) . "'"
	];

	// Optional fields
	if (isset($_POST['backup_key'])) {
		$updateFields[] = "`backup_key`='" . $conn->real_escape_string($_POST['backup_key']) . "'";
	}
	if (isset($_POST['smtp_username'])) {
		$updateFields[] = "`smtp_username`='" . $conn->real_escape_string($_POST['smtp_username']) . "'";
	}
	if (isset($_POST['smtp_password'])) {
		$updateFields[] = "`smtp_password`='" . $conn->real_escape_string($_POST['smtp_password']) . "'";
	}
	if (isset($_POST['template_preference'])) {
		$updateFields[] = "`template_preference`='" . $conn->real_escape_string($_POST['template_preference']) . "'";
	}

	$errors = [];
	$upload_dir = "../assets/uploads/";

	// Handle File Uploads
	$files = [
		'city_logo' => 'city_logo',
		'brgy_logo' => 'brgy_logo',
		'bg_logo' => 'bg_logo',
		'bg_brgy_cert' => 'bg_brgy_cert',
		'bg_indi_cert' => 'bg_indi_cert',
		'bg_business_permit' => 'bg_business_permit',
		'bg_elect_cert' => 'bg_elect_cert',
		'cert_template' => 'cert_template'
	];

	foreach ($files as $post_key => $db_col) {
		if (!empty($_FILES[$post_key]['name'])) {
			$filename = $_FILES[$post_key]['name'];
			$new_name = date('dmYHis') . str_replace(" ", "", $filename);
			$target = $upload_dir . basename($new_name);

			if (move_uploaded_file($_FILES[$post_key]['tmp_name'], $target)) {
				$updateFields[] = "`$db_col`='" . $conn->real_escape_string($new_name) . "'";
			} else {
				$errors[] = "Failed to upload $post_key.";
			}
		}
	}

	$query = "UPDATE tblbrgy_info SET " . implode(", ", $updateFields) . " WHERE id=1";

	if ($conn->query($query) === true) {
		$_SESSION['message'] = empty($errors) ? 'Barangay Info has been updated!' : 'Info updated, but some files failed: ' . implode(", ", $errors);
		$_SESSION['status'] = empty($errors) ? 'success' : 'warning';
	} else {
		$_SESSION['message'] = 'Something went wrong! ' . $conn->error;
		$_SESSION['status'] = 'danger';
	}
} else {
	$_SESSION['message'] = 'Please complete the form!';
	$_SESSION['status'] = 'danger';
}

if (isset($_SERVER["HTTP_REFERER"])) {
	header("Location: " . $_SERVER["HTTP_REFERER"]);
}

$conn->close();
