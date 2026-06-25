<?php

namespace App\Modules\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use App\Etc\Config\Environment;

class MailController
{
    private function createMailer(): PHPMailer
    {
        $mail             = new PHPMailer(true);
        $mail->CharSet    = 'UTF-8';
        $mail->isSMTP();
        $mail->Host       = Environment::get('MAIL_HOST', '');
        $mail->SMTPAuth   = true;
        $mail->Username   = Environment::get('MAIL_USERNAME', '');
        $mail->Password   = Environment::get('MAIL_PASSWORD', '');
        $mail->SMTPSecure = Environment::get('MAIL_ENCRYPTION', 'ssl');
        $mail->Port       = (int) Environment::get('MAIL_PORT', 465);
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
