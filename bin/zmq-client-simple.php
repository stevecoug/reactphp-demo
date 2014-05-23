#!/usr/bin/php
<?

require_once __DIR__."/../includes/demo.inc";

$context = new ZMQContext();

$push = $context->getSocket(ZMQ::SOCKET_PUSH);
$push->connect('tcp://127.0.0.1:5555');

while ($line = fgets(STDIN)) {
	$push->send(trim($line));
}


?>
