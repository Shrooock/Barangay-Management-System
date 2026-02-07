<?php
include '../bootstrap/index.php';

if (!isset($_SESSION['username'])) {
	if (isset($_SERVER["HTTP_REFERER"])) {
		header("Location: " . $_SERVER["HTTP_REFERER"]);
		return $conn->close();
	}
}

$id = $conn->real_escape_string($_POST['id']);
$name = $conn->real_escape_string($_POST['name']);
$pos = $conn->real_escape_string($_POST['position']);
$start = $conn->real_escape_string($_POST['start']);
$end = $conn->real_escape_string($_POST['end']);
$status = $conn->real_escape_string($_POST['status']);

if (!empty($id)) {
	// Check if this update would violate the Kagawad limit
	if ($status == 'Active') {
		$pos_q = "SELECT `position` FROM tblposition WHERE id = $pos";
		$pos_res = $conn->query($pos_q);
		if ($pos_res && $row = $pos_res->fetch_assoc()) {
			if (stripos($row['position'], 'Kagawad') !== false) {
				// Check how many OTHER active kagawads exist
				$kg_res = $conn->query("SELECT COUNT(*) as total FROM tblofficials JOIN tblposition ON tblposition.id = tblofficials.position WHERE tblposition.position LIKE '%Kagawad%' AND tblofficials.status = 'Active' AND tblofficials.id != $id");
				$kg_data = $kg_res->fetch_assoc();
				if ($kg_data['total'] >= 7) {
					$_SESSION['message'] = 'Limit reached: You already have 7 active Kagawads!';
					$_SESSION['status'] = 'danger';
					header("Location: ../officials.php");
					return $conn->close();
				}
			}
		}
	}

	// Handle image upload
	$image = isset($_POST['current_image']) ? $_POST['current_image'] : null;

	if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
		$uploadDir = '../assets/uploads/';
		$imageName = date('YmdHis') . '_' . basename($_FILES['image']['name']);
		$targetPath = $uploadDir . $imageName;

		if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
			// Delete old image if exists
			if (!empty($image) && file_exists($uploadDir . $image)) {
				unlink($uploadDir . $image);
			}
			$image = $imageName;
		}
	}

	$imageUpdate = $image ? ", `image`='$image'" : "";
	$query = "UPDATE tblofficials SET `name`='$name', `position`='$pos', termstart='$start', termend='$end', `status`='$status'$imageUpdate WHERE id=$id;";
	$result = $conn->query($query);

	if ($result === true) {

		$_SESSION['message'] = 'Brgy Official has been updated!';
		$_SESSION['status'] = 'success';
		logActivity($conn, "EDIT", "OFFICIAL", $name, "Updated official details for: $pos");
	} else {

		$_SESSION['message'] = 'Somethin went wrong!';
		$_SESSION['status'] = 'danger';
	}
} else {
	$_SESSION['message'] = 'No Brgy Official ID found!';
	$_SESSION['status'] = 'danger';
}

header("Location: ../officials.php");

$conn->close();
