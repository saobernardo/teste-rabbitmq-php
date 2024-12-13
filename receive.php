<?php

require_once __DIR__."/vendor/autoload.php";

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(realpath(dirname(__FILE__)));
$dotenv->load();

$connection = new AMQPStreamConnection($_ENV['RABBITMQ_HOST'], $_ENV['RABBITMQ_PORT'], $_ENV['RABBITMQ_USER'], $_ENV['RABBITMQ_PSWD']);
$channel = $connection->channel();

$channel->queue_declare('testeemail', false, false, false, false);

echo "[*] Waiting for messages. Press CTRL+C to exit\n";

$callback = function($msg){
  //echo $msg->body."\n";
  $email = explode('(*&)', $msg->body);

  $mail = new PHPMailer(true);

  try{
    $mail->isSMTP();
    $mail->Host = $_ENV['MAIL_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['MAIL_USER'];
    $mail->Password = $_ENV['MAIL_PSWD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port= $_ENV['MAIL_PORT'];
    $mail->CharSet = $_ENV['MAIL_CHARSET'];

    $mail->setFrom('saobernardo@email.com', "Lucas São Bernardo");
    $mail->addAddress($_ENV['DESTINATION_MAIL']);
    $mail->addReplyTo('saobernardo@email.com', "Lucas São Bernardo");
    
    $mail->isHTML(true);
    $mail->Subject = $email[0];
    $mail->Body = $email[1];

    $mail->send();
  }
  catch(Exception $e){
    print $e->getMessage();
  }
};

$channel->basic_consume('testeemail', '', false, true, false, false, $callback);

try{
  $channel->consume();
} catch(\Throwable $exception){
  echo $exception->getMessage();
}