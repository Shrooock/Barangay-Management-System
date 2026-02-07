<?php
require "bootstrap/index.php";
$brgy_info = $db->from("tblbrgy_info")->where("id", 1)->first()->exec();
echo "Data for ID 1:\n";
print_r($brgy_info);
