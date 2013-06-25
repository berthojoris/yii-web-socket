<?php
namespace YiiWebSocket\Application;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 12:36 AM
 * To change this template use File | Settings | File Templates.
 */
abstract class AApplication extends \YiiWebSocket\Component {

	/**
	 * @var
	 */
	protected $_sockets;

	/**
	 * @var ProtocolMap
	 */
	private $_protocolMap;

	/**
	 * @return string
	 */
	abstract public function getNamespace();

	public function __construct() {
		$this->_protocolMap = new ProtocolMap($this);
	}

	/**
	 * @param       $action
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function handle($action, array $arguments) {
		$this->consoleLog($action);
		print_r($arguments);
	}

	/**
	 * @param AProtocol $protocol
	 *
	 * @return AApplication
	 */
	final public function addProtocol(AProtocol $protocol) {
		$protocol->setApplication($this);
		$this->_protocolMap->addProtocol($protocol);
		return $this;
	}

	/**
	 * @param $namespace
	 *
	 * @return bool
	 */
	final public function hasProtocol($namespace) {
		return $this->_protocolMap->hasProtocol($namespace);
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	final public function hasAction($id) {
		return $this->_protocolMap->hasAction($id);
	}
}
