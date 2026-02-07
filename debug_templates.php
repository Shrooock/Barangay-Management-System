<?php
require_once "bootstrap/index.php";
$templates = $db->from("certificate_templates")->exec();
echo "<pre>";
print_r($templates);
echo "</pre>";
unlink(__FILE__);
?>
