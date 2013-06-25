<?php
namespace YiiWebSocket\Application;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 12:46 AM
 * To change this template use File | Settings | File Templates.
 */
class Manager extends \YiiWebSocket\Component {

	/**
	 * @var AApplication[]
	 */
	private $_applications = array();

	/**
	 * @param AApplication $application
	 *
	 * @return Manager
	 */
	public function addApplication(AApplication $application) {
		$this->_applications[$application->getNamespace()] = $application;
		return $this;
	}

	/**
	 * @param $namespace
	 *
	 * @return AApplication|null
	 */
	public function getApplication($namespace) {
		if ($this->applicationExists($namespace)) {
			return $this->_applications[$namespace];
		}
		return null;
	}

	/**
	 * @param $namespace
	 *
	 * @return bool
	 */
	public function applicationExists($namespace) {
		return array_key_exists($namespace, $this->_applications);
	}
}
