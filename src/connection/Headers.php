<?php
namespace YiiWebSocket\Connection;
/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 4:07 PM
 * To change this template use File | Settings | File Templates.
 *
 */
class Headers {

	/**
	 * @var array
	 */
	protected $_headers = array();

	/**
	 * @var Connection
	 */
	private $_connection;

	/**
	 * @var bool
	 */
	private $_hasErrors = false;

	/**
	 * @var string
	 */
	private $_data = '';

	/**
	 * @param            $headers
	 * @param Connection $connection
	 */
	public function __construct($headers, Connection $connection) {
		$this->_connection = $connection;
		if ($this->parse($headers)) {
			$connection->setHeaders($this);
		}
	}

	/**
	 * @return bool
	 */
	public function isValid() {
		return !$this->_hasErrors;
	}

	/**
	 * @return mixed
	 */
	public function getPath() {
		return $this->getHeader('path');
	}

	/**
	 * @param string $header
	 * @param null   $default
	 *
	 * @return null
	 */
	public function getHeader($header, $default = null) {
		return $this->hasHeader($header) ? $this->_headers[$header] : $default;
	}

	/**
	 * @param $header
	 *
	 * @return bool
	 */
	public function hasHeader($header) {
		return array_key_exists($header, $this->_headers);
	}

	/**
	 * @return array
	 */
	public function getAll() {
		return $this->_headers;
	}

	/**
	 * @return string
	 */
	public function getData() {
		return $this->_data;
	}

	/**
	 * @param string $headers
	 *
	 * @return bool
	 */
	protected function parse($headers) {
		$chunk = explode("\n\n", $headers);
		$headers = array_shift($chunk);
		$this->_data = implode("\n\n", $chunk);

		$lines = preg_split("/\r\n/", $headers);
		if (!preg_match('/\AGET ([\w\-\/]+) HTTP\/1.1\z/', $lines[0], $matches)) {
			echo 'Invalid request: ' . $lines[0];
			$this->_hasErrors = true;
			$this->_connection->sendHttpResponse(400);
			return false;
		}
		$path = $matches[1];
		foreach ($lines as $line) {
			$line = chop($line);
			if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
				$this->_headers[$matches[1]] = $matches[2];
			}
		}
		$this->_headers['path'] = $path;
		return true;
	}

	public function __destruct() {
		echo "Destruct: Headers\n";
		unset($this->_data);
		unset($this->_headers);
		unset($this->_connection);
	}
}
