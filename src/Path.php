<?php
namespace YiiWebSocket;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/20/13
 * Time: 11:34 AM
 * To change this template use File | Settings | File Templates.
 *
 * @method string getPath()
 * @method Server getServer()
 * @method \YiiWebSocket\Collections\Socket getSockets()
 *
 * @method Path onConnection($callback)
 */
class Path extends Component {

	/**
	 * @var \YiiWebSocket\Collections\Socket
	 */
	protected $_sockets;

	/**
	 * @var string
	 */
	protected $_path;

	/**
	 * @var Server
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
	 * @param Server $server
	 * @param string $path
	 */
	public function __construct(Server $server, $path) {
		$this->_server = $server;
		$this->_path = $path;
		$this->_sockets = new \YiiWebSocket\Collections\Socket();

		$self = $this;
		$this->_server->onConnection(function (Socket $socket) use ($self) {
			if ($self->getPath() == $socket->getPath()) {
				$socket->setPath($self);
				$authorization = $self->getAuthorizationCallback();
				if ($authorization !== null) {
					call_user_func($authorization, $socket, function ($errorMessage, $isAuthenticated) use ($self, $socket, &$sockets) {
						if (!$isAuthenticated) {
							echo $errorMessage;
							$socket->getConnection()->sendHttpResponse(401)->close();
						} else {
							$self->getSockets()->add($socket);
							$socket->onClose(function (Socket $socket) use ($self) {
								$self->getSockets()->remove($socket);
							});
							$self->emit('connection', $socket);
						}
					});
				} else {
					$self->getSockets()->add($socket);
					$socket->onClose(function (Socket $socket) use ($self) {
						$self->consoleLog('Unset socket from path: ' . $self->getPath());
						$self->getSockets()->remove($socket);
					});
					$self->emit('connection', $socket);
				}
			}
		});
	}

	/**
	 * @param Event\Handler $handler
	 */
	public function setHandler(\YiiWebSocket\Event\Handler $handler) {

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
