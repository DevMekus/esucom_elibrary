<?php

namespace App\Utils;

use configs\Database;

class MailClient
{
    public static function queueMail($toEmail, $subject, $templatePath, $templateData = [], $toName = '', $isHtml = true)
    {
        // Insert into queue table
        $db = Database::getInstance();
        
        $db->insert("email_queue", [
            'to_email'      => $toEmail,
            'to_name'       => $toName,
            'subject'       => $subject,
            'template_path' => $templatePath,
            'template_data' => json_encode($templateData),
            'is_html'       => $isHtml ? 1 : 0,
            'status'        => 'pending'
        ]);

        return true;
    }

    public static function sendQueuedEmails()
    {
        $db = Database::getInstance();
        $pendingEmails = $db->query("SELECT * FROM email_queue WHERE status='pending' LIMIT 10"); // batch send

        foreach ($pendingEmails as $email) {
            self::sendMail(
                $email['to_email'],
                $email['subject'],
                $email['template_path'],
                json_decode($email['template_data'], true),
                $email['to_name'],
                (bool)$email['is_html']
            );

            $db->update("email_queue", ['status' => 'sent'], ['id' => $email['id']]);
        }
    }

    public static function sendMail($toEmail, $subject, $templatePath, $templateData = [], $toName = '', $isHtml = true)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['EMAIL_ADDRESS'];
            $mail->Password   = $_ENV['EMAIL_PASSWORD'];

            // ✅ Secure TLS settings
            $mail->Port       = 465;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

            $mail->CharSet    = 'UTF-8';

            // ✅ From / Reply-to
            $mail->setFrom($_ENV['EMAIL_ADDRESS'], BRAND_NAME);
            $mail->addReplyTo($_ENV['EMAIL_ADDRESS'], 'Support');

            // ✅ Recipient
            $mail->addAddress($toEmail, $toName ?: $toEmail);

            $mail->isHTML($isHtml);
            $mail->Subject = $subject;

            // ✅ Load Template
            if (!file_exists($templatePath)) {
                throw new Exception("Email template missing: $templatePath");
            }

            $template = file_get_contents($templatePath);
            $body = str_replace(array_keys($templateData), array_values($templateData), $template);
            $mail->Body = $body;

            // ✅ Plain text fallback
            $mail->AltBody = strip_tags($body);

            // ✅ DKIM: Strong anti-spam protection
            $mail->DKIM_domain = $_ENV['EMAIL_DOMAIN'];
            $mail->DKIM_private = __DIR__ . '/dkim_private.key';
            $mail->DKIM_selector = 'mail';
            $mail->DKIM_passphrase = '';
            $mail->DKIM_identity = $mail->From;

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            Utility::log($e->getMessage(), 'error', 'Mailer::sendMail', ['email' => $toEmail], $e);
            return false;
        }
    }
}
