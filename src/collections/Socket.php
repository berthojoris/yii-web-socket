<?php
namespace YiiWebSocket\Collections;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/20/13
 * Time: 12:59 AM
 * To change this template use File | Settings | File Templates.
 *
 * @method Socket onExit($callback)
 *
 */
class Socket extends \YiiWebSocket\Component {

	/**
	 * @var \YiiWebSocket\Socket[]
	 */
	protected $_sockets = array();

	/**
	 * @param \YiiWebSocket\Socket $socket
	 */
	public function add(\YiiWebSocket\Socket $socket) {
		$self = $this;
		$this->_sockets[$socket->getId()] = $socket;
		$socket->onClose(function ($socket) use ($self) {
			$self->remove($socket);
		});
	}

	/**
	 * @param \YiiWebSocket\Socket $socket
	 *
	 * @return Socket
	 */
	public function remove(\YiiWebSocket\Socket $socket) {
		if ($this->exists($socket)) {
			parent::emit('exit', $socket);
			unset($this->_sockets[$socket->getId()]);
		}
		return $this;
	}

	/**
	 * @param \YiiWebSocket\Socket $socket
	 *
	 * @return bool
	 */
	public function exists(\YiiWebSocket\Socket $socket) {
		return array_key_exists($socket->getId(), $this->_sockets);
	}

	/**
	 * @return void|\YiiWebSocket\Component
	 */
	public function emit() {
		$json = \YiiWebSocket\Package::wrap(func_get_args());
		foreach ($this->_sockets as $socket) {
			$socket->getConnection()->write($json);
		}
	}
}
