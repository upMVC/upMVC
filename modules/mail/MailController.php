<?php
/*
 *   Created on Tue Oct 31 2023
 
 *   Copyright (c) 2023 BitsHost
 *   All rights reserved.

 *   Permission is hereby granted, free of charge, to any person obtaining a copy
 *   of this software and associated documentation files (the "Software"), to deal
 *   in the Software without restriction, including without limitation the rights
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *   copies of the Software, and to permit persons to whom the Software is
 *   furnished to do so, subject to the following conditions:

 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.

 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *   SOFTWARE.
 *   Here you may host your app for free:
 *   https://bitshost.biz/
 */

namespace Mail;

use PHPMailer\PHPMailer\PHPMailer;

class MailController
{

  public function sendMailByPHPMailer($to, $from, $subject, $message)
  {

    // SEND MAIL by PHP MAILER
    $mail          = new PHPMailer();
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP(); // Use SMTP protocol
    $mail->Host       = 'bitshost.net'; // Specify  SMTP server
    $mail->SMTPAuth   = true; // Auth. SMTP
    $mail->Username   = 'office@bitshost.net'; // Mail who send by PHPMailer
    $mail->Password   = '9ZZt*2To8R~vrtght'; // your pass mail box
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
    } else {
      // return true if message is send
      return true;
    }
  }

 public function sendMailByPHPMailerAddAttachment($to, $from, $subject, $message, $attachmentroute)
  {

    // SEND MAIL by PHP MAILER
    $mail          = new PHPMailer();
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP(); // Use SMTP protocol
    $mail->Host       = 'bitshost.net'; // Specify  SMTP server
    $mail->SMTPAuth   = true; // Auth. SMTP
    $mail->Username   = 'office@bitshost.net'; // Mail who send by PHPMailer
    $mail->Password   = '9ZZt*2To8R~vrtght'; // your pass mail box
    $mail->SMTPSecure = 'ssl'; // Accept SSL
    $mail->Port       = 465; // port of your out server
    $mail->setFrom($from); // Mail to send at
    $mail->addAddress($to); // Add sender
    $mail->addReplyTo($from); // Adress to reply
    $mail->isHTML(true); // use HTML message
    $mail->Subject = $subject;
    $mail->Body    = $message;

    //$mail->addAttachment("uploads/" . $file_name)
    $mail->addAttachment($attachmentroute);

    // SEND
    if (!$mail->send()) {

      // render error if it is
      $tab = array('error' => 'Mailer Error: ' . $mail->ErrorInfo);
      echo json_encode($tab);
      exit;
    } else {
      // return true if message is send
      return true;
    }
  }
}
