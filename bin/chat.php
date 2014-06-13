#!/usr/bin/php
<?php

require_once __DIR__."/../vendor/autoload.php";

$app = new Ratchet\App("chat.local", 8080);
$app->route("/chat", new Demo\Chat());
$app->run();



?>
