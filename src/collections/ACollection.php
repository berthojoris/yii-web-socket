<?php
namespace YiiWebSocket\Collections;

use YiiWebSocket\Component;
use YiiWebSocket\IIdentified;
use YiiWebSocket\IClientEmitter;
use YiiWebSocket\IFrameEmitter;


/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/26/13
 * Time: 10:24 PM
 * To change this template use File | Settings | File Templates.
 *
 */
abstract class ACollection extends Component implements IClientEmitter, IFrameEmitter, \Countable, \Iterator {

	/**
	 * @var array
	 */
	protected $_collection = array();

	/**
	 * @var int
	 */
	protected $_itemsCount = 0;

	/**
	 * @param \YiiWebSocket\IIdentified $item
	 */
	public function add(IIdentified $item) {
		$this->_collection[$item->getId()] = $item;
		$this->_itemsCount++;
		$this->_emit('add', $item);
	}

	/**
	 * @param \YiiWebSocket\IIdentified $item
	 */
	public function remove(IIdentified $item) {
		if ($this->exists($item)) {
			unset($this->_collection[$item->getId()]);
			$this->_itemsCount--;
			$this->_emit('remove', $item);
		}
	}

	/**
	 * @param \YiiWebSocket\IIdentified $item
	 *
	 * @return bool
	 */
	public function exists(IIdentified $item) {
		return array_key_exists($item->getId(), $this->_collection);
	}

	/**
	 * Delete collection
	 */
	public function delete() {
		$this->console()->info('Delete collection of: ' . get_class($this));
		$this->_emit('delete', $this);
		$this->_collection = array();
		$this->_eventEmitter = null;
	}

	/**
	 * Filter current collection using callback function
	 *
	 * @param \Closure|callable $callback
	 *
	 * @return ACollection
	 */
	public function filter($callback) {
		$class = get_class($this);
		$collection = new $class();
		/** @var ACollection $collection */
		if (!is_callable($callback) && !($callback instanceof \Closure)) {
			return $collection;
		}
		foreach ($this->_collection as $item) {
			if (call_user_func($callback, $item)) {
				$collection->add($item);
			}
		}
		return $collection;
	}

	/**
	 * @param ACollection $collection
	 *
	 * @return bool
	 */
	public function mergeWith(ACollection $collection) {
		if (get_class($collection) != get_class($this)) {
			return false;
		}
		foreach ($collection as $item) {
			$this->add($item);
		}
		return true;
	}

	/**
	 * @return int
	 */
	public function count() {
		return $this->_itemsCount;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key() {
		return key($this->_collection);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current() {
		return current($this->_collection);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		next($this->_collection);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind() {
		reset($this->_collection);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Checks if current position is valid
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 *       Returns true on success or false on failure.
	 */
	public function valid() {
		return array_key_exists($this->key(), $this->_collection);
	}

	protected function _emit() {
		return call_user_func_array(array($this->getEventEmitter(), 'emit'), func_get_args());
	}
}
