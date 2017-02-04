<?php

namespace Demo;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
	protected $clients;
	protected $next_conn_id = 0;
	
	private $possible_names = [
		"Fred",
		"Barney",
		"Wilma",
		"Pebbles",
		"Bamm-Bamm",
		"Betty",
		"Dino",
	];
	private $possible_colors = [
		"Red" => "d00000",
		"Orange" => "e08000",
		"Green" => "00d000",
		"Blue" => "0000d0",
		"Purple" => "c000c0",
	];
	
	public function __construct() {
		$this->clients = new \SplObjectStorage;
	}
	
	public function onOpen(ConnectionInterface $conn) {
		$conn_id = $this->next_conn_id++;
		
		$name = "Connection #$conn_id";
		$color_value = "000000";
		
		$info = [
			"id" => $conn_id,
			"conn" => $conn,
			"auth" => false,
			"name" => $name,
			"color" => $color_value,
		];
		$this->clients->attach($conn, $info);
		
		$color_name = array_keys($this->possible_colors)[($conn_id % 5)];
		$color_value = $this->possible_colors[$color_name];
		$name = "$color_name " . $this->possible_names[($conn_id % 7)];
		$this->setupAuth($conn, $name, $color_value, $info);
		
		echo "WS CONNECTED #$conn_id: $name ($color_value)\n";
	}
	
	public function onMessage(ConnectionInterface $from_conn, $payload) {
		try {
			$data = json_decode($payload);
			$info = $this->clients->offsetGet($from_conn);
			
			switch ($data->type) {
				case "chat":
					$this->recvMessage($from_conn, $data, $info);
				break;
				
				default:
					print_r($data);
					throw new \Exception("Unknown message type received: $data->type");
				break;
			}
		} catch (\Exception $e) {
			$this->sendError($from_conn, $e->getMessage());
		}
	}
	
	public function onClose(ConnectionInterface $conn) {
		$info = $this->clients->offsetGet($conn);
		$this->clients->detach($conn);
		
		printf("WS DISCONNECTED: %s\n", $info['name']);
	}
	
	public function onError(ConnectionInterface $conn, \Exception $e) {
		$conn->close();
	}
	
	private function setupAuth($conn, $name, $color, $info) {
		printf(" + AUTH (%d): %s - %s\n", $info['id'], $name, $color);
		
		if ($info === null) {
			$info = $this->clients->offsetGet($conn);
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

