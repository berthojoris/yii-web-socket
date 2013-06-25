<?php
namespace YiiWebSocket\Package;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/20/13
 * Time: 11:20 PM
 * To change this template use File | Settings | File Templates.
 */
class Client {

	/**
	 * @var array
	 */
	protected $_callbacks = array();

	/**
	 * @param string $json
	 */
	public function unwrap($json, \YiiWebSocket\Socket $socket = null) {
		$config = json_decode($json, true);
		if ($config === false) {
			$socket->getConnection()->sendHttpResponse();
			return false;
		}
		if ($this->isEvent($config)) {
			$this->handleEvent($config, $socket);
			return $config;
		} else if ($this->isCallback($config)) {
			return $this->handleCallback($config, $socket);
		} else {
			$socket->getConnection()->sendHttpResponse();
			$socket->close();
			return false;
		}
	}

	/**
	 * @param string               $event
	 * @param \YiiWebSocket\Socket $socket
	 * @param callable|\Closure    $callback
	 *
	 * @return string
	 */
	public function registerCallback($event, \YiiWebSocket\Socket $socket, $callback) {
		$id = \YiiWebSocket\Package::makeCallbackId($event, $socket);
		echo "Register callback with: id - {$id}\n";
		$this->_callbacks[$id] = $callback;
		$self = $this;
		$socket->onClose(function () use ($self, $id) {
			$self->removeCallback($id);
		});
		return $id;
	}

	public function removeCallback($id) {
		if (array_key_exists($id, $this->_callbacks)) {
			unset($this->_callbacks[$id]);
		}
	}

	/**
	 * @param array                $arguments
	 * @param \YiiWebSocket\Socket $socket
	 *
	 * @return array
	 */
	protected function parseArguments(array $arguments, \YiiWebSocket\Socket $socket) {
		$args = array();
		foreach ($arguments as $argument) {
			if ($argument['type'] == 'callback') {
				$args[] = $this->createCallbackWrapper($argument['value'], $socket);
			} else {
				$args[] = $argument['value'];
			}
		}
		return $args;
	}

	protected function handleEvent(array & $event, \YiiWebSocket\Socket $socket) {
		$event['type'] = 'event';
		$event['arguments'] = $this->parseArguments($event['arguments'], $socket);
	}

	protected function handleCallback(array & $callback, \YiiWebSocket\Socket $socket) {
		$callback['type'] = 'callback';
		$id = $callback['callback'];
		if (array_key_exists($id, $this->_callbacks)) {
			call_user_func_array($this->_callbacks[$id], $this->parseArguments($callback['arguments'], $socket));
			unset($this->_callbacks[$id]);
		}
		return true;
	}

	/**
	 * @param array $config
	 *
	 * @return bool
	 */
	protected function isEvent(array $config) {
		return array_key_exists('event', $config) && !empty($config['event']);
	}

	/**
	 * @param array $config
	 *
	 * @return bool
	 */
	protected function isCallback(array $config) {
		return array_key_exists('callback', $config) && array_key_exists($config['callback'], $this->_callbacks);
	}

	protected function createCallbackWrapper($id, \YiiWebSocket\Socket $socket) {
		return function () use ($id, $socket) {
			$package = \YiiWebSocket\Package::getServer()->wrapCallback($id, func_get_args(), $socket);
			$socket->getConnection()->forceWrite($package);
		};
	}
}