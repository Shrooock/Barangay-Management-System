<?php
require "./bootstrap/index.php";
$certs = $db->from("certificates")->exec();
echo "<pre>";
print_r($certs);
echo "</pre>";
unlink(__FILE__);
?>
