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
	 * @return Socket
	 */
	public static function getCurrentSocket() {
		return Socket::current();
	}

	public static function setTimeout($callback, $delay = 100) {

	}

	public static function setInterval($callback, $interval = 1000) {

	}
}
