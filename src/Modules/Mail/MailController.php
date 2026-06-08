<?php

namespace App\Modules\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailController
{
    private function createMailer(): PHPMailer
    {
        $mail             = new PHPMailer(true);
        $mail->CharSet    = 'UTF-8';
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'] ?? '';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'] ?? '';
        $mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
        $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? 'ssl';
        $mail->Port       = (int) ($_ENV['MAIL_PORT'] ?? 465);
        $mail->isHTML(true);
        return $mail;
    }

    public function sendMailByPHPMailer($to, $from, $subject, $message): bool
    {
        $mail = $this->createMailer();
        $mail->setFrom($from);
        $mail->addAddress($to);
        $mail->addReplyTo($from);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        if (!$mail->send()) {
            echo json_encode(['error' => 'Mailer Error: ' . $mail->ErrorInfo]);
            exit;
        }

        return true;
    }

    public function sendMailByPHPMailerAddAttachment($to, $from, $subject, $message, $attachmentroute): bool
    {
        $mail = $this->createMailer();
        $mail->setFrom($from);
        $mail->addAddress($to);
        $mail->addReplyTo($from);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->addAttachment($attachmentroute);

        if (!$mail->send()) {
            echo json_encode(['error' => 'Mailer Error: ' . $mail->ErrorInfo]);
            exit;
        }

        return true;
    }
}
