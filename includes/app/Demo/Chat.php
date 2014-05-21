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
		$this->clients->attach($conn, [
			"id" => $conn_id,
			"conn" => $conn,
			"auth" => false,
			"name" => "Connection #$conn_id",
			"color" => "#000000",
		]);
		printf("CONNECTED: Connection #%d\n", $conn_id);
	}
	
	public function onMessage(ConnectionInterface $from_conn, $payload) {
		$data = json_decode($payload);
		$info = $this->clients->offsetGet($from_conn);
		
		switch ($data->type) {
			case "chat":
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
			break;
			
			case "auth":
				printf(" + AUTH (%d): %s - %s\n", $info['id'], $data->name, $data->color);
				$error = false;
				if (strlen($data->name) < 3) $error = "Display name must be at least 3 characters long";
				if (strlen($data->name) > 20) $error = "Display name cannot exceed 20 characters long";
				if (!preg_match('/^[A-Za-z]([A-Za-z0-9]+[ ._-])*[A-Za-z0-9]+$/', $data->name)) $error = "Display name contains illegal characters";
				if (!preg_match('/^#[0-9a-fA-F]{6}$/', $data->color)) $error = "Invalid color selected";
				
				if ($error === false) {
					$from_conn->send(json_encode([
						"type" => "auth",
						"valid" => true,
					]));
					$info['auth'] = true;
					$info['name'] = $data->name;
					$info['color'] = $data->color;
					$this->clients->offsetSet($from_conn, $info);
				} else {
					$from_conn->send(json_encode([
						"type" => "error",
						"error" => $error,
					]));
				}
			break;
			
			default:
				print_r($data);
				$client->send(json_encode([
					"type" => "error",
					"error" => "Unknown message type received",
				]));
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
}

?>
