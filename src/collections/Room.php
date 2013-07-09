<?php
namespace YiiWebSocket\Collections;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/30/13
 * Time: 9:29 PM
 * To change this template use File | Settings | File Templates.
 */
class Room extends ACollection {

	/**
	 * @param $id
	 *
	 * @return \YiiWebSocket\Extension\Room|null
	 */
	public function get($id) {
		return array_key_exists($id, $this->_collection) ? $this->_rooms[$id] : null;
	}

	/**
	 * Send to all clients except current socket
	 */
	public function emit() {
		$frame = new \YiiWebSocket\Package\Frame(func_get_args());
		$this->emitFrame($frame);
	}


	/**
	 * Send to all clients
	 */
	public function broadcast() {
		$frame = new \YiiWebSocket\Package\Frame(func_get_args());
		$this->broadcastFrame($frame);
	}

	/**
	 * @param \YiiWebSocket\Package\Frame $frame
	 *
	 * @return mixed
	 */
	public function emitFrame(\YiiWebSocket\Package\Frame $frame) {
		/** @var \YiiWebSocket\Extension\Room $room */
		foreach ($this->_collection as $room) {
			$room->emitFrame($frame);
		}
	}

	/**
	 * @param \YiiWebSocket\Package\Frame $frame
	 *
	 * @return mixed
	 */
	public function broadcastFrame(\YiiWebSocket\Package\Frame $frame) {
		/** @var \YiiWebSocket\Extension\Room $room */
		foreach ($this->_collection as $room) {
			$room->broadcastFrame($frame);
		}
	}
}