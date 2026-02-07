<?php
include '../bootstrap/index.php';

if (!isAdmin()) {
	header("Location: ../dashboard.php");
}

if (isset($_POST['import'])) {
	$filename = $_FILES["file"]["tmp_name"];

	if ($_FILES["file"]["size"] > 0) {
		$file = fopen($filename, "r");

		// Read header
		$headers = fgetcsv($file);
		$header_map = [];
		if ($headers) {
			foreach ($headers as $index => $header) {
				$header_map[strtolower(trim($header))] = $index;
			}
		}

		$success_count = 0;
		$error_count = 0;

		/**
		 * Define full mapping for robust import
		 */
		$mapping = [
			'national_id' => ['national id'],
			'citizenship' => ['citizenship'],
			'firstname'   => ['first name', 'firstname'],
			'middlename'  => ['middle name', 'middlename'],
			'lastname'    => ['last name', 'lastname'],
			'alias'       => ['alias'],
			'birthplace'  => ['birthplace', 'birtplace'],
			'birthdate'   => ['birthdate'],
			'age'         => ['age'],
			'civilstatus' => ['civil status', 'civilstatus'],
			'gender'      => ['gender'],
			'voterstatus' => ['voter status', 'voterstatus'],
			'voter_precinct_number' => ['voter precinct number', "voter's precinct number", 'voter_precinct_number'],
			'identified_as' => ['identified as', 'identified_as'],
			'phone'       => ['contact number', 'phone'],
			'email'       => ['email address', 'email'],
			'occupation'  => ['occupation'],
			'address'     => ['address'],
			'resident_type' => ['resident type', 'resident_type'],
			'is_4ps'      => ['is 4ps', 'is_4ps'],
			'is_pwd'      => ['is pwd', 'is_pwd'],
			'is_senior'   => ['is senior', 'is_senior'],
			'remarks'     => ['remarks']
		];

		while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {
			$data = [];
			foreach ($mapping as $db_column => $header_variants) {
				$val = null;
				foreach ($header_variants as $variant) {
					if (isset($header_map[$variant])) {
						$val = $getData[$header_map[$variant]] ?? null;
						break;
					}
				}
				$data[$db_column] = $conn->real_escape_string($val ?? '');
			}

			$national_id = $data['national_id'];
			$firstname   = $data['firstname'];
			$lastname    = $data['lastname'];

			// Check if resident already exists by national_id or name combination
			$check = $conn->query("SELECT id FROM residents WHERE (national_id != '' AND national_id = '$national_id') OR (firstname='$firstname' AND lastname='$lastname')");
			
			if($check->num_rows == 0) {
				$columns = implode(', ', array_keys($data));
				$values = "'" . implode("', '", array_values($data)) . "'";
				
				$sql = "INSERT INTO residents ($columns) VALUES ($values)";
				
				if ($conn->query($sql)) {
					$success_count++;
				} else {
					$error_count++;
				}
			} else {
				$error_count++; // Duplicate
			}
		}

		fclose($file);

		$_SESSION['message'] = "Import complete! Success: $success_count, Failed: $error_count";
		if($error_count > 0) {
			$_SESSION['message'] .= ". Last Error: " . $conn->error;
		}
		$_SESSION['status'] = 'success';
	} else {
		$_SESSION['message'] = "Please upload a valid CSV file.";
		$_SESSION['status'] = 'danger';
	}
}

header("Location: ../resident.php");
$conn->close();
