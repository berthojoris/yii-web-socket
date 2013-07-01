<?php
namespace YiiWebSocket\Extension;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/20/13
 * Time: 11:34 AM
 * To change this template use File | Settings | File Templates.
 *
 * @method string getPath()
 * @method \YiiWebSocket\Server getServer()
 *
 * @method Path onConnection($callback)
 */
class Path extends \YiiWebSocket\Component {

	/**
	 * @var Path[]
	 */
	protected static $_paths = array();

	/**
	 * @var \YiiWebSocket\Collections\Socket
	 */
	protected $_sockets;

	/**
	 * @var string
	 */
	protected $_path;

	/**
	 * @var \YiiWebSocket\Server
	 */
	protected $_server;

	/**
	 * @var callable|\Closure
	 */
	protected $_authorizationCallback;

	/**
	 * @var \YiiWebSocket\Event\Handler
	 */
	protected $_handler;

	/**
	 * @param $path
	 *
	 * @return bool
	 */
	public static function exists($path) {
		return array_key_exists($path, self::$_paths);
	}

	/**
	 * @param $path
	 *
	 * @return null|Path
	 */
	public static function get($path) {
		if (self::exists($path)) {
			return self::$_paths[$path];
		}
		return null;
	}

	/**
	 * @param \YiiWebSocket\Server $server
	 * @param string $path
	 */
	public function __construct(\YiiWebSocket\Server $server, $path) {
		$this->_server = $server;
		$this->_path = $path;
		$this->_sockets = new \YiiWebSocket\Collections\Socket();
		self::$_paths[$this->getId()] = $this;

		$self = $this;
		$this->_server->onConnection(function (\YiiWebSocket\Socket $socket) use ($self) {
			if ($self->getPath() == $socket->getHeaders()->getPath()) {
				$socket->setPath($self);
				$authorization = $self->getAuthorizationCallback();
				if ($authorization !== null) {
					call_user_func($authorization, $socket, function ($errorMessage, $isAuthenticated) use ($self, $socket, &$sockets) {
						if (!$isAuthenticated) {
							echo $errorMessage;
							$socket->getConnection()->sendHttpResponse(401)->close();
						} else {
							$self->getSockets()->add($socket);
							$socket->onClose(function (\YiiWebSocket\Socket $socket) use ($self) {
								$self->getSockets()->remove($socket);
							});
							$self->emit('connection', $socket);
						}
					});
				} else {
					$self->getSockets()->add($socket);
					$socket->onClose(function (\YiiWebSocket\Socket $socket) use ($self) {
						$self->consoleLog('Unset socket from path: ' . $self->getPath());
						$self->getSockets()->remove($socket);
					});
					$self->emit('connection', $socket);
				}
			}
		});
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->_path;
	}

	/**
	 * @return \YiiWebSocket\Collections\Socket
	 */
	public function getSockets() {
		return $this->_sockets;
	}

	/**
	 * @param callable|\Closure $callback
	 *
	 * @return Path
	 */
	public function authorization($callback) {
		if (is_callable($callback) || ($callback instanceof \Closure)) {
			$this->_authorizationCallback = $callback;
		}
		return $this;
	}
}
