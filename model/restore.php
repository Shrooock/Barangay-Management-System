<?php

include '../server/server.php';

if (!empty($_FILES)) {
    // Validating SQL file type by extensions
    if (
        !in_array(strtolower(pathinfo($_FILES["backup_file"]["name"], PATHINFO_EXTENSION)), array(
            "sql"
        ))
    ) {

        $_SESSION['message'] = 'Invalid File Type';
        $_SESSION['status'] = 'danger';

        if (isset($_SERVER["HTTP_REFERER"])) {
            header("Location: " . $_SERVER["HTTP_REFERER"]);
        }
    } else {
        if (is_uploaded_file($_FILES["backup_file"]["tmp_name"])) {
            move_uploaded_file($_FILES["backup_file"]["tmp_name"], $_FILES["backup_file"]["name"]);
            $response = restoreMysqlDB($_FILES["backup_file"]["name"], $conn);

            if ($response) {
                $_SESSION['message'] = 'Database restored successfully.';
                $_SESSION['status'] = 'success';
            } else {
                $_SESSION['message'] = "Database not restored completely.";
                $_SESSION['status'] = 'danger';
            }
        }

        if (isset($_SERVER["HTTP_REFERER"])) {
            header("Location: " . $_SERVER["HTTP_REFERER"]);
        }
    }
}



function restoreMysqlDB($filePath, $conn)
{
    $tempSql = '';
    $error = '';

    if (file_exists($filePath)) {
        $lines = file($filePath);

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            // Skip empty lines or SQL comments
            if ($trimmedLine == '' || strpos($trimmedLine, '--') === 0 || strpos($trimmedLine, '#') === 0 || strpos($trimmedLine, '/*') === 0) {
                continue;
            }

            $tempSql .= $line;

            // Check if the line ends with a semicolon (indicating end of statement)
            if (substr($trimmedLine, -1) == ';') {
                if (!$conn->query($tempSql)) {
                    $error .= $conn->error . "\n";
                }
                $tempSql = '';
            }
        }

        @unlink($filePath); // Clean up uploaded file
        return empty($error);
    }
    return false;
}
