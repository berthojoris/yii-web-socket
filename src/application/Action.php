<?php
namespace YiiWebSocket\Application;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 1:06 AM
 * To change this template use File | Settings | File Templates.
 */
class Action {

	/**
	 * @var string
	 */
	private $_id;

	/**
	 * @var ProtocolMap
	 */
	private $_map;

	/**
	 * @var AProtocol
	 */
	private $_protocol;

	/**
	 * @var \ReflectionMethod
	 */
	private $_method;

	/**
	 * @param ProtocolMap       $map
	 * @param AProtocol         $protocol
	 * @param \ReflectionMethod $method
	 */
	public function __construct(ProtocolMap $map, AProtocol $protocol, \ReflectionMethod $method) {
		$this->_map = $method;
		$this->_protocol = $protocol;
		$this->_method = $method;

		if ($protocol->getNamespace() == '') {
			$this->_id = $protocol->getNamespace() . '.' . $method->getName();
		} else {
			$this->_id = $method->getName();
		}
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * @param array $arguments
	 * @throws \Exception
	 *
	 * @return mixed
	 */
	public function invoke(array $arguments = array()) {
		$this->validateArguments($arguments);
		return call_user_func_array(array($this->_protocol, $this->_method->getName()), $arguments);
	}

	/**
	 * @param array $arguments
	 *
	 * @throws \Exception
	 */
	private function validateArguments(array &$arguments) {
		$numberOfArguments = count($arguments);
		if ($numberOfArguments < $this->_method->getNumberOfRequiredParameters()) {
			throw new \Exception('Invalid number of required parameters in action: ' . $this->getId());
		}
	}
}
