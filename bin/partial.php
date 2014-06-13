#!/usr/bin/php
<?php

require_once __DIR__."/../vendor/autoload.php";

use React\Partial;
$square = Partial\bind("pow", Partial\…(), 2);

for ($i = 1; $i <= 10; $i++) {
	printf("%d ^ 2 = %d\n", $i, $square($i));
}

