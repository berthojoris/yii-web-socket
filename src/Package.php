<?php
namespace YiiWebSocket;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/20/13
 * Time: 11:10 PM
 * To change this template use File | Settings | File Templates.
 */
class Package extends Component {

	/**
	 * @var \YiiWebSocket\Package\Client
	 */
	protected static $_client;

	/**
	 * @var \YiiWebSocket\Package\Server
	 */
	protected static $_server;


	/**
	 * @param array $arguments
	 *
	 * @return string
	 */
	public static function wrap(array $arguments, Socket $socket = null) {
		return self::getServer()->wrap($arguments, $socket);
	}

	/**
	 * @param string $json
	 */
	public static function unwrap($json, Socket $socket) {
		return self::getClient()->unwrap($json, $socket);
	}

	/**
	 * @return Package\Client
	 */
	public static function getClient() {
		if (self::$_client === null) {
			self::$_client = new \YiiWebSocket\Package\Client();
		}
		return self::$_client;
	}

	/**
	 * @return Package\Server
	 */
	public static function getServer() {
		if (self::$_server === null) {
			self::$_server = new \YiiWebSocket\Package\Server();
		}
		return self::$_server;
	}

	/**
	 * @param        $event
	 * @param Socket $socket
	 *
	 * @return string
	 */
	public static function makeCallbackId($event, Socket $socket) {
		return $socket->getId() . '|' . $event;
	}
}
