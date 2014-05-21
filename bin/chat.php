#!/usr/bin/php
<?

require_once "chat.inc";

$app = new Ratchet\App("chat.stevemeyers.net", 8080, "192.41.86.155");
$app->route("/chat", new Demo\Chat());
$app->run();



?>
