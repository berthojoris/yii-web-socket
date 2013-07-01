<?php
namespace YiiWebSocket\Collections;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/30/13
 * Time: 10:41 PM
 * To change this template use File | Settings | File Templates.
 */
class SocketSelector {

	/**
	 * @var Socket
	 */
	private $_sockets;

	public function __construct() {
		$this->_sockets = new Socket();
	}

	/**
	 * Get sockets from some paths
	 *
	 * EXAMPLE OF USAGE
	 * 1) ->of('/', '/chat');
	 * 2) ->of(array('/', '/chat'), '/event')
	 *
	 * @return SocketSelector
	 */
	public function of() {
		$paths = $this->argumentsToList(func_get_args());
		foreach ($paths as $pathId) {
			if ($path = \YiiWebSocket\Path::get($pathId)) {
				$this->_sockets->mergeWith($path->getSockets());
			}
		}
		return $this;
	}

	public function in() {
		$rooms = $this->argumentsToList(func_get_args());
		foreach ($rooms as $roomId) {
			if ($room = \YiiWebSocket\Room::getRoom($roomId)) {
				$this->_sockets->mergeWith($room->getSockets());
			}
		}
		return $this;
	}

	public function with() {

	}

	public function find($callback) {

	}

	/**
	 * @return Socket
	 */
	public function collection() {
		return $this->_sockets;
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	protected function argumentsToList(array $args) {
		$result = array();
		foreach ($args as $argument) {
			if (!is_array($argument)) {
				$argument = array($argument);
			}
			$result = array_merge($result, $argument);
		}
		return $result;
	}
}
