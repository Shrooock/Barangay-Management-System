<?php
require "./bootstrap/index.php";

$db->query("ALTER TABLE certificate_templates ADD COLUMN is_default TINYINT(1) DEFAULT 0")->exec();

echo "Database updated successfully.";
unlink(__FILE__);
?>
