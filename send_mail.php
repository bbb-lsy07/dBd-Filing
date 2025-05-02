<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'vendor/PHPMailer/src/Exception.php';
require_once 'vendor/PHPMailer/src/PHPMailer.php';
require_once 'vendor/PHPMailer/src/SMTP.php';

function sendMail($to, $subject, $body) {
    require_once 'common.php';
    $db = init_database();
    $settings = $db->querySingle("SELECT * FROM settings", true);

    $smtp_host = $settings['smtp_host'] ?? '';
    $smtp_port = $settings['smtp_port'] ?? '';
    $smtp_username = $settings['smtp_username'] ?? '';
    $smtp_password = $settings['smtp_password'] ?? '';
    $smtp_secure = $settings['smtp_secure'] ?? 'tls';
    $from_email = $settings['contact_email'] ?? 'no-reply@bbb-lsy07.my';
    $from_name = $settings['site_title'] ?? '联bBb盟 ICP 备案系统';

    if (empty($smtp_host) || empty($smtp_port) || empty($smtp_username) || empty($smtp_password)) {
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = $smtp_secure;
        $mail->Port = $smtp_port;
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>