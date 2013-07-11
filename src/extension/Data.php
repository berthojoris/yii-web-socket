<?php
namespace YiiWebSocket\Extension;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/30/13
 * Time: 11:02 PM
 * To change this template use File | Settings | File Templates.
 */
class Data implements \Serializable {

	/**
	 * @var array
	 */
	private $_data = array();

	/**
	 * @param int|string $key
	 * @param mixed $value
	 *
	 * @return Data
	 */
	public function set($key, $value) {
		$this->_data[$key] = $value;
		return $this;
	}

	/**
	 * @param int|string|array $key
	 *
	 * @return mixed
	 */
	public function get($key) {
		if ($this->exists($key)) {
			if (is_array($key)) {
				$result = array();
				foreach ($key as $k) {
					$result[$k] = $this->_data[$k];
				}
				return $result;
			} else {
				return $this->_data[$key];
			}
		}
		return null;
	}

	/**
	 * @param int|string|array $key
	 *
	 * @return bool
	 */
	public function exists($key) {
		if (is_array($key)) {
			foreach ($key as $k) {
				if (!array_key_exists($k, $this->_data)) {
					return false;
				}
			}
			return true;
		} else {
			return array_key_exists($key, $this->_data);
		}
	}

	/**
	 * @param $key
	 */
	public function remove($key) {
		if ($this->exists($key)) {
			unset($this->_data[$key]);
		}
	}

	/**
	 * @return array
	 */
	public function toArray() {
		return $this->_data;
	}

	/**
	 * @param array $data
	 *
	 * @return Data
	 */
	public function replaceWith(array $data) {
		$this->_data = $data;
		return $this;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * String representation of object
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize() {
		return serialize($this->_data);
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Constructs the object
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 *
	 * @param string $serialized <p>
	 *                           The string representation of the object.
	 * </p>
	 *
	 * @return mixed the original value unserialized.
	 */
	public function unserialize($serialized) {
		$this->_data = unserialize($serialized);
	}
}
