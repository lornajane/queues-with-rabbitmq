<?php

require "vendor/autoload.php";

$rabbit = new PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $rabbit->channel();

$channel->queue_declare('pushes', false, true, false, false);
echo "waiting for jobs ...\n";


$process = function ($message) {
    $added = 0;
    $removed = 0;
    $modified = 0;

    $data = json_decode($message->getBody(), true);
    if(isset($data['commits'])) {
        foreach($data['commits'] as $commit) {
            $added += count($commit['added']);
            $removed += count($commit['removed']);
            $modified += count($commit['modified']);
        }
    }

    echo "Added/Removed/Modified totals: " . $added . "/" . $removed . "/" . $modified . "\n";
    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
};

$channel->basic_consume('pushes', '', false, false, false, false, $process);

while(count($channel->callbacks)) {
    $channel->wait();
}
