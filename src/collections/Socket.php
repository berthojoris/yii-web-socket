<?php
namespace YiiWebSocket\Collections;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/20/13
 * Time: 12:59 AM
 * To change this template use File | Settings | File Templates.
 *
 * @method integer getCount()
 *
 * @method Socket onExit($callback)
 * @method Socket onAdd($callback)
 * @method Socket onRemove($callback)
 *
 */
class Socket extends ACollection {

	/**
	 * @var \YiiWebSocket\Socket[]
	 */
	protected $_sockets = array();

	/**
	 * @var int
	 */
	protected $_count = 0;

	/**
	 * @param \YiiWebSocket\Socket $socket
	 */
	public function add($socket) {
		if (!$this->exists($socket)) {
			$self = $this;
			$this->_sockets[$socket->getId()] = $socket;
			$this->_count++;
			$socket->onClose(function ($socket) use ($self) {
				$self->remove($socket);
			});
			$this->emitAdd($socket);
		}
	}

	/**
	 * @param \YiiWebSocket\Socket $socket
	 *
	 * @return Socket
	 */
	public function remove($socket) {
		if ($this->exists($socket)) {
			$this->emitRemove($socket);
			$this->_count--;
			unset($this->_sockets[$socket->getId()]);
		}
		return $this;
	}

	/**
	 * @param \YiiWebSocket\Socket $socket
	 *
	 * @return bool
	 */
	public function exists($socket) {
		return array_key_exists($socket->getId(), $this->_sockets);
	}

	/**
	 * Emit event to all collection items without current socket
	 *
	 * @return void|\YiiWebSocket\Component
	 */
	public function emit() {
		$json = \YiiWebSocket\Package::wrap(func_get_args());
		$currentId = \YiiWebSocket\Socket::current()->getId();
		foreach ($this->_sockets as $socket) {
			if ($currentId != $socket->getId()) {
				$socket->getConnection()->write($json);
			}
		}
		return $this;
	}

	/**
	 *
	 * Emit event to all collection items with current socket
	 *
	 * @return Socket
	 */
	public function broadcast() {
		$json = \YiiWebSocket\Package::wrap(func_get_args());
		foreach ($this->_sockets as $socket) {
			$socket->getConnection()->write($json);
		}
		return $this;
	}

	/**
	 * Call $callback for every socket in collection
	 *
	 * @param $callback
	 */
	public function each($callback) {
		if (!is_callable($callback) && !($callback instanceof \Closure)) {
			return false;
		}
		foreach ($this->_sockets as $socket) {
			call_user_func($callback, $socket);
		}
		return true;
	}

	/**
	 * Filter current collection using callback function
	 *
	 * @param \Closure|callable $callback
	 *
	 * @return Socket
	 */
	public function filter($callback) {
		$collection = new Socket();
		if (!is_callable($callback) && !($callback instanceof \Closure)) {
			return $collection;
		}
		foreach ($this->_sockets as $socket) {
			if (call_user_func($callback, $socket)) {
				$collection->add($socket);
			}
		}
		return $collection;
	}

	/**
	 * Merge current collection with other collection
	 *
	 * @param Socket $collection
	 */
	public function mergeWith(Socket $collection) {
		$self = $this;
		$collection->each(function (\YiiWebSocket\Socket $socket) use ($self) {
			$self->add($socket);
		});
	}

	/**
	 * @return mixed
	 */
	public function delete() {
		$this->consoleLog('Delete: Collections\Socket');
		$this->_emit('delete', $this);
		$this->_sockets = array();
		$this->_eventEmitter = null;
//		unset($this->_sockets);
//		unset($this->_eventEmitter);
	}

	public function __destruct() {
		$this->consoleLog('Destruct: Collections\Socket');
	}
}
