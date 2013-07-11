<?php
namespace YiiWebSocket\Connection;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 2:50 PM
 * To change this template use File | Settings | File Templates.
 */
abstract class AHandshake {

	/**
	 * @var Headers
	 */
	protected $headers;

	/**
	 * @var Connection
	 */
	protected $connection;

	/**
	 * @param Connection $connection
	 *
	 * @return AHandshake
	 */
	final public function prepare(Connection $connection) {
		$this->connection = $connection;
		$this->headers = $connection->getHeaders();
		return $this;
	}

	/**
	 * Remove links on connection and headers objects
	 */
	final public function clean() {
		//  remove object links
		$this->connection = null;
		$this->headers = null;
	}

	/**
	 * @return bool
	 */
	abstract public function doHandshake();
}
