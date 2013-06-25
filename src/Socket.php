<?php
namespace YiiWebSocket;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 10:36 AM
 * To change this template use File | Settings | File Templates.
 *
 * @method \YiiWebSocket\Connection\Connection getConnection()
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
class Socket extends Component {

	/**
	 * @var string
	 */
	protected $_id;

	/**
	 * @var \YiiWebSocket\Connection\Connection
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
	 * @param Connection\Connection $connection
	 * @param Server                $server
	 */
	public function __construct(\YiiWebSocket\Connection\Connection $connection, Server $server) {
		$this->_id = $connection->getId();
		$this->_connection = $connection;
		$this->_server = $server;

		$self = $this;

		$connection->onData(function ($data) use ($self) {
			$config = Package::getClient()->unwrap($data, $self);

			if (is_array($config)) {
				$self->emit('request', $config);
				$self->emit($config['type'], $config);
			}
		});
	}

	/**
	 * @return Client
	 */
	public function getClient() {
		return $this->_connection->getClient();
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->getHeaders()->getPath();
	}

	/**
	 * @return \YiiWebSocket\Connection\Headers
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

	public function broadcast() {
		call_user_func_array(array($this->_path->getSockets(), 'emit'), func_get_args());
	}

	/**
	 * @param $event
	 */
	public function call($event) {
		$this->_connection->write(Package::wrap(func_get_args(), $this));
	}

	/**
	 * Close socket connection
	 */
	public function close() {
		$this->_connection->close();
	}

	public function free() {
		$this->consoleLog('Free socket resources');
		$this->emit('free', $this);
		unset($this->_connection);
		unset($this->_server);
		unset($this->_eventEmitter);
		unset($this->_session);
		unset($this->_path);
	}

	public function __destruct() {
		$this->consoleLog('Destruct: Socket');
		parent::__destruct();
	}
}
