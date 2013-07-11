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
	 * @param \YiiWebSocket\IIdentified $item
	 */
	public function add(\YiiWebSocket\IIdentified $item) {
		/** @var \YiiWebSocket\Socket $item */
		if (!$this->exists($item)) {
			$self = $this;
			$item->onClose(function ($socket) use ($self) {
				$self->remove($socket);
			});
			parent::add($item);
		}
	}

	/**
	 * Emit event to all collection items without current socket
	 *
	 * @return void|\YiiWebSocket\Component
	 */
	public function emit() {
		$json = \YiiWebSocket\Package::wrap(func_get_args());
		$currentSocket = \YiiWebSocket\Socket::current();
		$currentId = $currentSocket ? $currentSocket->getId() : false;
		/** @var \YiiWebSocket\Socket $socket */
		foreach ($this->_collection as $socket) {
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
		/** @var \YiiWebSocket\Socket $socket */
		foreach ($this->_sockets as $socket) {
			$socket->getConnection()->write($json);
		}
		return $this;
	}

	/**
	 * @param \YiiWebSocket\Package\Frame $frame
	 *
	 * @return mixed
	 */
	public function emitFrame(\YiiWebSocket\Package\Frame $frame) {
		$json = $frame->getFrame();
		$currentSocket = \YiiWebSocket\Socket::current();
		$currentId = $currentSocket ? $currentSocket->getId() : false;
		/** @var \YiiWebSocket\Socket $socket */
		foreach ($this->_collection as $socket) {
			if ($currentId != $socket->getId()) {
				$socket->getConnection()->write($json);
			}
		}
	}

	/**
	 * @param \YiiWebSocket\Package\Frame $frame
	 *
	 * @return mixed
	 */
	public function broadcastFrame(\YiiWebSocket\Package\Frame $frame) {
		$json = $frame->getFrame();
		/** @var \YiiWebSocket\Socket $socket */
		foreach ($this->_collection as $socket) {
			$socket->getConnection()->write($json);
		}
	}


	public function __destruct() {
		$this->consoleLog('Destruct: Collections\Socket');
	}
}
