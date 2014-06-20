#!/usr/bin/php
<?php

require_once __DIR__."/../vendor/autoload.php";

$ip = (isset($argv[1])) ? $argv[1] : "127.0.0.1";
$hostname = (isset($argv[2])) ? $argv[2] : "localhost";


$loop = React\EventLoop\Factory::create();


$http_socket = new React\Socket\Server($loop);
$http = new React\Http\Server($http_socket);
$http->on('request', [ new Demo\Http(), "onRequest" ]);
$http_socket->listen(8000, $ip);


$app = new Ratchet\App($hostname, 8080, $ip, $loop);
$app->route("/chat", new Demo\Chat());
$app->run();



?>
