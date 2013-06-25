<?php
namespace YiiWebSocket\Connection;

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
 * @method const getType()
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
	 * @var
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

	public function __construct(\React\Socket\Connection $connection, \YiiWebSocket\Server $server) {
		$this->_server = $server;
		$this->_connection = $connection;

		$this->_client = new \YiiWebSocket\Client($this);

		$self = $this;
		$this->_connection->on('data', function ($data) use ($self, $connection) {
			if ($self->isResolved()) {
				if ($self->getHandshake()) {
					if ($self->getIsWaitingForData() === true) {
						$data = $self->getDataBuffer() . $data;
						$self->setIsWaitingForData(false);
					}
					$self->getDataConverter()->connection = $self;
					$state = call_user_func(array($self->getDataConverter(), 'decode'), $data);
					$self->getDataConverter()->connection = null;
					if ($state == ADataConverter::RETURN_STATE_SUCCESS) {
						$self->emit('data', $self->getDataConverter()->data, $self);
					} else if ($state == ADataConverter::RETURN_STATE_WAITING_DATA) {
						//  wait untill all data will be transfered
						$self->setIsWaitingForData(true);
						$self->setDataBuffer($data);
						echo 'Waiting for data...';
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
		$this->_connection->on('error', function () {
			echo 'Error';
		});
		$this->_connection->on('end', function () use ($self) {
			$self->consoleLog('Close connection #' . $self->getId());
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
	 * @return ADataConverter
	 */
	public function getDataConverter() {
		return $this->_server->getDataConverter($this);
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
		$handshakeHandler = $this->_server->getConnectionResolver()->resolve($data, $this);
		if ($handshakeHandler === null) {
			echo 'Handshake handler is null';
			$this->close();
		} else {
			$this->_resolved = true;
			$handshakeHandler->connection = $this;
			$handshakeHandler->headers = $this->getHeaders();
			$this->_handshake = $handshakeHandler->doHandshake();
			unset($handshakeHandler->connection);
			unset($handshakeHandler->headers);
			if ($this->_handshake) {
				$this->emit('connect', $this);
				$data = $this->_headers->getData();
				if ($data) {
					$this->_connection->emit('data', array($data, $this->_connection));
				}
			} else {
				echo 'Handshake error';
			}
		}
	}

	/**
	 * @param string $data
	 *
	 * @return bool
	 */
	public function write($data) {
		$this->getDataConverter()->connection = $this;
		$data = call_user_func_array(array($this->getDataConverter(), 'encode'), func_get_args());
		$this->getDataConverter()->connection = null;
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
			$this->getDataConverter()->connection = $this;
			$data = call_user_func_array(array($this->getDataConverter(), 'encode'), array($data));
			$this->getDataConverter()->connection = null;
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
		unset($this->_connection);
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
