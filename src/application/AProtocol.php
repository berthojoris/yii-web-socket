<?php
namespace YiiWebSocket\Application;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 12:40 AM
 * To change this template use File | Settings | File Templates.
 */
abstract class AProtocol extends \YiiWebSocket\Component {

	/**
	 * @var string
	 */
	protected $_namespace;

	/**
	 * @var AApplication
	 */
	private $_application;

	/**
	 * @param AApplication $application
	 *
	 * @return AProtocol
	 */
	final public function setApplication(AApplication $application) {
		$this->_application = $application;
		return $this;
	}

	/**
	 * @return AApplication
	 */
	final public function getApplication() {
		return $this->_application;
	}

	/**
	 * @return string
	 */
	final public function getNamespace() {
		if ($this->_namespace === null) {
			$this->_namespace = strtolower(get_class($this));
		}
		return $this->_namespace;
	}
}
