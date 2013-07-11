<?php
namespace YiiWebSocket\Connection;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 7/9/13
 * Time: 5:18 PM
 * To change this template use File | Settings | File Templates.
 */
class Type {

	/**
	 * @var AHandshake
	 */
	protected $_handshake;

	/**
	 * @var ADataConverter
	 */
	protected $_dataConverter;

	/**
	 * @var AConnectionDeterminer
	 */
	protected $_connectionDeterminer;

	/**
	 * @var int
	 */
	protected $_priority = 0;

	/**
	 * @param int $priority
	 */
	public function __construct($priority = 0) {
		if (is_numeric($priority)) {
			$this->_priority = (int) $priority;
		}
	}

	/**
	 * @return int
	 */
	public function getPriority() {
		return $this->_priority;
	}

	/**
	 * @param AHandshake $handshake
	 *
	 * @return Type
	 */
	public function setHandshake(AHandshake $handshake) {
		$this->_handshake = $handshake;
		return $this;
	}

	/**
	 * @param ADataConverter $converter
	 *
	 * @return Type
	 */
	public function setDataConverter(ADataConverter $converter) {
		$this->_dataConverter = $converter;
		return $this;
	}

	/**
	 * @param AConnectionDeterminer $determiner
	 *
	 * @return Type
	 */
	public function setConnectionDeterminer(AConnectionDeterminer $determiner) {
		$this->_connectionDeterminer = $determiner;
		return $this;
	}

	/**
	 * @return AHandshake
	 */
	public function getHandshake() {
		return $this->_handshake;
	}

	/**
	 * @return ADataConverter
	 */
	public function getDataConverter() {
		return $this->_dataConverter;
	}

	/**
	 * @return AConnectionDeterminer
	 */
	public function getConnectionDeterminer() {
		return $this->_connectionDeterminer;
	}

	/**
	 * @return bool
	 */
	public function isValid() {
		return
				is_object($this->_handshake) &&
				is_object($this->_dataConverter) &&
				is_object($this->_connectionDeterminer);
	}
}
