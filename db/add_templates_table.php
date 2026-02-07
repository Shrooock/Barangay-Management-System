<?php
require_once __DIR__ . "/../bootstrap/index.php";

$sql = "CREATE TABLE IF NOT EXISTS `certificate_templates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `certificate_id` int unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `content` longtext NOT NULL, -- This will now store JSON design
  `styles` longtext,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($sql)) {
    echo "Table 'certificate_templates' created successfully.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}
