<?php
namespace YiiWebSocket\Extension;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/30/13
 * Time: 11:02 PM
 * To change this template use File | Settings | File Templates.
 */
class Data {

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
}
