<?

namespace Demo;
define("HTDOCS", realpath(__DIR__."/../../htdocs"));

class Http {
	public function onRequest($request, $response) {
		$path = $request->getPath();
		$file = realpath(HTDOCS.$path);
		
		if ($path === "/") return $this->redirect($response, "/chat.html");
		if (substr($file, 0, strlen(HTDOCS)) !== HTDOCS || !is_file($file)) return $this->error404($response, $path);
		
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		switch ($ext) {
			case "js": $type = "application/javascript"; break;
			case "html": $type = "text/html"; break;
			default: $type = "text/plain"; break;
		}
		
		echo "HTTP FILE: $path ($type)\n";
		$response->writeHead(200, [ 'Content-Type' => $type ]);
		$response->end(file_get_contents(HTDOCS.$path));
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
