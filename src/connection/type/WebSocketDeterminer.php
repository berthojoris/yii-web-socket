<?php
namespace YiiWebSocket\Connection\Type;

use YiiWebSocket\Connection\AConnectionDeterminer;
use YiiWebSocket\Connection\Headers;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 7/10/13
 * Time: 11:20 PM
 * To change this template use File | Settings | File Templates.
 */
class WebSocketDeterminer extends AConnectionDeterminer {

	/**
	 * @param Headers $headers
	 *
	 * @return bool
	 */
	public function determine(Headers $headers) {
		return $headers->getHeader('Sec-WebSocket-Version') || $headers->getHeader('Sec-WebSocket-Key');
	}
}
