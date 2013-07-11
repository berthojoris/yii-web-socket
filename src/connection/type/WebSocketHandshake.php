<?php
namespace YiiWebSocket\Connection\Type;

use YiiWebSocket\Connection\AHandshake;
use YiiWebSocket\Helper\Util;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 3:42 PM
 * To change this template use File | Settings | File Templates.
 */
class WebSocketHandshake extends AHandshake {

	/**
	 * @return bool|mixed
	 */
	public function doHandshake() {
		if (!$this->checkVersion() || !$this->checkOrigin()) {
			$this->connection->close();
			return false;
		}
		if (!$this->connection->writeRawData($this->makeHeaders())) {
			return false;
		}
		return true;
	}

	protected function makeHeaders() {
		$secKey = $this->headers->getHeader('Sec-WebSocket-Key');
		$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
		$response = "HTTP/1.1 101 Switching Protocols\r\n";
		$response .= "Upgrade: websocket\r\n";
		$response .= "Connection: Upgrade\r\n";
		$response .= "Sec-WebSocket-Accept: " . $secAccept . "\r\n";

		$protocol = $this->headers->getHeader('Sec-WebSocket-Protocol');
		if (!empty($protocol)) {
			$response .= "Sec-WebSocket-Protocol: " . substr($this->headers->getHeader('Path'), 1) . "\r\n";
		}
		$response .= "\r\n";
		return $response;
	}

	protected function checkVersion() {
		$webSocketVersion = $this->headers->getHeader('Sec-WebSocket-Version');
		if (!$webSocketVersion || $webSocketVersion < 6) {
			$this->connection->sendHttpResponse(501);
			return false;
		}
		return true;
	}

	protected function checkOrigin() {
		$origin = $this->headers->getHeader('Sec-WebSocket-Origin', $this->headers->getHeader('Origin'));
		if ($origin === null || empty($origin) || !Util::checkOrigin($origin)) {
			$this->connection->sendHttpResponse(401);
			return false;
		}
		return true;
	}
}
