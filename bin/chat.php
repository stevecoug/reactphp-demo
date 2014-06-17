#!/usr/bin/php
<?php

require_once __DIR__."/../vendor/autoload.php";

$loop = React\EventLoop\Factory::create();

$http_socket = new React\Socket\Server($loop);
$http = new React\Http\Server($http_socket);
$http->on('request', function ($request, $response) {
        $response->writeHead(200, [ 'Content-Type' => 'text/plain' ]);
        $response->end("Hello World!\n");
});

$http_socket->listen(8000);

$app = new Ratchet\App("chat.local", 8080, "127.0.0.1", $loop);
$app->route("/chat", new Demo\Chat());
$app->run();



?>
