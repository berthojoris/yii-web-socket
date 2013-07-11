<?php
namespace YiiWebSocket\Connection\Type;

use YiiWebSocket\Connection\AConnectionDeterminer;
use YiiWebSocket\Connection\Headers;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 7/10/13
 * Time: 11:16 PM
 * To change this template use File | Settings | File Templates.
 */
class PHPEventDeterminer extends AConnectionDeterminer {

	/**
	 * @param Headers $headers
	 *
	 * @return bool|void
	 */
	public function determine(Headers $headers) {
		return $headers->getHeader('PHP-Event') == 'yes';
	}
}
