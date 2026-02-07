<?php
if (!function_exists('logActivity')) {
    function logActivity($conn, $action, $target_type, $target_name = null, $details = null) {
        if (!isset($_SESSION)) {
            session_start();
        }

        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;
        $user_id_val = $user_id ? $user_id : "NULL";

        // Escape inputs
        $action = $conn->real_escape_string($action);
        $target_type = $conn->real_escape_string($target_type);
        $target_name = $target_name ? "'" . $conn->real_escape_string($target_name) . "'" : "NULL";
        $details = $details ? "'" . $conn->real_escape_string($details) . "'" : "NULL";

        // Self-healing: Ensure activity_log table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'activity_log'");
        if ($table_check->num_rows == 0) {
            $create_table = "CREATE TABLE `activity_log` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT UNSIGNED DEFAULT NULL, 
                `action` VARCHAR(50) NOT NULL,    
                `target_type` VARCHAR(50) NOT NULL, 
                `target_name` VARCHAR(100) DEFAULT NULL, 
                `details` TEXT DEFAULT NULL,      
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $conn->query($create_table);
        }

        $query = "INSERT INTO activity_log (user_id, action, target_type, target_name, details) 
                  VALUES ($user_id_val, '$action', '$target_type', $target_name, $details)";
        
        return $conn->query($query);
    }
}
?>
