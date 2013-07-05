<?php
namespace YiiWebSocket;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 9:34 PM
 * To change this template use File | Settings | File Templates.
 */
class Process extends Component {

	/**
	 * @var Server
	 */
	protected static $_server;

	/**
	 * @param Server $server
	 */
	public static function setServer(Server $server) {
		self::$_server = $server;
	}

	/**
	 * @return Server
	 */
	public static function getServer() {
		return self::$_server;
	}

	/**
	 * @return Socket
	 */
	public static function getCurrentSocket() {
		return Socket::current();
	}

	/**
	 * Set timeout
	 *
	 * @param \Closure|callable $callback
	 * @param int $interval in seconds
	 */
	public static function setTimeout($callback, $interval = 1) {
		self::getServer()->getLoop()->addTimer($interval, $callback);
	}

	/**
	 * Set interval for $callback
	 *
	 * @param \Closure|callable $callback
	 * @param int $interval
	 */
	public static function setInterval($callback, $interval = 1) {
		self::getServer()->getLoop()->addPeriodicTimer($interval, $callback);
	}
}
