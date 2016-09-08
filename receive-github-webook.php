<?php

require "vendor/autoload.php";

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if(false !== $data) {
    $rabbit = new PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

    $channel = $rabbit->channel();

    $channel->queue_declare('pushes', false, true, false, false);
    // use the raw json in the message
    $message = new PhpAmqpLib\Message\AMQPMessage($input, ["delivery_mode" => 2]);
    $channel->basic_publish($message, '', 'pushes');
}

