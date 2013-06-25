<?php
namespace YiiWebSocket\Application;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 12:54 AM
 * To change this template use File | Settings | File Templates.
 */
class ProtocolMap extends \YiiWebSocket\Component {

	/**
	 * @var AProtocol[]
	 */
	private $_protocols = array();

	/**
	 * @var Action[]
	 */
	private $_map = array();

	public function __construct(AApplication $application) {

	}

	/**
	 * @param AProtocol $protocol
	 */
	public function addProtocol(AProtocol $protocol) {
		$this->_protocols[$protocol->getNamespace()] = $protocol;
		$this->_buildMap($protocol);
	}

	/**
	 * @param $namespace
	 *
	 * @return bool
	 */
	public function hasProtocol($namespace) {
		return array_key_exists($namespace, $this->_protocols);
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function hasAction($id) {
		return array_key_exists($id, $this->_map);
	}

	/**
	 * @param $id
	 *
	 * @return Action|null
	 */
	public function getAction($id) {
		return $this->hasAction($id) ? $this->_map[$id] : null;
	}

	/**
	 * @param AProtocol $protocol
	 */
	protected function _buildMap(AProtocol $protocol) {
		$rClass = new \ReflectionClass($protocol);
		foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
			$action = new Action($this, $protocol, $method);
			$this->_map[$action->getId()] = $action;
		}
	}
}
