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

	/**
	 * @param string|int $path
	 *
	 * @return \YiiWebSocket\Extension\Path
	 */
	final public function path($path) {
		return \YiiWebSocket\Extension\Path::get($path);
	}

	/**
	 * @param string|int $room
	 *
	 * @return \YiiWebSocket\Extension\Room
	 */
	final public function room($room) {
		return \YiiWebSocket\Extension\Room::getRoom($room);
	}

	final public function emit() {
		$socket = $this->getSocket();
		if ($socket) {
			call_user_func_array(array($socket, 'emit'), func_get_args());
		}
	}

	final public function broadcast() {
		$socket = $this->getSocket();
		if ($socket) {
			call_user_func_array(array($socket, 'broadcast'), func_get_args());
		}
	}

	public function onConnection() {

	}

	public function onClose() {

	}
}
