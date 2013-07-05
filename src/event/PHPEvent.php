<?php
namespace YiiWebSocket\Event;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/24/13
 * Time: 1:39 PM
 * To change this template use File | Settings | File Templates.
 */
class PHPEvent {

	/**
	 * @var string
	 */
	public $origin;

	/**
	 * @var string
	 */
	private $_url;

	private $_path;

	private $_accessToken;

	private $_socket;

	/**
	 * @param string $accessToken
	 * @param string $host
	 * @param int    $port
	 * @param string $path
	 */
	public function __construct($accessToken, $host, $port = 3002, $path = '/') {
		$this->_path = $path;
		$this->_url = sprintf('tcp://%s:%s%s', $host, $port, $path);
		$this->_accessToken = $accessToken;
	}

	/**
	 * @param $event
	 *
	 * @return int
	 * @throws \RuntimeException
	 */
	public function emit($event) {
		$this->_socket = stream_socket_client($this->_url, $errno, $errstr, 0, STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT);

		if (!$this->_socket) {
			throw new \RuntimeException($errstr, $errno);
		}

		stream_set_blocking($this->_socket, 0);

		$args = func_get_args();
		if ($this->send($this->createHeaders()) === false) {
			return false;
		}
		$sent = $this->send($this->createPackage(array_shift($args), $args));

		fclose($this->_socket);
		return $sent;
	}

	/**
	 * @param string $package
	 */
	private function send($package) {
		if (!is_resource($this->_socket)) {
			return false;
		}
		$len = strlen($package);
		for ($written = 0; $written < $len; $written += $fwrite) {
			$fwrite = @fwrite($this->_socket, substr($package, $written));
			if ($fwrite === false || $fwrite === 0) {
				return $written;
			}
		}
		return $written;
	}

	/**
	 * @return string
	 */
	private function createHeaders() {
		$cookies = array();
		foreach (\Yii::app()->request->cookies as $cookie) {
			$cookies[$cookie->name] = $cookie->value;
		}
		$headers = array(
			'GET ' . $this->_path . ' HTTP/1.1',
			'PHP-Event: yes',
			'Origin: ' . ($this->origin === null ? isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'unknown' : $this->origin),
			'Access-Token: ' . $this->_accessToken,
			'Cookie: ' . http_build_query($cookies)
		);
		return implode("\r\n", $headers) . "\n\n";
	}

	/**
	 * @param       $event
	 * @param array $arguments
	 *
	 * @return string
	 */
	private function createPackage($event, array $arguments) {
		$package = array(
			'event' => $event,
			'arguments' => array()
		);
		foreach ($arguments as $argument) {
			$package['arguments'][] = array(
				'type' => gettype($argument),
				'value' => $argument
			);
		}
		return json_encode($package);
	}
}