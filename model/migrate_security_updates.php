<?php
$host = 'localhost';
$db   = 'bsms';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

echo "Updating database schema (PDO)...\n";

// Add backup_key and SMTP settings to tblbrgy_info
$smtp_cols = [
    'backup_key' => "VARCHAR(255) DEFAULT 'AUTHORIZED_FLASH_DRIVE_KEY'",
    'smtp_host' => "VARCHAR(100) DEFAULT 'smtp.gmail.com'",
    'smtp_port' => "INT DEFAULT 587",
    'smtp_username' => "VARCHAR(100) DEFAULT NULL",
    'smtp_password' => "VARCHAR(255) DEFAULT NULL"
];

foreach ($smtp_cols as $col => $def) {
    $check = $pdo->query("SHOW COLUMNS FROM `tblbrgy_info` LIKE '$col'")->fetch();
    if (!$check) {
        $pdo->exec("ALTER TABLE `tblbrgy_info` ADD COLUMN `$col` $def");
        echo "Added '$col' column to 'tblbrgy_info'.\n";
    }
}

// Add email and OTP columns to users
$columns = [
    'email' => "VARCHAR(100) DEFAULT NULL AFTER `user_type`",
    'otp_code' => "VARCHAR(10) DEFAULT NULL AFTER `email`",
    'otp_expires' => "TIMESTAMP NULL DEFAULT NULL AFTER `otp_code`"
];

foreach ($columns as $col => $def) {
    $check = $pdo->query("SHOW COLUMNS FROM `users` LIKE '$col'")->fetch();
    if (!$check) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `$col` $def");
        echo "Added '$col' column to 'users'.\n";
    }
}

echo "Migration complete.\n";
?>
