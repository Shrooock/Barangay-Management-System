<?php
$query = "SELECT * FROM tblbrgy_info";
$result = $conn->query($query);
$row = $result->fetch_assoc();

if ($row) {
	$province = $row["province"];
	$town = $row["town"];
	$brgy = $row["brgy_name"];
	$number = $row["number"];
	$address = $row["address"] ?? '';
	$city_logo = $row["city_logo"];
	$brgy_logo = $row["brgy_logo"];
	$backup_key = isset($row["backup_key"]) ? $row["backup_key"] : 'AUTHORIZED_FLASH_DRIVE_KEY';
	$smtp_host = $row["smtp_host"] ?? 'smtp.gmail.com';
	$smtp_port = $row["smtp_port"] ?? 587;
	$smtp_username = $row["smtp_username"] ?? '';
	$smtp_password = $row["smtp_password"] ?? '';
	$bg_logo = isset($row["bg_logo"]) ? $row["bg_logo"] : null;
	$bg_brgy_cert = isset($row["bg_brgy_cert"]) ? $row["bg_brgy_cert"] : null;
	$bg_indi_cert = isset($row["bg_indi_cert"]) ? $row["bg_indi_cert"] : null;
	$bg_business_permit = isset($row["bg_business_permit"]) ? $row["bg_business_permit"] : null;
	$bg_elect_cert = isset($row["bg_elect_cert"]) ? $row["bg_elect_cert"] : null;
	$template_preference = $row["template_preference"] ?? 'both';
}

$pos_q = "SELECT * FROM tblposition ORDER BY `order` ASC";
$pos_r = $conn->query($pos_q);

$position = [];
while ($row = $pos_r->fetch_assoc()) {
	$position[] = $row;
}

$chair_q = "SELECT * FROM tblchairmanship";
$res_q = $conn->query($chair_q);

$chair = [];
while ($row = $res_q->fetch_assoc()) {
	$chair[] = $row;
}

?>
