<?

namespace Demo;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
	protected $clients;
	protected $next_conn_id = 1;
	
	public function __construct() {
		$this->clients = new \SplObjectStorage;
	}
	
	public function onOpen(ConnectionInterface $conn) {
		$conn_id = $this->next_conn_id++;
		$name = "Connection #$conn_id";
		$auth = false;
		$color = "000000";
		
		$info = [
			"id" => $conn_id,
			"conn" => $conn,
			"auth" => $auth,
			"name" => $name,
			"color" => $color,
		];
		$this->clients->attach($conn, $info);
		
		echo "CONNECTED: Connection #$conn_id\n";
		
		if ($name = $conn->WebSocket->request->getCookie("demo_chat_name")) {
			$color = $conn->WebSocket->request->getCookie("demo_chat_color");
			$this->setupAuth($conn, $name, $color, $info);
		}
	}
	
	public function onMessage(ConnectionInterface $from_conn, $payload) {
		$data = json_decode($payload);
		$info = $this->clients->offsetGet($from_conn);
		
		switch ($data->type) {
			case "chat":
				$this->recvMessage($from_conn, $data, $info);
			break;
			
			case "auth":
				$this->setupAuth($from_conn, $data->name, substr($data->color, 1), $info);
			break;
			
			default:
				print_r($data);
				$this->sendError($from_conn, "Unknown message type received: $data->type");
			break;
		}
	}
	
	public function onClose(ConnectionInterface $conn) {
		$info = $this->clients->offsetGet($conn);
		$this->clients->detach($conn);
		
		printf("DISCONNECTED: %s\n", $info['name']);
	}
	
	public function onError(ConnectionInterface $conn, \Exception $e) {
		$conn->close();
	}
	
	private function setupAuth($conn, $name, $color, &$info) {
		printf(" + AUTH (%d): %s - %s\n", $info['id'], $name, $color);
		
		if ($info === null) {
			$info = $this->clients->offsetGet($conn);
		}
		
		$error = false;
		if (strlen($name) < 3) $error = "Display name ($name) must be at least 3 characters long";
		if (strlen($name) > 20) $error = "Display name ($name) cannot exceed 20 characters long";
		if (!preg_match('/^[A-Za-z]([A-Za-z0-9]+[ ._-])*[A-Za-z0-9]+$/', $name)) $error = "Display name ($name) contains illegal characters";
		if (!preg_match('/^[0-9a-fA-F]{6}$/', $color)) $error = "Invalid color selected: $color";
		
		if ($error !== false) {
			$this->sendError($conn, $error);
			return false;
		}
		
		$conn->send(json_encode([
			"type" => "auth",
			"valid" => true,
			"name" => $name,
			"color" => $color,
		]));
		
		$info['auth'] = true;
		$info['name'] = $name;
		$info['color'] = $color;
		
		$this->clients->offsetSet($conn, $info);
	}
	private function recvMessage($from_conn, $data, $info) {
		printf(" + CHAT (%d / %s): %s\n", $info['id'], $info['name'], $data->message);
		if ($info['auth'] === true) {
			foreach ($this->clients as $client) {
				if ($client !== $from_conn) {
					$client->send(json_encode([
						"type" => "chat",
						"from" => $info['name'],
						"color" => $info['color'],
						"message" => $data->message,
					]));
				}
			}
		}
	}
	private function sendError($conn, $msg) {
		$conn->send(json_encode([
			"type" => "error",
			"error" => $msg,
		]));
	}
}

?>
