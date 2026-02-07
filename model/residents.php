<?php

include "../bootstrap/index.php";

use function _\camelCase as _camelCase;

function toNumberOrZero($val)
{
	if (is_numeric($val)) {
		return $val + 0;
	}
	return 0;
}

if (isset($_POST["register-resident"])) {
	try {
		$national_id = getBody("national_id", $_POST);
		$citizenship = getBody("citizenship", $_POST);
		$address = getBody("address", $_POST);
		$fname = getBody("fname", $_POST);
		$mname = getBody("mname", $_POST);
		$lname = getBody("lname", $_POST);
		$alias = getBody("alias", $_POST);
		$birthplace = getBody("birthplace", $_POST);
		$birthdate = getBody("birthdate", $_POST);
		$age = toNumberOrZero(getBody("age", $_POST));
		$civil_status = getBody("civil_status", $_POST);
		$gender = getBody("gender", $_POST);
		$voter_status = getBody("voter_status", $_POST);
		$voter_precinct_number = getBody("voter_precinct_number", $_POST);
		$identified_as = getBody("identified_as", $_POST);
		$email = getBody("email", $_POST);
		$number = getBody("number", $_POST);
		$occupation = getBody("occupation", $_POST);
		$is_4ps = toNumberOrZero(getBody("is_4ps", $_POST));
		$is_pwd = toNumberOrZero(getBody("is_pwd", $_POST));
        $is_solo_parent = toNumberOrZero(getBody("is_solo_parent", $_POST));
        $is_head_of_family = toNumberOrZero(getBody("is_head_of_family", $_POST));
        $resident_type = toNumberOrZero(getBody("resident_type", $_POST));
		$is_senior = $age > 60;

		$profileimg = getBody("profileimg", $_POST);


		$requiredFields = [
			"National ID" => $national_id,
			"Citizenship" => $citizenship,
			"Address" => $address,
			"First Name" => $fname,
			"Middle Name" => $mname,
			"Last Name" => $lname,
			"Alias" => $alias,
			"Birth Place" => $birthplace,
			"Birth Date" => $birthdate,
			"Age" => $age,
			"Civil Status" => $civil_status,
			"Gender" => $gender,
			"Voter Status" => $voter_status,
			"Email" => $email,
			"Contact Number" => $number,
			"Occupation" => $occupation,
		];

		/**
		 * Check required fields
		 */
		$emptyRequiredField = array_find_key($requiredFields, fn($item) => empty($item));

		if ($emptyRequiredField) {
			$_SESSION["message"] = "<b>$emptyRequiredField</b> is required!";
			$_SESSION["status"] = "danger";

			if ($_SERVER["HTTP_REFERER"]) {
				header("Location: " . $_SERVER["HTTP_REFERER"]);
				return $conn->close();
			}

			header("location: ../resident-register.php");
			return $conn->close();
		}

		/**
		 * Handle profile image
		 */
		$profileCamera = getBody("profileimg", $_POST); // base 64 image
		$profileFile = $_FILES["img"];

		$imgFilename = empty($profileCamera) ? null : $profileCamera;

		if ($profileFile["name"]) {
			$uniqId = uniqid(date("YmdhisU"));
			$ext = pathinfo($profileFile["name"], PATHINFO_EXTENSION);
			$imgFilename = "$uniqId.$ext";
			$imgDir = "../assets/uploads/$imgFilename";

			move_uploaded_file($profileFile["tmp_name"], $imgDir);
		}

		/**
		 * Check for duplicates
		 */
		$check_dup = $conn->query("SELECT id FROM residents WHERE national_id = '$national_id' AND deleted_at IS NULL LIMIT 1");
		if ($check_dup->num_rows > 0) {
			$_SESSION["message"] = "Duplicate Error: A resident with National ID <b>$national_id</b> already exists!";
			$_SESSION["status"] = "danger";

			if ($_SERVER["HTTP_REFERER"]) {
				header("Location: " . $_SERVER["HTTP_REFERER"]);
				return $conn->close();
			}
			header("location: ../resident-register.php");
			return $conn->close();
		}

		$result = $db
			->insert("residents")
			->values([
				"national_id" => $national_id,
				"citizenship" => $citizenship,
				"firstname" => $fname,
				"middlename" => $mname,
				"lastname" => $lname,
				"alias" => $alias,
				"birthplace" => $birthplace,
				"birthdate" => $birthdate,
				"age" => $age,
				"civilstatus" => $civil_status,
				"gender" => $gender,
				"voterstatus" => $voter_status,
				"voter_precinct_number" => $voter_precinct_number,
				"identified_as" => $identified_as,
				"phone" => $number,
				"email" => $email,
				"occupation" => $occupation,
				"address" => $address,
				"account_id" => null,
				"is_4ps" => $is_4ps,
				"is_pwd" => $is_pwd,
                "is_solo_parent" => $is_solo_parent,
                "is_head_of_family" => $is_head_of_family,
                "resident_type" => $resident_type,
				"is_senior" => $is_senior,
				"picture" => $imgFilename,
			])
			->exec();

		$_SESSION["message"] = "Resident registered";
		$_SESSION["status"] = "success";

		logActivity($conn, "ADD", "RESIDENT", "$fname $lname", "Registered new resident");

		if ($_SERVER["HTTP_REFERER"]) {
			header("Location: " . $_SERVER["HTTP_REFERER"]);
			return $conn->close();
		}

		header("location: ../resident-register.php");
		return $conn->close();
	} catch (Exception $e) {
		echo "<pre>";
		var_dump($e);
		echo "</pre>";
		throw $e;
	}
}

if (isset($_POST["update-resident"])) {
	$resident_id = getBody("resident_id", $_POST);
	$national_id = getBody("national_id", $_POST);
	$citizenship = getBody("citizenship", $_POST);
	$address = getBody("address", $_POST);
	$fname = getBody("fname", $_POST);
	$mname = getBody("mname", $_POST);
	$lname = getBody("lname", $_POST);
	$alias = getBody("alias", $_POST);
	$birthplace = getBody("birthplace", $_POST);
	$birthdate = getBody("birthdate", $_POST);
	$age = getBody("age", $_POST);
	$civil_status = getBody("civil_status", $_POST);
	$gender = getBody("gender", $_POST);
	$voter_status = getBody("voter_status", $_POST);
	$voter_precinct_number = getBody("voter_precinct_number", $_POST);
	$identified_as = getBody("identified_as", $_POST);
	$email = getBody("email", $_POST);
	$number = getBody("number", $_POST);
	$occupation = getBody("occupation", $_POST);
	$is_pwd = getBody("is_pwd", $_POST);
	$is_4ps = getBody("is_4ps", $_POST);
    $is_solo_parent = getBody("is_solo_parent", $_POST);
    $is_head_of_family = getBody("is_head_of_family", $_POST);
    $resident_type = getBody("resident_type", $_POST);

	$requiredFields = [
		"National ID" => $national_id,
		"Citizenship" => $citizenship,
		"Address" => $address,
		"First Name" => $fname,
		"Middle Name" => $mname,
		"Last Name" => $lname,
		"Alias" => $alias,
		"Birth Place" => $birthplace,
		"Birth Date" => $birthdate,
		"Age" => $age,
		"Civil Status" => $civil_status,
		"Gender" => $gender,
		"Voter Status" => $voter_status,
		"Email" => $email,
		"Contact Number" => $number,
		"Occupation" => $occupation,
	];

	/**
	 * Check required fields
	 */
	$emptyRequiredField = array_find_key($requiredFields, fn($item) => empty($item));

	if ($emptyRequiredField) {
		$_SESSION["message"] = "<b>$emptyRequiredField</b> is required!";
		$_SESSION["status"] = "danger";

		header("location: ../resident-view.php?resident_id=$resident_id");
		return $conn->close();
	}

	$resident_details = $db
		->from("residents")
		->where("residents.id", $resident_id)
		->first()
		->select([
			"avatar" => "residents.picture",
		])
		->exec();

	if (!$resident_details) {
		$_SESSION["message"] = "Resident not found!";
		$_SESSION["status"] = "danger";

		header("location: ../resident-view.php?resident_id=$resident_id");
		return $conn->close();
	}

	/**
	 * Handle profile image
	 */
	$profile_camera = getBody("profileimg", $_POST); // base 64 image
	$profile_file = $_FILES["img"];

	$img_filename = empty($profile_camera) ? $resident_details["avatar"] : $profile_camera;

	if ($profile_file["name"]) {
		$uniqId = uniqid(date("YmdhisU"));
		$ext = pathinfo($profile_file["name"], PATHINFO_EXTENSION);
		$img_filename = "$uniqId.$ext";
		$imgDir = "../assets/uploads/$img_filename";

		move_uploaded_file($profile_file["tmp_name"], $imgDir);
	}

	/**
	 * Check for duplicates (excluding current resident)
	 */
	$check_dup = $conn->query("SELECT id FROM residents WHERE national_id = '$national_id' AND id != '$resident_id' AND deleted_at IS NULL LIMIT 1");
	if ($check_dup->num_rows > 0) {
		$_SESSION["message"] = "Duplicate Error: Another resident with National ID <b>$national_id</b> already exists!";
		$_SESSION["status"] = "danger";

		header("location: ../resident-view.php?resident_id=$resident_id");
		return $conn->close();
	}

	$result = $db
		->update("residents")
		->where("id", $resident_id)
		->set([
			"national_id" => $national_id,
			"citizenship" => $citizenship,
			"firstname" => $fname,
			"middlename" => $mname,
			"lastname" => $lname,
			"alias" => $alias,
			"birthplace" => $birthplace,
			"birthdate" => $birthdate,
			"age" => $age,
			"address" => $address,
			"civilstatus" => $civil_status,
			"gender" => $gender,
			"voterstatus" => $voter_status,
			"voter_precinct_number" => $voter_precinct_number,
			"identified_as" => $identified_as,
			"phone" => $number,
			"email" => $email,
			"occupation" => $occupation,
			"is_pwd" => $is_pwd,
			"is_4ps" => $is_4ps,
            "is_solo_parent" => $is_solo_parent,
            "is_head_of_family" => $is_head_of_family,
            "resident_type" => $resident_type,
			"is_senior" => $age >= 60 ? 1 : 0,
			"picture" => $img_filename,
		])
		->exec();

	$_SESSION["message"] = "Resident details updated";
	$_SESSION["status"] = "success";

	logActivity($conn, "EDIT", "RESIDENT", "$fname $lname", "Updated resident details");

	header("location: ../resident-view.php?resident_id=$resident_id");
	return $conn->close();
}

if (isset($_GET["remove-resident"])) {
	$resident_id = $_GET["id"];

	$db
		->delete("residents")
		->where("residents.id", $resident_id)
		->exec();

	$_SESSION["message"] = "Resident removed";
	$_SESSION["status"] = "success";

	$res_query = $conn->query("SELECT firstname, lastname FROM residents WHERE id = '$resident_id'");
	$res_data = $res_query->fetch_assoc();
	$res_name = $res_data ? $res_data['firstname'] . ' ' . $res_data['lastname'] : "ID: $resident_id";

	logActivity($conn, "DELETE", "RESIDENT", $res_name, "Permanently removed resident from database");

	header("location: ../archived_residents.php");
	return $conn->close();
}

if (isset($_GET["unset-4ps"])) {
	$resident_id = $_GET["id"];

	$db
		->update("residents")
		->where("residents.id", $resident_id)
		->set([
			"is_4ps" => 0,
		])
		->exec();

	$_SESSION["message"] = "Resident removed from 4Ps";
	$_SESSION["status"] = "success";

	$res_query = $conn->query("SELECT firstname, lastname FROM residents WHERE id = '$resident_id'");
	$res_data = $res_query->fetch_assoc();
	$res_name = $res_data ? $res_data['firstname'] . ' ' . $res_data['lastname'] : "ID: $resident_id";

	logActivity($conn, "EDIT", "RESIDENT", $res_name, "Unset 4Ps beneficiary status");

	header("location: ../4ps-residents.php");
	return $conn->close();
}
