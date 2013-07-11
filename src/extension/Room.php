<?php
namespace YiiWebSocket\Extension;

use YiiWebSocket\IIdentified;
use YiiWebSocket\IClientEmitter;
use YiiWebSocket\IFrameEmitter;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/26/13
 * Time: 9:58 PM
 * To change this template use File | Settings | File Templates.
 *
 * @method Room onJoin(callable $callback)
 * @method Room onLeave(callable $callback)
 * @method Room onDelete(callable $callback)
 *
 */
class Room extends \YiiWebSocket\Component implements IClientEmitter, IIdentified, IFrameEmitter {

	/**
	 * @var Room[]
	 */
	private static $_rooms = array();

	/**
	 * @var mixed
	 */
	private $_id;

	/**
	 * @var \YiiWebSocket\Collections\Socket
	 */
	private $_sockets;

	/**
	 * @var Data
	 */
	protected $_data;

	/**
	 * @param string|int $id
	 * @param bool       $createIFNull
	 *
	 * @return Room|null
	 */
	public static function getRoom($id, $createIFNull = false) {
		if (!self::roomExists($id)) {
			if ($createIFNull) {
				return new Room($id);
			}
			return null;
		}
		return self::$_rooms[$id];
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public static function roomExists($id) {
		return array_key_exists($id, self::$_rooms);
	}

	/**
	 * @param $id
	 */
	public static function removeRoom($id) {
		if (self::roomExists($id)) {
			self::$_rooms[$id]->delete();
			unset(self::$_rooms[$id]);
		}
	}

	/**
	 * @param $id
	 */
	public function __construct($id) {
		$this->_id = $id;
		$this->_sockets = new \YiiWebSocket\Collections\Socket();

		$self = $this;
		$this->_sockets->onRemove(function (\YiiWebSocket\Socket $socket) use ($self) {
			$self->leave($socket);
		});
		self::$_rooms[$id] = $this;
	}

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * @return \YiiWebSocket\Collections\Socket
	 */
	public function getSockets() {
		return $this->_sockets;
	}

	/**
	 * @param \YiiWebSocket\Socket $socket
	 *
	 * @return Room
	 */
	public function join(\YiiWebSocket\Socket $socket) {
		$this->_sockets->add($socket);
		$this->_emit('join', $socket);
		return $this;
	}

	/**
	 * @param \YiiWebSocket\Socket $socket
	 */
	public function leave(\YiiWebSocket\Socket $socket) {
		if ($this->exists($socket)) {
			$this->_emit('leave', $socket);
			$this->_sockets->remove($socket);
		}
	}

	/**
	 * @param \YiiWebSocket\Socket $socket
	 *
	 * @return bool
	 */
	public function exists(\YiiWebSocket\Socket $socket) {
		return $this->_sockets->exists($socket);
	}

	public function emit() {
		return call_user_func_array(array($this->_sockets, 'emit'), func_get_args());
	}

	public function broadcast() {
		return call_user_func_array(array($this->_sockets, 'broadcast'), func_get_args());
	}

	/**
	 * @param \YiiWebSocket\Package\Frame $frame
	 *
	 * @return mixed
	 */
	public function emitFrame(\YiiWebSocket\Package\Frame $frame) {
		$this->_sockets->emitFrame($frame);
	}

	/**
	 * @param \YiiWebSocket\Package\Frame $frame
	 *
	 * @return mixed
	 */
	public function broadcastFrame(\YiiWebSocket\Package\Frame $frame) {
		$this->_sockets->broadcastFrame($frame);
	}

	/**
	 * @return Data
	 */
	public function data() {
		if ($this->_data === null) {
			$this->_data = new Data();
		}
		return $this->_data;
	}

	public function delete() {
		$this->_emit('delete', $this);
		$this->_sockets->delete();
		unset($this->_sockets);
		unset($this->_eventEmitter);
		unset($this->_data);
		unset(self::$_rooms[$this->getId()]);
	}

	protected function _emit() {
		return call_user_func_array(array($this->getEventEmitter(), 'emit'), func_get_args());
	}
}