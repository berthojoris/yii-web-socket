<?php
namespace YiiWebSocket;

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
class Session extends Component implements \Serializable, \Iterator {

	/**
	 * @var array
	 */
	protected $_data = array();

	/**
	 * @var string
	 */
	protected $_id;

	public function __construct() {}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @return Session
	 */
	public function add($key, $value) {
		$this->_data[$key] = $value;
		return $this;
	}

	/**
	 * @param      $key
	 * @param null $default
	 */
	public function get($key, $default = null) {
		return $this->exists($key) ? $this->_data[$key] : $default;
	}

	/**
	 * @param $key
	 *
	 * @return bool
	 */
	public function exists($key) {
		return array_key_exists($key, $this->_data);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current() {
		return $this->_data[$this->key()];
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind() {
		reset($this->_data);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		next($this->_data);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Checks if current position is valid
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 *       Returns true on success or false on failure.
	 */
	public function valid() {
		return array_key_exists($this->key(), $this->_data);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key() {
		return key($this->_data);
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * String representation of object
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize() {
		return serialize(array(
			'id' => $this->_id,
			'data' => $this->_data
		));
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
		$data = unserialize($serialized);
		$this->_id = $data['id'];
		$this->_data = $data['data'];
	}

	public function __destruct() {
		parent::__destruct();
	}
}
