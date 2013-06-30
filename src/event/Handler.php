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
class Handler extends \YiiWebSocket\Component implements \YiiWebSocket\IClientEmitter {

	/**
	 * @return \YiiWebSocket\Socket
	 */
	final public function getSocket() {
		return \YiiWebSocket\Socket::current();
	}

	final public function emit() {
		return call_user_func_array(array($this->getSocket(), 'emit'), func_get_args());
	}

	final public function broadcast() {
		return call_user_func_array(array($this->getSocket(), 'broadcast'), func_get_args());
	}

	public function onConnection() {

	}

	public function onClose() {

	}
}
