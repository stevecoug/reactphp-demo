#!/usr/bin/php
<?

require_once __DIR__."/../includes/demo.inc";

$app = new Ratchet\App("chat.local", 8080);
$app->route("/chat", new Demo\Chat());
$app->run();



?>
