<?php
namespace YiiWebSocket\Event;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/22/13
 * Time: 1:45 PM
 * To change this template use File | Settings | File Templates.
 *
 * @method \YiiWebSocket\Socket getSocket()
 *
 */
class Listener extends \YiiWebSocket\Component {

	/**
	 * @var \YiiWebSocket\Socket
	 */
	protected $_socket;

	/**
	 * @var object
	 */
	protected $_handler;

	/**
	 * @param \YiiWebSocket\Socket $socket
	 */
	public function __construct(\YiiWebSocket\Socket $socket) {
		$this->_socket = $socket;
		$self = $this;
		$socket->onEvent(array($this, 'handle'));
		$socket->onClose(function () use ($self) {
			$self->unsetSocket();
		});
	}

	final public function unsetSocket() {
		$this->consoleLog('Destruct: Event\Listener');
		unset($this->_socket);
		unset($this->_eventEmitter);
	}

	/**
	 * @param array $request
	 */
	public function handle(array $request) {
		$event = $request['event'];
		if ($this->getEventEmitter()->hasEvent($event)) {
			call_user_func_array(array($this->getEventEmitter(), 'emit'), array_merge(array($event), $request['arguments']));
		}
	}

	/**
	 * @return void|\YiiWebSocket\Component
	 */
	public function emit() {}
}