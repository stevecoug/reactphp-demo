#!/usr/bin/php
<?php

require_once __DIR__."/../vendor/autoload.php";

stream_set_blocking(STDIN, false);

$loop = React\EventLoop\Factory::create();

$context = new React\ZMQ\Context($loop);

$push = $context->getSocket(ZMQ::SOCKET_PUSH);
$push->connect('tcp://127.0.0.1:5555');

$pull = $context->getSocket(ZMQ::SOCKET_PULL);
$pull->connect('tcp://127.0.0.1:5556');

$pull->on('error', function ($e) {
    print_r($e->getMessage());
});

$pull->on('message', function ($msg) {
	printf("[%0.5f] RECV: %s\n", microtime(true), $msg);
});

$loop->addPeriodicTimer(.05, function () use (&$push) {
	if ($line = trim(fgets(STDIN))) {
		$push->send($line);
		printf("[%0.5f] SEND: %s\n", microtime(true), $line);
	}
});

$loop->run();



?>
