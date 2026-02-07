<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOTP($email, $otp) {
    global $conn;

    // Fetch SMTP settings
    $res = $conn->query("SELECT smtp_host, smtp_port, smtp_username, smtp_password, brgy_name FROM tblbrgy_info WHERE id=1");
    $settings = $res->fetch_assoc();

    if (!$settings || empty($settings['smtp_username']) || empty($settings['smtp_password'])) {
        $_SESSION['email_error'] = "SMTP settings are not configured in Barangay Info.";
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $settings['smtp_username'];
        $mail->Password   = $settings['smtp_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom($settings['smtp_username'], $settings['brgy_name'] . " Management System");
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code';
        $mail->Body    = "Your verification code is: <h2 style='color: #007bff;'>$otp</h2> This code will expire in 10 minutes.";
        $mail->AltBody = "Your verification code is: $otp. This code will expire in 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        $_SESSION['email_error'] = $mail->ErrorInfo;
        return false;
    }
}
