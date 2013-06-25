<?php
namespace YiiWebSocket;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 12:18 AM
 * To change this template use File | Settings | File Templates.
 *
 * @property resource   $socket
 *
 */
class Client extends Component {

	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $ip;

	/**
	 * @var int
	 */
	public $port;

	/**
	 * @var resource
	 */
	private $_socket;

	/**
	 * @param \YiiWebSocket\Connection\Connection $connection
	 */
	public function __construct(\YiiWebSocket\Connection\Connection $connection) {
		$this->_socket = $connection->getStream();
		$socketName = stream_socket_get_name($this->_socket, true);
		$tmp = explode(':', $socketName);
		$this->ip = $tmp[0];
		$this->port = (int) $tmp[1];
		$this->id = (int) $this->_socket;
	}

	/**
	 * @return resource
	 */
	public function getSocket() {
		return $this->_socket;
	}

	public function __destruct() {
		$this->consoleLog('Destruct: Client');
		unset($this->_socket);
		parent::__destruct();
	}
}
