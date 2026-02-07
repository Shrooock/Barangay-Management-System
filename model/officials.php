<?php

require "../server/server.php";
require "../helpers/method-vars.php";

if (getBody("register-official", $_POST)) {
	try {
		$name = isset($_POST['name']) ? trim($_POST['name']) : '';
		$position = isset($_POST['position']) ? trim($_POST['position']) : '';
		$start = isset($_POST['start']) ? trim($_POST['start']) : '';
		$end = isset($_POST['end']) ? trim($_POST['end']) : '';
		$status = isset($_POST['status']) ? trim($_POST['status']) : '';

		// Validate all required fields are not empty
		$missingFields = [];
		if (empty($name))
			$missingFields[] = "Fullname";
		if (empty($position) || $position === '')
			$missingFields[] = "Position";
		if (empty($start))
			$missingFields[] = "Term Start";
		if (empty($end))
			$missingFields[] = "Term End";
		if (empty($status))
			$missingFields[] = "Status";

		if (!empty($missingFields)) {
			$_SESSION["message"] = "Missing required fields: " . implode(", ", $missingFields) . ". Please make sure all fields are filled.";
			$_SESSION["status"] = "danger";

			header("location: ../officials.php");
			return $conn->close();
		}

		// Check for duplicate active official names
		$check_dup = $conn->query("SELECT id FROM tblofficials WHERE `name` = '$name' AND `status` = 'Active' LIMIT 1");
		if ($check_dup->num_rows > 0) {
			$_SESSION['message'] = "Duplicate Error: An active official named <b>$name</b> already exists!";
			$_SESSION['status'] = 'danger';
			header("Location: ../officials.php");
			return $conn->close();
		}

		// Check if the position is Barangay Captain (either ID 4 or name contains "Captain")
		$isCaptainPosition = ($position == 4);
		$isKagawadPosition = false;

		$pos_q = "SELECT `position` FROM tblposition WHERE id = $position";
		$pos_res = $conn->query($pos_q);
		if ($pos_res && $row = $pos_res->fetch_assoc()) {
			if (stripos($row['position'], 'Captain') !== false) {
				$isCaptainPosition = true;
			}
			if (stripos($row['position'], 'Kagawad') !== false) {
				$isKagawadPosition = true;
			}
		}

		if ($isCaptainPosition) {
			$hasCaptain = $db
				->from("tblofficials")
				->whereRaw("(`position` = 4 OR id IN (SELECT tblofficials.id FROM tblofficials JOIN tblposition ON tblposition.id = tblofficials.position WHERE tblposition.position LIKE '%Captain%'))")
				->where("status", "Active")
				->select([
					"id" => "tblofficials.id",
				])
				->exec();

			if (!empty($hasCaptain)) {
				$_SESSION["message"] = "A captain is already registered!";
				$_SESSION["status"] = "danger";

				header("location: ../officials.php");
				return $conn->close();
			}
		}

		if ($isKagawadPosition) {
			$kg_res = $conn->query("SELECT COUNT(*) as total FROM tblofficials JOIN tblposition ON tblposition.id = tblofficials.position WHERE tblposition.position LIKE '%Kagawad%' AND tblofficials.status = 'Active'");
			$kg_data = $kg_res->fetch_assoc();
			$kagawadCount = $kg_data['total'];

			if ($kagawadCount >= 7) {
				$_SESSION["message"] = "Limit reached: You can only have a maximum of 7 active Kagawads!";
				$_SESSION["status"] = "danger";

				header("location: ../officials.php");
				return $conn->close();
			}
		}

		// Handle image upload
		$image = null;
		if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
			$uploadDir = '../assets/uploads/';
			$imageName = date('YmdHis') . '_' . basename($_FILES['image']['name']);
			$targetPath = $uploadDir . $imageName;

			if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
				$image = $imageName;
			}
		}

		// Sanitize inputs
		$name = $conn->real_escape_string($name);
		$position = $conn->real_escape_string($position);
		$start = $conn->real_escape_string($start);
		$end = $conn->real_escape_string($end);
		$status = $conn->real_escape_string($status);

		// Build values array - only include image if it exists
		$values = [
			"name" => $name,
			"position" => $position,
			"termstart" => $start,
			"termend" => $end,
			"status" => $status,
		];

		// Only add image if it was uploaded
		if ($image !== null) {
			$values["image"] = $image;
		}

		$result = $db
			->insert("tblofficials")
			->values($values)
			->exec();

		if ($result["status"] !== true) {
			$_SESSION["message"] = "Failed to add official. Please try again.";
			$_SESSION["status"] = "danger";

			header("location: ../officials.php");
			return $conn->close();
		}

		$_SESSION["message"] = "Official registered";
		$_SESSION["status"] = "success";

		header("location: ../officials.php");
		$conn->close();

		return;
	} catch (Exception $e) {
		$_SESSION["message"] = "Error: " . $e->getMessage();
		$_SESSION["status"] = "danger";
		header("location: ../officials.php");
		$conn->close();
	}
}
