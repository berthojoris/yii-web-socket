<?php
namespace YiiWebSocket\Connection;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/20/13
 * Time: 12:26 PM
 * To change this template use File | Settings | File Templates.
 */
class WebSocketDataConverter extends ADataConverter {

	/**
	 * @param $arguments
	 * @param $number
	 * @param $default
	 *
	 * @return mixed
	 */
	private function getArg($arguments, $number, $default) {
		if (!isset($arguments[$number])) {
			return $default;
		}
		return $arguments[$number];
	}

	/**
	 * hybi10Encode
	 *
	 * @param        $payload
	 * @param string $type
	 * @param bool   $masked
	 *
	 * @return bool|string
	 */
	public function encode() {
		$arguments = func_get_args();
		$payload = $this->getArg($arguments, 0, '');
		$type = $this->getArg($arguments, 1, 'text');
		$masked = $this->getArg($arguments, 2, false);

		$frameHead = array();
		$frame = '';
		$payloadLength = strlen($payload);

		switch ($type) {
			case 'text':
				// first byte indicates FIN, Text-Frame (10000001):
				$frameHead[0] = 129;
				break;

			case 'close':
				// first byte indicates FIN, Close Frame(10001000):
				$frameHead[0] = 136;
				break;

			case 'ping':
				// first byte indicates FIN, Ping frame (10001001):
				$frameHead[0] = 137;
				break;

			case 'pong':
				// first byte indicates FIN, Pong frame (10001010):
				$frameHead[0] = 138;
				break;
		}

		// set mask and payload length (using 1, 3 or 9 bytes)
		if ($payloadLength > 65535) {
			$payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
			$frameHead[1] = ($masked === true) ? 255 : 127;
			for ($i = 0; $i < 8; $i++) {
				$frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
			}
			// most significant bit MUST be 0 (close connection if frame too big)
			if ($frameHead[2] > 127) {
				$this->close(1004);
				return false;
			}
		} elseif ($payloadLength > 125) {
			$payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
			$frameHead[1] = ($masked === true) ? 254 : 126;
			$frameHead[2] = bindec($payloadLengthBin[0]);
			$frameHead[3] = bindec($payloadLengthBin[1]);
		} else {
			$frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
		}

		// convert frame-head to string:
		foreach (array_keys($frameHead) as $i) {
			$frameHead[$i] = chr($frameHead[$i]);
		}
		if ($masked === true) {
			// generate a random mask:
			$mask = array();
			for ($i = 0; $i < 4; $i++) {
				$mask[$i] = chr(rand(0, 255));
			}

			$frameHead = array_merge($frameHead, $mask);
		}
		$frame = implode('', $frameHead);

		// append payload to frame:
		$framePayload = array();
		for ($i = 0; $i < $payloadLength; $i++) {
			$frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
		}

		return $frame;
	}

	/**
	 * @param            $data
	 * @param Connection $connection
	 *
	 * @return array|bool
	 */
	public function decode() {
		$data = $this->getArg(func_get_args(), 0, '');
		$payloadLength = '';
		$mask = '';
		$unmaskedPayload = '';
		$decodedData = array();

		// estimate frame type:
		$firstByteBinary = sprintf('%08b', ord($data[0]));
		$secondByteBinary = sprintf('%08b', ord($data[1]));
		$opcode = bindec(substr($firstByteBinary, 4, 4));
		$isMasked = ($secondByteBinary[0] == '1') ? true : false;
		$payloadLength = ord($data[1]) & 127;

		// close connection if unmasked frame is received:
		if ($isMasked === false) {
			$this->close(1002);
			return self::RETURN_STATE_NO_ACTION;
		}

		switch ($opcode) {
			// text frame:
			case 1:
				$decodedData['type'] = 'text';
				break;

			case 2:
				$decodedData['type'] = 'binary';
				break;

			// connection close frame:
			case 8:
				$decodedData['type'] = 'close';
				break;

			// ping frame:
			case 9:
				$decodedData['type'] = 'ping';
				break;

			// pong frame:
			case 10:
				$decodedData['type'] = 'pong';
				break;

			default:
				// Close connection on unknown opcode:
				$this->close(1003);
				break;
		}

		if ($payloadLength === 126) {
			$mask = substr($data, 4, 4);
			$payloadOffset = 8;
			$dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
		} elseif ($payloadLength === 127) {
			$mask = substr($data, 10, 4);
			$payloadOffset = 14;
			$tmp = '';
			for ($i = 0; $i < 8; $i++) {
				$tmp .= sprintf('%08b', ord($data[$i + 2]));
			}
			$dataLength = bindec($tmp) + $payloadOffset;
			unset($tmp);
		} else {
			$mask = substr($data, 2, 4);
			$payloadOffset = 6;
			$dataLength = $payloadLength + $payloadOffset;
		}

		/**
		 * We have to check for large frames here. socket_recv cuts at 1024 bytes
		 * so if websocket-frame is > 1024 bytes we have to wait until whole
		 * data is transferd.
		 */
		if (strlen($data) < $dataLength) {
			return self::RETURN_STATE_WAITING_DATA;
		}

		if ($isMasked === true) {
			for ($i = $payloadOffset; $i < $dataLength; $i++) {
				$j = $i - $payloadOffset;
				if (isset($data[$i])) {
					$unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
				}
			}
			$decodedData['payload'] = $unmaskedPayload;
		} else {
			$payloadOffset = $payloadOffset - 4;
			$decodedData['payload'] = substr($data, $payloadOffset);
		}

		if (!isset($decodedData['type'])) {
			$this->connection->sendHttpResponse(401)->close();
			return self::RETURN_STATE_NO_ACTION;
		}
		switch ($decodedData['type']) {

			case 'text':
			case 'binary':
				$this->data = $decodedData['payload'];
				return self::RETURN_STATE_SUCCESS;
				break;

			case 'close':
				$this->close();
				return self::RETURN_STATE_NO_ACTION;
				break;

			case 'ping':
				$connection = Connection::getCurrent();
				if ($connection) {
					$connection->writeRawData($this->encode($decodedData['payload'], 'pong', false));
				}
				return self::RETURN_STATE_NO_ACTION;
				break;

			case 'pong':
				return self::RETURN_STATE_NO_ACTION;
				break;
		}
		return self::RETURN_STATE_NO_ACTION;
	}

	/**
	 * @param int        $statusCode
	 * @param Connection $connection
	 */
	private function close($statusCode = 1000) {
		$payload = str_split(sprintf('%016b', $statusCode), 8);
		$payload[0] = chr(bindec($payload[0]));
		$payload[1] = chr(bindec($payload[1]));
		$payload = implode('', $payload);

		switch ($statusCode) {
			case 1000:
				$payload .= 'normal closure';
				break;

			case 1001:
				$payload .= 'going away';
				break;

			case 1002:
				$payload .= 'protocol error';
				break;

			case 1003:
				$payload .= 'unknown data (opcode)';
				break;

			case 1004:
				$payload .= 'frame too large';
				break;

			case 1007:
				$payload .= 'utf8 expected';
				break;

			case 1008:
				$payload .= 'message violates server policy';
				break;
		}

		$connection = Connection::getCurrent();
		if ($connection) {
			$connection->write($payload, 'close', false);
			$connection->close();
		}
	}
}
