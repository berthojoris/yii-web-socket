<?php
namespace YiiWebSocket;

use YiiWebSocket\Extension\Data;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 10:40 AM
 * To change this template use File | Settings | File Templates.
 *
 * @method string getId()
 *
 */
class Session extends Component implements \Serializable, \ArrayAccess {

	/**
	 * @var array
	 */
	protected static $_sessions = array();

	/**
	 * @var Data
	 */
	protected $_data;

	/**
	 * @var string
	 */
	protected $_id;

	public function __construct() {
		$this->_data = new Data();
	}

	/**
	 * @return |Extension\Data
	 */
	public function data() {
		return $this->_data;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * String representation of object
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize() {
		$this->_setSystemData('id', $this->_id);
		$serialized = serialize($this->_data->toArray());
		return $serialized;
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
		if (!($this->_data instanceof Data)) {
			$this->_data = new Data();
		}
		$this->_data->replaceWith(unserialize($serialized));
		$this->_id = $this->_getSystemData('id');
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to assign the value to.
	 * </p>
	 * @param mixed $value  <p>
	 *                      The value to set.
	 * </p>
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->_data->set($offset, $value);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to retrieve.
	 * </p>
	 *
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		return $this->_data->get($offset);
	}


	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Whether a offset exists
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset <p>
	 *                      An offset to check for.
	 * </p>
	 *
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 *       The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset) {
		return $this->_data->exists($offset);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to unset.
	 * </p>
	 *
	 * @return void
	 */
	public function offsetUnset($offset) {
		$this->_data->remove($offset);
	}


	/**
	 * @param $key
	 * @param $value
	 */
	private function _setSystemData($key, $value) {
		$this->_data->set('__system@#' . $key, $value);
	}

	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	private function _getSystemData($key) {
		return $this->_data->get('__system@#' . $key);
	}
}
