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
	 * @var \YiiWebSocket\Extension\Room[]
	 */
	private $_rooms = array();

	/**
	 * @param $id
	 *
	 * @return \YiiWebSocket\Extension\Room|null
	 */
	public function get($id) {
		return array_key_exists($id, $this->_rooms) ? $this->_rooms[$id] : null;
	}

	/**
	 * @param \YiiWebSocket\Extension\Room $room
	 */
	public function add($room) {
		$this->_rooms[$room->getId()] = $room;
		$this->emitAdd($room);
	}

	/**
	 * Check if collection has $item
	 *
	 * @param \YiiWebSocket\Extension\Room $room
	 *
	 * @return bool
	 */
	public function exists($room) {
		return array_key_exists($room->getId(), $this->_rooms);
	}

	/**
	 * Remove item from current collection
	 *
	 * @param \YiiWebSocket\Extension\Room $room
	 */
	public function remove($room) {
		if ($this->exists($room)) {
			$this->emitRemove($room);
			unset($this->_rooms[$room->getId()]);
		}
	}

	/**
	 * Send to all clients except current socket
	 */
	public function emit() {
		$arguments = func_get_args();
		foreach ($this->_rooms as $room) {
			call_user_func_array(array($room, 'emit'), $arguments);
		}
	}


	/**
	 * Send to all clients
	 */
	public function broadcast() {
		$arguments = func_get_args();
		foreach ($this->_rooms as $room) {
			call_user_func_array(array($room, 'broadcast'), $arguments);
		}
	}

	/**
	 * Delete collection
	 *
	 * @return mixed
	 */
	public function delete() {
		$this->_emit('delete', $this);
		unset($this->_rooms);
		unset($this->_eventEmitter);
	}
}
