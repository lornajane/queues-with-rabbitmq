<?php

require "vendor/autoload.php";

$rabbit = new PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $rabbit->channel();

$channel->exchange_declare('images', 'topic', false, true, false); // name, type, passive, durable, autodelete
$channel->queue_declare('triagev1', false, true, false, false); // name, passive, durable, exclusive, autodelete
$channel->queue_bind('triagev1', 'images', 'image.v1.triage');

$url = "https://www.gravatar.com/avatar/f6bb323eb6b2ad7f5ca2f8f3fc15f887"; // lorna's avatar

// generate some randomness!
$badge = random_int(0,1) ? "pro" : false;
$resize_count = random_int(1,4);
$resizes = [];
for($i=0;$i<$resize_count;$i++) {
    $size = random_int(1, 5) * 50;
    $resizes[] = [$size, $size];
}

// create message
$message = [];
$message['url'] = $url;
if($badge) {
    $message['badge'] = $badge;
}
$message['sizes'] = $resizes;

$amqp_msg = new PhpAmqpLib\Message\AMQPMessage(json_encode($message));

$channel->basic_publish($amqp_msg, 'images', 'image.v1.triage');
