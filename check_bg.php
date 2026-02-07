<?php
$conn = mysqli_connect('localhost', 'root', '', 'bsms');
if (!$conn) die('Connect Error: ' . mysqli_connect_error());
$res = mysqli_query($conn, "SELECT bg_brgy_cert, bg_indi_cert, bg_business_permit, bg_elect_cert FROM tblbrgy_info WHERE id=1");
$row = mysqli_fetch_assoc($res);
print_r($row);
mysqli_close($conn);
