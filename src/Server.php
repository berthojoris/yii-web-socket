<?php
namespace YiiWebSocket;

require_once __DIR__ . DIRECTORY_SEPARATOR .  'vendor/autoload.php';

use YiiWebSocket\Extension\Path;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 10:42 AM
 * To change this template use File | Settings | File Templates.
 * @method \React\EventLoop\LoopInterface getLoop()
 *
 * @method Server onConnection($callback)
 * @method Server onClose($callback)
 *
 */
class Server extends Component {

	/**
	 * @var \React\Socket\Server
	 */
	private $_server;

	/**
	 * @var \React\EventLoop\LoopInterface
	 */
	protected $_loop;

	/**
	 * @var Config
	 */
	protected $_config;

	/**
	 * @var \YiiWebSocket\Connection\Connection[]
	 */
	protected $_connections = array();

	/**
	 * @var Socket[]
	 */
	protected $_sockets = array();

	/**
	 * @var Path[]
	 */
	protected $_paths = array();

	/**
	 * @var \YiiWebSocket\Connection\ADataConverter
	 */
	protected $_dataConverters = array();

	/**
	 * @param Config $config
	 */
	public function __construct(Config $config) {
		$this->_config = $config;

		$this->console()->debug = $config->debug;
		$this->console()->level = $config->debugLevel;

		$this->_loop = \React\EventLoop\Factory::create();
		$this->_server = new \React\Socket\Server($this->_loop);

		$self = $this;

		$this->_server->on('connection', array($this, 'handleConnection'));

		Process::setServer($this);
	}

	/**
	 * @param Connection\Connection $connection
	 *
	 * @return \YiiWebSocket\Connection\ADataConverter
	 */
	public function getDataConverter(\YiiWebSocket\Connection\Connection $connection) {
		$type = $connection->getType();
		if (!array_key_exists($type, $this->_dataConverters)) {
			$this->_dataConverters[$type] = \YiiWebSocket\Connection\ADataConverter::create($type);
		}
		return $this->_dataConverters[$type];
	}

	/**
	 * @param string $path
	 *
	 * @return Path
	 */
	public function of($path) {
		if (!array_key_exists($path, $this->_paths)) {
			$this->_paths[$path] = new Path($this, $path);
		}
		return $this->_paths[$path];
	}

	public function listen() {
		$this->consoleLog("Socket server listening on  " . $this->_config->getHost() . ':' . $this->_config->getPort());
		$this->dumpMemory();

		$this->_server->listen($this->_config->getPort(), $this->_config->getHost());
		$this->_loop->run();
	}

	/**
	 * @return Config
	 */
	public function getConfig() {
		return $this->_config;
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function hasConnection($id) {
		return array_key_exists($id, $this->_connections);
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function hasSocket($id) {
		return array_key_exists($id, $this->_sockets);
	}

	/**
	 * @param $id
	 *
	 * @return \YiiWebSocket\Connection\Connection|null
	 */
	public function getConnection($id) {
		if ($this->hasConnection($id)) {
			return $this->_connections[$id];
		}
		return null;
	}

	/**
	 * @param $id
	 */
	public function removeConnection($id) {
		if ($this->hasConnection($id)) {
			unset($this->_connections[$id]);
		}
	}

	/**
	 * @param $id
	 */
	public function removeSocket($id) {
		if ($this->hasSocket($id)) {
			unset($this->_sockets[$id]);
		}
	}

	/**
	 * @param \React\Socket\Connection $connection
	 */
	public function handleConnection(\React\Socket\Connection $connection) {
		$id = (int) $connection->stream;
		if ($this->hasConnection($id)) {
			$connection = $this->getConnection($id);
		} else {
			$connection = $this->createConnection($connection);
		}
	}

	/**
	 * @param \React\Socket\Connection $connection
	 */
	public function createConnection(\React\Socket\Connection $connection) {
		$this->console()->info('New connection');
		$this->dumpMemory();
		$connection->bufferSize = $this->_config->getPackageSize();
		$connection = new \YiiWebSocket\Connection\Connection($connection, $this);
		$this->_connections[$connection->getId()] = $connection;
		$self = $this;
		$connection->onConnect(function () use ($connection, $self) {

			$socket = $self->createSocket($connection);
			$self->emit('connection', $socket);

			$connection->onClose(function () use ($connection, $self) {
				$self->consoleLog('Remove connection');
				$self->emit('close', $connection);
				$self->removeConnection($connection->getId());
				$self->dumpMemory();
			});
		});
	}

	/**
	 * @param Connection\Connection $connection
	 *
	 * @return Socket
	 */
	public function createSocket(\YiiWebSocket\Connection\Connection $connection) {
		$socket = new Socket($connection, $this);
		$this->_sockets[$socket->getId()] = $socket;
		$self = $this;
		$connection->onClose(function () use ($socket, $self) {
			$self->consoleLog('Emit close event in Socket #' . $socket->getId());
			call_user_func(array($socket->getEventEmitter(), 'emit'), 'close', $socket);
			$socket->free();
			$self->removeSocket($socket->getId());
		});
		return $socket;
	}
}
