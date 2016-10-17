<?php

namespace Demo;

class Http {
	private $htdocs;
	
	public function __construct($htdocs) {
		$this->htdocs = realpath($htdocs);
	}
	public function onRequest($request, $response) {
		$path = $request->getPath();
		if ($path === "/") $path = "/index.html";
		
		$file = realpath($this->htdocs.$path);
		
		if (substr($file, 0, strlen($this->htdocs)) !== $this->htdocs || !is_file($file)) return $this->error404($response, $path);
		
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		switch ($ext) {
			case "html": $type = "text/html"; break;
			case "js": $type = "application/javascript"; break;
			case "css": $type = "text/css"; break;
			default: $type = "text/plain"; break;
		}
		
		echo "HTTP FILE: $path ($type)\n";
		$response->writeHead(200, [ 'Content-Type' => $type ]);
		$response->end(file_get_contents($file));
	}
	private function redirect($response, $path) {
		echo "HTTP REDIRECT TO $path\n";
		$response->writeHead(302, [ 'Location' => $path ]);
		$response->end("");
		return;
	}
	private function error404($response, $path) {
		echo "HTTP FILE NOT FOUND: $path\n";
		$response->writeHead(404, [ 'Content-Type' => "text/plain" ]);
		$response->end("HTTP 404 - File not found\n\n$path");
		return;
	}
}
