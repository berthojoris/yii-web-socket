<?php
namespace YiiWebSocket\Connection;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 2:17 PM
 * To change this template use File | Settings | File Templates.
 */
class Resolver extends AResolver {

	/**
	 * @param            $data
	 * @param Connection $connection
	 *
	 * @return AHandshake
	 */
	public function resolve($data, Connection $connection) {
		if ($data) {
			$headers = new Headers($data, $connection);
			if ($headers->isValid()) {
				if ($this->isWebSocket($headers)) {
					$handler = $this->getHandshakeHandler(self::CONNECTION_TYPE_WEB_SOCKET);
					$connection->setType(self::CONNECTION_TYPE_WEB_SOCKET);
					return $handler;
				} else if ($this->isPHPEventSocket($headers)) {
					$handler = $this->getHandshakeHandler(self::CONNECTION_TYPE_PHP_EVENT);
					$connection->setType(self::CONNECTION_TYPE_PHP_EVENT);
					return $handler;
				}
			}
		}
		return null;
	}

	/**
	 * @param Headers $headers
	 *
	 * @return bool
	 */
	protected function isWebSocket(Headers $headers) {
		return $headers->getHeader('Sec-WebSocket-Version') || $headers->getHeader('Sec-WebSocket-Key');
	}

	/**
	 * @param Headers $headers
	 *
	 * @return string|null
	 */
	protected function isPHPEventSocket(Headers $headers) {
		return $headers->getHeader('PHP-Event') == 'yes';
	}
}
