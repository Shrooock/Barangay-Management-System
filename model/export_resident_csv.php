<?php

require("../server/server.php");

// get Users
$query = "SELECT national_id, citizenship, firstname, middlename, lastname, alias, birthplace, birthdate, age, civilstatus, gender, voterstatus, voter_precinct_number, identified_as, phone, email, occupation, address, resident_type, is_4ps, is_pwd, is_senior, remarks FROM residents";
if (!$result = $conn->query($query)) {
    exit($conn->error);
}

$users = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Residents.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array('National ID', 'Citizenship', 'First Name', 'Middle Name', 'Last Name', 'Alias', 'Birthplace', 'Birthdate', 'Age', 'Civil Status', 'Gender', 'Voter Status', 'Voter Precinct Number', 'Identified As', 'Contact Number', 'Email Address', 'Occupation', 'Address', 'Resident Type', 'Is 4PS', 'Is PWD', 'Is Senior', 'Remarks'));

if (count($users) > 0) {
    foreach ($users as $row) {
        fputcsv($output, $row);
    }
}


?>