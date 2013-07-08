<?php
namespace YiiWebSocket\Helper;

use YiiWebSocket\Process;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 7/5/13
 * Time: 5:12 PM
 * To change this template use File | Settings | File Templates.
 */
class Util {

	/**
	 * @param string $origin
	 *
	 * @return bool
	 */
	public static function checkOrigin($origin) {
		$domain = str_replace('http://', '', $origin);
		$domain = str_replace('https://', '', $domain);
		$domain = str_replace('www.', '', $domain);
		$domain = str_replace('/', '', $domain);

		return Process::getServer()->getConfig()->hasOrigin($domain);
	}
}
