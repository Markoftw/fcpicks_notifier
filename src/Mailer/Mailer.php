<?php

namespace Markoftw\fcpicks\Mailer;

use Markoftw\fcpicks\Config\Config;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{

    public static function sendMsg($values, $recipients = NULL)
    {
        $mail = new PHPMailer;
        //$mail->SMTPDebug = 3;                               // Enable verbose debug output
        if (Config::get('mailer/enabled') == TRUE) {
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = Config::get('mailer/username');                 // SMTP username
            $mail->Password = Config::get('mailer/password');                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to
            $mail->From = Config::get('mailer/username');
            $mail->FromName = 'FCpicks Mailer';
            $mail->addAddress('info@example.com', 'Information');     // Add a recipient
            //$mail->addReplyTo('info@example.com', 'Information');
            //$mail->addCC('cc@example.com');
            //$mail->addBCC('bcc@example.com');
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'New pick: ' . $values[2];
            $mail->Body = self::mailBody($values);
            $mail->AltBody = 'No alternative body available.';
            if (!$mail->send()) {
                echo 'Message could not be sent.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            } else {
                echo 'Message has been sent';
            }
        }
    }

    public static function mailBody($values)
    {
        return '<b>Date:</b> ' . $values[0] . ' <br/>
                <b>League:</b> ' . $values[1] . ' <br/>
                <b>Match:</b> ' . $values[2] . ' <br/>
                <b>Type:</b> ' . $values[3] . ' <br/>
                <b>Odds:</b> ' . $values[4] . ' <br/>
                <b>Bookie:</b> ' . $values[5];
    }

}
