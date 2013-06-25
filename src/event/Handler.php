<?php
namespace YiiWebSocket\Event;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/24/13
 * Time: 3:34 PM
 * To change this template use File | Settings | File Templates.
 *
 */
class Handler extends \YiiWebSocket\Component {

	/**
	 * @var \YiiWebSocket\Socket
	 */
	private $_socket;

	/**
	 * @return \YiiWebSocket\Socket
	 */
	final public function getSocket() {
		return $this->_socket ? $this->_socket : null;
	}

	/**
	 * @param \YiiWebSocket\Socket $socket
	 *
	 * @return Handler
	 */
	final public function setSocket(\YiiWebSocket\Socket $socket) {
		$this->_socket = $socket;
		return $this;
	}

	final public function call() {
		if ($this->_socket) {
			call_user_func_array(array($this->_socket, 'call'), func_get_args());
		}
	}

	final public function broadcast() {
		if ($this->_socket) {
			call_user_func_array(array($this->_socket, 'broadcast'), func_get_args());
		}
	}

	public function onConnection() {

	}

	public function onClose() {

	}
}
