<?php
namespace YiiWebSocket\Connection;

use YiiWebSocket\Process;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/13/13
 * Time: 11:46 PM
 * To change this template use File | Settings | File Templates.
 *
 * @property int        $id
 * @property resource   $socket
 * @property bool       $handshake
 *
 * @method \YiiWebSocket\Client getClient()
 * @method \YiiWebSocket\Connection\Headers getHeaders()
 * @method \YiiWebSocket\Server getServer()
 * @method bool getHandshake()
 * @method \React\Socket\Connection getConnection()
 *
 * @method Connection onClose($callback)
 * @method Connection onConnect($callback)
 * @method Connection onData($callback)
 * @method Connection onFree($callback)
 *
 */
class Connection extends \YiiWebSocket\Component {

	/**
	 * @var Connection
	 */
	private static $_current;

	/**
	 * @var Type
	 */
	protected $_type;

	/**
	 * @var \React\Socket\Connection
	 */
	protected $_connection;

	/**
	 * @var bool
	 */
	protected $_handshake = false;

	/**
	 * @var bool
	 */
	protected $_resolved = false;

	/**
	 * @var \YiiWebSocket\Client
	 */
	protected $_client;

	/**
	 * @var \YiiWebSocket\Server
	 */
	protected $_server;

	/**
	 * @var Headers
	 */
	protected $_headers;

	/**
	 * @var \YiiWebSocket\Socket
	 */
	protected $_socket;

	/**
	 * @var string
	 */
	protected $_dataBuffer = '';

	/**
	 * @var bool
	 */
	protected $_isWaitingForData = false;

	/**
	 * @param Connection $connection
	 */
	public static function setCurrent(Connection $connection) {
		self::$_current = $connection;
	}

	/**
	 * @return Connection
	 */
	public static function getCurrent() {
		return self::$_current;
	}

	public static function removeCurrent(Connection $connection) {
		if (self::$_current && $connection->getId() == self::$_current->getId()) {
			self::$_current = null;
		}
	}

	public function __construct(\React\Socket\Connection $connection, \YiiWebSocket\Server $server) {
		$this->_server = $server;
		$this->_connection = $connection;

		$this->_client = new \YiiWebSocket\Client($this);

		$self = $this;
		$this->_connection->on('data', function ($data) use ($self, $connection) {
			Connection::setCurrent($self);
			if ($self->isResolved()) {
				if ($self->getHandshake()) {
					if ($self->getIsWaitingForData() === true) {
						$data = $self->getDataBuffer() . $data;
						$self->setIsWaitingForData(false);
					}
					$state = call_user_func(array($self->getType()->getDataConverter(), 'decode'), $data);
					if ($state == ADataConverter::RETURN_STATE_SUCCESS) {
						$self->emit('data', $self->getType()->getDataConverter()->data, $self);
					} else if ($state == ADataConverter::RETURN_STATE_WAITING_DATA) {
						//  wait untill all data will be transfered
						$self->setIsWaitingForData(true);
						$self->setDataBuffer($data);
						$self->console()->info('Waiting for data...');
					} else if ($state == ADataConverter::RETURN_STATE_NO_ACTION) {
						//  seems like all actions was done
					}
				} else {
					$self->close();
				}
			} else {
				$self->resolve($data);
			}
		});
		$this->_connection->on('error', function ($error) use ($self) {
			$self->console()->error($error);
			$self->emit('error', $error, $self);
		});
		$this->_connection->on('end', function () use ($self) {
			$self->console()->info('Close connection #' . $self->getId());
			$self->emit('close');
			$self->free();
			unset($self);
		});
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->_client->id;
	}

	/**
	 * @param Type $type
	 */
	public function setType(Type $type) {
		if ($this->_type === null) {
			$this->_type = $type;
		}
	}

	/**
	 * @return Type
	 */
	public function getType() {
		return $this->_type;
	}

	/**
	 * @param \YiiWebSocket\Socket $socket
	 *
	 * @return Connection
	 */
	public function setSocket(\YiiWebSocket\Socket $socket) {
		$this->_socket = $socket;
		return $this;
	}

	/**
	 * @return resource
	 */
	public function getStream() {
		return $this->_connection->stream;
	}

	/**
	 * @return bool
	 */
	public function isResolved() {
		return $this->_resolved;
	}

	/**
	 * @param Headers $headers
	 *
	 * @return Connection
	 */
	public function setHeaders(Headers $headers) {
		if ($this->_headers === null) {
			$this->_headers = $headers;
		}
		return $this;
	}

	/**
	 * @param string $data
	 */
	public function resolve($data) {
		if ($this->isResolved()) {
			return;
		}
		if (Process::getServer()->getConfig()->getConnectionResolver()->resolve($data, $this)) {
			$this->_resolved = true;
			$handshakeHandler = $this->getType()->getHandshake();
			echo 1;
			$this->_handshake = $handshakeHandler->prepare($this)->doHandshake();
			$handshakeHandler->clean();
			if ($this->_handshake) {
				$this->emit('connect', $this);
				$data = $this->_headers->getData();
				if ($data) {
					$this->_connection->emit('data', array($data, $this->_connection));
				}
			} else {
				$this->console()->error('Handshake error: send status 401 and close connection');
				$this->sendHttpResponse(401)->close();
			}
		} else {
			$this->console()->error('Could not resolve connection: send status 400 and close connection');
			$this->sendHttpResponse();
			$this->close();
		}
	}

	/**
	 * @param string $data
	 *
	 * @return bool
	 */
	public function write($data) {
		$data = call_user_func_array(array($this->getType()->getDataConverter(), 'encode'), func_get_args());
		if ($data) {
			return $this->_connection->write($data);
		}
		return false;
	}

	/**
	 * @param $data
	 *
	 * @return bool
	 */
	public function writeRawData($data) {
		return $this->_connection->write($data);
	}

	/**
	 * @param      $data
	 * @param bool $encode
	 *
	 * @return bool|int
	 */
	public function forceWrite($data, $encode = true) {
		if ($encode) {
			$data = call_user_func_array(array($this->getType()->getDataConverter(), 'encode'), array($data));
		}
		if ($data) {
			return fwrite($this->getStream(), $data);
		}
		return false;
	}

	/**
	 * @param int $httpStatusCode
	 *
	 * @return Connection
	 */
	public function sendHttpResponse($httpStatusCode = 400) {
		$httpHeader = 'HTTP/1.1 ';
		switch ($httpStatusCode) {
			case 400:
				$httpHeader .= '400 Bad Request';
				break;

			case 401:
				$httpHeader .= '401 Unauthorized';
				break;

			case 403:
				$httpHeader .= '403 Forbidden';
				break;

			case 404:
				$httpHeader .= '404 Not Found';
				break;

			case 501:
				$httpHeader .= '501 Not Implemented';
				break;
		}
		$httpHeader .= "\r\n";
		$this->writeRawData($httpHeader);
		return $this;
	}

	public function close() {
		$this->getConnection()->close();
	}

	public function free() {
		$this->consoleLog('Free connection resources');
		$this->emit('free', $this);
		self::removeCurrent($this);
		unset($this->_connection);
		unset($this->_type);
		unset($this->_client);
		unset($this->_server);
		unset($this->_eventEmitter);
		unset($this->_headers);
		unset($this->_socket);
	}

	public function __destruct() {
		$this->consoleLog('Destruct: Connection');
		parent::__destruct();
	}
}
