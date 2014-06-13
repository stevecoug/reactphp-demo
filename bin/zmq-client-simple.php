#!/usr/bin/php
<?php

require_once __DIR__."/../vendor/autoload.php";

$context = new ZMQContext();

$push = $context->getSocket(ZMQ::SOCKET_PUSH);
$push->connect('tcp://127.0.0.1:5555');

while ($line = fgets(STDIN)) {
	$push->send(trim($line));
}


?>
