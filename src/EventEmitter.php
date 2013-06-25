<?php

namespace YiiWebSocket;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/13/13
 * Time: 11:03 PM
 * To change this template use File | Settings | File Templates.
 */
class EventEmitter {

	/**
	 * @var array
	 */
	private $_events = array();

	/**
	 * @var object
	 */
	private $_parent;

	/**
	 * @param $parent
	 */
	public function __construct($parent) {
		$this->_parent = $parent;
	}

	/**
	 * @param string $event
	 *
	 * @return string
	 */
	public static function resolveEventName($event) {
		$name = array();
		$len = strlen($event);
		$i = 0;
		while ($i < $len) {
			$char = $event[$i];
			if (ctype_upper($char)) {
				$name[] = '.';
				$char = strtolower($char);
			}
			$name[] = $char;
			$i++;
		}
		if ($name[0] == '.') {
			array_shift($name);
		}
		return implode('', $name);
	}

	/**
	 * @param $event
	 *
	 * @return bool
	 */
	public function hasEvent($event) {
		return array_key_exists($event, $this->_events);
	}

	/**
	 * @param string $event
	 * @param callable|\Closure $callback
	 */
	public function on($event, $callback) {
		$event = self::resolveEventName($event);
		if (!$this->hasEvent($event)) {
			$this->_events[$event] = array();
		}
		$this->_events[$event][] = $callback;
		return $this->_parent;
	}

	/**
	 * @param $event
	 */
	public function emit($event) {
		if ($this->hasEvent($event)) {
			$arguments = array_slice(func_get_args(), 1, func_num_args() - 1);
			foreach ($this->_events[$event] as $callback) {
				if (call_user_func_array($callback, $arguments) === false) {
					break;
				}
			}
		}
		return $this->_parent;
	}

	public function free() {
		unset($this->_events);
		unset($this->_parent);
	}

	public function __destruct() {
		echo 'Destruct: EventEmitter of: ' . get_class($this->_parent) . "\n";
		$this->free();
	}
}
