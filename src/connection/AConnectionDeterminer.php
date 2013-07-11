<?php
namespace YiiWebSocket\Connection;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 7/9/13
 * Time: 5:11 PM
 * To change this template use File | Settings | File Templates.
 */
abstract class AConnectionDeterminer {

	/**
	 * @param Headers $headers
	 *
	 * @return bool
	 */
	abstract public function determine(Headers $headers);
}
