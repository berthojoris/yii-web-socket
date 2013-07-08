<?php
namespace YiiWebSocket;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/13/13
 * Time: 11:10 PM
 * To change this template use File | Settings | File Templates.
 *
 * @property EventEmitter $eventEmitter
 *
 */
class Component extends \CComponent {

	/**
	 * @var \YiiWebSocket\Helper\Console
	 */
	protected static $_console;

	/**
	 * @var bool
	 */
	protected $_canSetProtected = true;

	/**
	 * @var EventEmitter
	 */
	protected $_eventEmitter;

	/**
	 * @return EventEmitter
	 */
	public function getEventEmitter() {
		if ($this->_eventEmitter === null) {
			$this->_eventEmitter = new EventEmitter($this);
		}
		return $this->_eventEmitter;
	}

	/**
	 * @param string $method
	 * @param array  $arguments
	 *
	 * @return mixed
	 */
	public function __call($method, $arguments) {
		if (strpos($method, 'on') === 0) {
			array_unshift($arguments, substr($method, 2));
			return call_user_func_array(array($this->getEventEmitter(), 'on'), $arguments);
		} else if (strpos($method, 'get') === 0) {
			$_method = substr($method, 3);
			$property = '_' . strtolower($_method);
			if (property_exists($this, $property)) {
				return $this->$property;
			}
			$_method[0] = strtolower($_method[0]);
			$property = '_' . $_method;
			if (property_exists($this, $property)) {
				return $this->$property;
			}
		} else if ($this->_canSetProtected && strpos($method, 'set') === 0) {
			$property = '_' . strtolower(substr($method, 3));
			if (property_exists($this, $property)) {
				$this->$property = array_shift($arguments);
				return $this;
			}
			$_method = substr($method, 3);
			$_method[0] = strtolower($_method[0]);
			$property = '_' . $_method;
			if (property_exists($this, $property)) {
				$this->$property = array_shift($arguments);
				return $this;
			}
		} else {
			return parent::__call($method, $arguments);
		}
		return null;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasEvent($name) {
		return $this->getEventEmitter()->hasEvent($name);
	}

	/**
	 * @param $event
	 * @param $callback
	 *
	 * @return Component
	 */
	public function on($event, $callback) {
		return $this->getEventEmitter()->on($event, $callback);
	}

	/**
	 * @return Component
	 */
	public function emit() {
		return call_user_func_array(array($this->getEventEmitter(), 'emit'), func_get_args());
	}

	public function dumpMemory() {
		$this->console()->info(sprintf('Memory usage: %0.2f', memory_get_usage() / 1024));
	}

	/**
	 * @return Helper\Console
	 */
	public function console() {
		return \YiiWebSocket\Helper\Console::getInstance();
	}

	/**
	 * @return Component
	 */
	public function consoleLog() {
		foreach (func_get_args() as $argument) {
			echo $argument . "\n";
		}
		return $this;
	}

	public function __destruct() {
		$this->consoleLog('Destruct: Component of class - ' . get_class($this));
		unset($this->_eventEmitter);
	}
}