#!/usr/bin/php
<?php

require_once __DIR__."/../vendor/autoload.php";

$loop = React\EventLoop\Factory::create();

$http_socket = new React\Socket\Server($loop);
$http = new React\Http\Server($http_socket);
$http->on('request', function ($request, $response) {
	$path = $request->getPath();
	
	switch ($path) {
		case "/":
			$response->writeHead(301, [ 'Location' => "/chat.html" ]);
			$response->end("");
			return;
		break;
		case "/chat.html":
			$type = "text/html";
		break;
		case "/js/jquery.cookie.js":
			$type = "application/javascript";
		break;
		default:
			echo "INVALID FILE REQUEST: $path\n";
			$response->writeHead(404, [ 'Content-Type' => "text/plain" ]);
			$response->end("HTTP 404 - File not found");
			return;
		break;
	}
	echo "HTTP FILE: $path\n";
	$response->writeHead(200, [ 'Content-Type' => $type ]);
	$response->end(file_get_contents(__DIR__."/../htdocs$path"));
});

$http_socket->listen(8000);

$app = new Ratchet\App("chat.local", 8080, "127.0.0.1", $loop);
$app->route("/chat", new Demo\Chat());
$app->run();



?>
