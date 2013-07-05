<?php
namespace YiiWebSocket;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/8/13
 * Time: 11:22 PM
 * To change this template use File | Settings | File Templates.
 *
 * @method Config setHost(string $host)
 * @method Config setPort(int $port)
 *
 * @method string   getHost()
 * @method int      getPort()
 * @method \YiiWebSocket\Application\Manager getApplicationManager()
 *
 */
class Config {

	const DEBUG_LEVEL_ALL = 32;
	const DEBUG_LEVEL_ERROR =8;
	const DEBUG_LEVEL_WARN = 4;
	const DEBUG_LEVEL_INFO = 2;
	const DEBUG_LEVEL_MESSAGE = 1;

	/**
	 * @var bool
	 */
	public $debug = true;

	/**
	 * @var int
	 */
	public $debugLevel = self::DEBUG_LEVEL_ALL;

	/**
	 * @var string
	 */
	protected $_host = '127.0.0.1';

	/**
	 * @var int
	 */
	protected $_port = 3002;

	/**
	 * @var \YiiWebSocket\Connection\AResolver
	 */
	protected $_connectionResolver;

	/**
	 * @var array
	 */
	protected $_origins = array();

	/**
	 * @var \YiiWebSocket\Application\Manager
	 */
	protected $_applicationManager;

	/**
	 * @var AccessTokenManager
	 */
	protected $_accessTokenManager;

	/**
	 * @param string $domain
	 *
	 * @return Config
	 */
	public function addOrigin($domain) {
		array_push($this->_origins, $domain);
		if ($domain == '127.0.0.1') {
			if (!in_array('localhost', $this->_origins)) {
				array_push($this->_origins, 'localhost');
			}
		}
		return $this;
	}

	/**
	 * @param $domain
	 *
	 * @return bool
	 */
	public function hasOrigin($domain) {
		return in_array($domain, $this->_origins);
	}

	/**
	 * @param Application\AApplication $application
	 *
	 * @return Config
	 */
	public function setApplicationManager(\YiiWebSocket\Application\Manager $manager) {
		$this->_applicationManager = $manager;
		return $this;
	}

	/**
	 * @return AccessTokenManager
	 */
	public function getAccessTokenManager() {
		if ($this->_accessTokenManager === null) {
			$this->_accessTokenManager = new AccessTokenManager();
		}
		return $this->_accessTokenManager;
	}

	/**
	 * @param Connection\AResolver $resolver
	 *
	 * @return Config
	 */
	public function setConnectionResolver(\YiiWebSocket\Connection\AResolver $resolver) {
		$this->_connectionResolver = $resolver;
		return $this;
	}

	/**
	 * @return Connection\AResolver|Connection\Resolver
	 */
	public function getConnectionResolver() {
		if ($this->_connectionResolver === null) {
			$this->_connectionResolver = new \YiiWebSocket\Connection\Resolver();
		}
		return $this->_connectionResolver;
	}

	/**
	 * @param $method
	 * @param $arguments
	 *
	 * @return null|Config
	 */
	public function __call($method, $arguments) {
		if (strpos($method, 'get') === 0) {
			$_method = substr($method, 3);
			$property = '_' . strtolower($_method);
			if (property_exists($this, $property)) {
				return $this->$property;
			}
			$_method[0] = strtolower($_method[0]);
			$property = '_' . $_method;
			if (property_exists($this, $property)) {
				return $this->$property;
			}
		} else if (strpos($method, 'set') === 0) {
			$property = '_' . strtolower(substr($method, 3));
			if (property_exists($this, $property)) {
				$this->$property = array_shift($arguments);
				return $this;
			}
		}
		return null;
	}
}