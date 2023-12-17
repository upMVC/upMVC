<?php
namespace Mail;

use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;

class MailController
{
  
  function send_mail_by_PHPMailer($to, $from, $subject, $message)
  {

    // SEND MAIL by PHP MAILER
    $mail          = new PHPMailer();
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP(); // Use SMTP protocol
    $mail->Host       = 'yourhost.com'; // Specify  SMTP server
    $mail->SMTPAuth   = true; // Auth. SMTP
    $mail->Username   = 'youremail@email.com'; // Mail who send by PHPMailer
    $mail->Password   = 'Klr!GH2]@Xb6'; // your pass mail box
    $mail->SMTPSecure = 'ssl'; // Accept SSL
    $mail->Port       = 465; // port of your out server
    $mail->setFrom($from); // Mail to send at
    $mail->addAddress($to); // Add sender
    $mail->addReplyTo($from); // Adress to reply
    $mail->isHTML(true); // use HTML message
    $mail->Subject = $subject;
    $mail->Body    = $message;

    // SEND
    if (!$mail->send()) {

      // render error if it is
      $tab = array('error' => 'Mailer Error: ' . $mail->ErrorInfo);
      echo json_encode($tab);
      exit;
    }
    else {
      // return true if message is send
      return true;
    }

  }

  function send_mail_by_PHPMailer_addAttachment($to, $from, $subject, $message, $attachmentroute)
  {

    // SEND MAIL by PHP MAILER
    $mail          = new PHPMailer();
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP(); // Use SMTP protocol
    $mail->Host       = 'yourhost.com'; // Specify  SMTP server
    $mail->SMTPAuth   = true; // Auth. SMTP
    $mail->Username   = 'youremail@email.com'; // Mail who send by PHPMailer
    $mail->Password   = 'Klrad!GdH2da]@Xb6ddsadf'; // your pass mail box
    $mail->SMTPSecure = 'ssl'; // Accept SSL
    $mail->Port       = 465; // port of your out server
    $mail->setFrom($from); // Mail to send at
    $mail->addAddress($to); // Add sender
    $mail->addReplyTo($from); // Adress to reply
    $mail->isHTML(true); // use HTML message
    $mail->Subject = $subject;
    $mail->Body    = $message;
    
    //$mail->addAttachment("uploads/" . $file_name);
    $mail->addAttachment($attachmentroute);

    // SEND
    if (!$mail->send()) {

      // render error if it is
      $tab = array('error' => 'Mailer Error: ' . $mail->ErrorInfo);
      echo json_encode($tab);
      exit;
    }
    else {
      // return true if message is send
      return true;
    }

  }



}
