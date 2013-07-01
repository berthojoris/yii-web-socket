<?php
namespace YiiWebSocket;

use YiiWebSocket\Connection\Connection;
use YiiWebSocket\Connection\Headers;

use YiiWebSocket\Extension\Path;
use YiiWebSocket\Extension\Room;
use YiiWebSocket\Extension\Data;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 10:36 AM
 * To change this template use File | Settings | File Templates.
 *
 * @method Connection getConnection()
 * @method Server getServer()
 * @method string getId()
 * @method Session getSession()
 * @method Socket setPath(Path $path)
 *
 * @method Socket onClose($callback)
 * @method Socket onData($callback)
 * @method Socket onEvent($callback)
 * @method Socket onCallback($callback)
 * @method Socket onRequest($callback)
 */
class Socket extends Component implements IClientEmitter {

	/**
	 * @var Socket
	 */
	private static $_currentSocket;

	/**
	 * @var string
	 */
	protected $_id;

	/**
	 * @var Connection
	 */
	protected $_connection;

	/**
	 * @var Server
	 */
	protected $_server;

	/**
	 * @var Session
	 */
	protected $_session;

	/**
	 * @var Path
	 */
	protected $_path;

	/**
	 * @var Room
	 */
	protected $_rooms;

	/**
	 * @var Data
	 */
	protected $_data;

	/**
	 * @return Socket
	 */
	public static function current() {
		return self::$_currentSocket;
	}

	/**
	 * @param Socket $socket
	 */
	public static function setCurrent(Socket $socket) {
		self::$_currentSocket = $socket;
	}

	/**
	 * Clear current socket
	 */
	public static function clear(Socket $socket) {
		if (self::$_currentSocket && self::$_currentSocket->getId() == $socket->getId()) {
			self::$_currentSocket = null;
		}
	}

	/**
	 * @param Connection $connection
	 * @param Server     $server
	 */
	public function __construct(\YiiWebSocket\Connection\Connection $connection, Server $server) {
		$this->_id = $connection->getId();
		$this->_connection = $connection;
		$this->_server = $server;

		$self = $this;

		$connection->onData(function ($data) use ($self) {
			Socket::setCurrent($self);
			$config = Package::getClient()->unwrap($data, $self);

			if (is_array($config)) {
				call_user_func(array($self->getEventEmitter(), 'emit'), 'request', $config);
				call_user_func(array($self->getEventEmitter(), 'emit'), $config['type'], $config);
			}
			Socket::clear($self);
		});
		$this->consoleLog('On socket create.');
		$this->dumpMemory();
	}

	/**
	 * @return Client
	 */
	public function getClient() {
		return $this->_connection->getClient();
	}

	/**
	 * @return Path
	 */
	public function getPath() {
		return $this->_path;
	}

	/**
	 * @return Headers
	 */
	public function getHeaders() {
		return $this->_connection->getHeaders();
	}

	/**
	 * @param Session $session
	 *
	 * @return Socket
	 */
	public function setSession(Session $session) {
		$this->_session = $session;
		return $this;
	}

	/**
	 * @param int|string $room
	 *
	 * @return Socket
	 */
	public function join($room) {
		$room = Room::getRoom($room, true)->join($this);
		$this->rooms()->add($room);
		return $this;
	}

	/**
	 * @param int|string $room
	 */
	public function leave($room) {
		if ($room = $this->rooms()->get($room)) {
			$room->leave($this);
		}
	}

	/**
	 * Return room by roomId
	 *
	 * @param $room
	 *
	 * @return Room|null
	 */
	public function room($room) {
		return $this->rooms()->get($room);
	}

	/**
	 * Return rooms with current socket
	 *
	 * @return Collections\Room
	 */
	public function rooms() {
		if ($this->_rooms === null) {
			$this->_rooms = new \YiiWebSocket\Collections\Room();
		}
		return $this->_rooms;
	}

	/**
	 * @return Data
	 */
	public function data() {
		if ($this->_data === null) {
			$this->_data = new Data();
		}
		return $this->_data;
	}

	/**
	 * @return mixed|void|Component
	 */
	public function emit() {
		$this->_connection->write(Package::wrap(func_get_args(), $this));
	}

	/**
	 * Emit event to all sockets in current path
	 *
	 * @return mixed|void
	 */
	public function broadcast() {
		call_user_func_array(array($this->_path->getSockets(), 'emit'), func_get_args());
	}

	/**
	 * Close socket connection
	 */
	public function close() {
		$this->_connection->close();
	}

	public function free() {
		$this->consoleLog('Free socket resources');
		$this->dumpMemory();
		$this->_emit('free', $this);
		self::clear($this);
		unset($this->_connection);
		unset($this->_server);
		unset($this->_eventEmitter);
		unset($this->_session);
		unset($this->_path);
		unset($this->_rooms);
		unset($this->_data);
	}

	public function __destruct() {
		$this->consoleLog('Destruct: Socket');
		parent::__destruct();
	}

	protected function _emit() {
		return call_user_func_array(array($this->getEventEmitter(), 'emit'), func_get_args());
	}
}
