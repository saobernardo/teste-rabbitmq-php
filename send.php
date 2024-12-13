<?php

require_once __DIR__.'/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('testeemail', false, false, false, false);

$msg = new AMQPMessage('Teste titulo email(*&)Teste corpo email');

$channel->basic_publish($msg, '', 'testeemail');

echo "Enviado!\n";

$channel->close();
$connection->close();