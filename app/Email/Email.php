<?php

namespace App\Email;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email
{
    public static function send_mail($data)
    {
        $mail = new PHPMailer(true);                    // Passing `true` enables exceptions
        try {
            //Server settings
            $mail->SMTPDebug = 0;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';                       // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'svshilingov@gmail.com';            // SMTP username
            $mail->Password = '12358132kk112aa';                  // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('svshilingov@gmail.com', 'Bob from Matcha Team');
            $mail->addAddress($data['email']);     // Add a recipient
            $mail->addReplyTo('svshilingov@gmail.com', 'Mailer Bot');

            //Content
            $mail->isHTML(true);                          // Set email format to HTML
            $mail->Subject = $data['subject'];
            $mail->Body = $data['body'];
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }

    }
}