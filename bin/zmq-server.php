#!/usr/bin/php
<?php

require_once __DIR__."/../vendor/autoload.php";

$loop = React\EventLoop\Factory::create();

$context = new React\ZMQ\Context($loop);

$pull = $context->getSocket(ZMQ::SOCKET_PULL);
$pull->bind('tcp://127.0.0.1:5555');

$push = $context->getSocket(ZMQ::SOCKET_PUSH);
$push->bind('tcp://127.0.0.1:5556');

$pull->on('error', function ($e) {
    print_r($e->getMessage());
});

$pull->on('message', function ($msg) use (&$push) {
	printf("[%0.5f] RECV: %s\n", microtime(true), $msg);
	$hash = md5($msg);
	$push->send($hash);
	printf("[%0.5f] SEND: %s\n", microtime(true), $hash);
});

$loop->run();



?>
