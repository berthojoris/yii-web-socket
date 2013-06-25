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
	public $headers;

	/**
	 * @var Connection
	 */
	public $connection;

	/**
	 * @return mixed
	 */
	abstract public function getType();

	/**
	 * @return bool
	 */
	abstract public function doHandshake();
}
