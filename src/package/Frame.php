<?php
namespace YiiWebSocket\Package;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 7/9/13
 * Time: 4:19 PM
 * To change this template use File | Settings | File Templates.
 */
class Frame {

	/**
	 * @var array
	 */
	protected $_arguments;

	/**
	 * @var string
	 */
	protected $_frame = '';

	/**
	 * @var bool
	 */
	protected $_isWrapped = false;

	public function __construct(array $arguments) {
		$this->_arguments = $arguments;
	}

	/**
	 * @return Frame
	 */
	public function encode() {
		$this->_frame = \YiiWebSocket\Package::wrap($this->_arguments);
		$this->_isWrapped = true;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFrame() {
		if (!$this->_isWrapped) {
			$this->encode();
		}
		return $this->_frame;
	}

	/**
	 * @return array
	 */
	public function getArguments() {
		return $this->_arguments;
	}
}
