<?php

namespace App;

use App\Config;

/**
* Mail
*
* PHP version 7.0
*/
class Mail
{

  /**
  * Send a message
  *
  * @param string $to Recipient
  * @param string $subject Subject
  * @param string $text Text-only content of the message
  * @param string $html HTML content of the message
  *
  * @return mixed
  */
  public static function send($to, $subject, $text, $html)
  {
    $email = new \SendGrid\Mail\Mail();
    $email->setFrom("contact@flod.ai", "Sentive AI");
    $email->setSubject($subject);
    $email->addTo($to, "User");
    $email->addContent(
      "text/plain", $text
    );
    $email->addContent(
      "text/html", $html
    );
    $sendgrid = new \SendGrid(\App\Config::SENDGRID_API_KEY);
    try {
      $response = $sendgrid->send($email);
      print $response->statusCode() . "\n";
      //print_r($response->headers());
      print $response->body() . "\n";
    } catch (\Exception $e) {
      echo 'Caught exception: ',  $e->getMessage(), "\n";
    }
  }
}
