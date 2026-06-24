<?php

namespace App\Utils;

use App\Utils\Utility;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class MailClient
{
    public static function sendMail($toEmail, $subject, $templatePath, $templateData = [], $toName = '', $isHtml = true)
    {
        $mail = new PHPMailer(true);

        try {
            // SMTP settings for MailHog (Local Dev)
            $mail->isSMTP();
            $mail->Host       = 'localhost';
            $mail->Port       = 1025;
            $mail->SMTPAuth   = false;
            $mail->SMTPSecure = false;

            // Email headers
            $mail->setFrom('info@pizzasquare.ng', BRAND_NAME);
            $mail->addAddress($toEmail, $toName ?: $toEmail);
            $mail->isHTML($isHtml);
            $mail->Subject = $subject;

            // Load and parse HTML template
            if (!file_exists($templatePath)) {
                throw new \Exception("Email template not found: $templatePath");
            }

            $template = file_get_contents($templatePath);
            $body = str_replace(array_keys($templateData), array_values($templateData), $template);
            $mail->Body = $body;

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            Utility::log($e->getMessage(), 'error', 'Mailer::sendMail', ['host' => 'localhost'], $e);
        }
    }
}
