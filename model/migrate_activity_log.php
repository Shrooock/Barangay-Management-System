<?php
include '../server/server.php';

$query = "CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL, 
    `action` VARCHAR(50) NOT NULL,    
    `target_type` VARCHAR(50) NOT NULL, 
    `target_name` VARCHAR(100) DEFAULT NULL, 
    `details` TEXT DEFAULT NULL,      
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($query)) {
    echo "Activity log table created successfully.";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
