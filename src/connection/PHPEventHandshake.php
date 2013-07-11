<?php
namespace YiiWebSocket\Connection;

use YiiWebSocket\Helper\Util;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 4:50 PM
 * To change this template use File | Settings | File Templates.
 */
class PHPEventHandshake extends AHandshake {

	/**
	 * @return mixed
	 */
	public function getType() {
		return Resolver::CONNECTION_TYPE_PHP_EVENT;
	}

	/**
	 * @param Headers    $headers
	 * @param Connection $connection
	 *
	 * @return mixed
	 */
	public function doHandshake() {
		if (!$this->checkOrigin() || !$this->checkAccessToken()) {
			$this->connection->close();
			return false;
		}
		return true;
	}

	private function checkAccessToken() {
		$accessToken = $this->headers->getHeader('Access-Token');
		if (!$accessToken || !$this->connection->getServer()->getConfig()->getAccessTokenManager()->isValid($accessToken)) {
			$this->connection->sendHttpResponse(401);
			return false;
		}
		return true;
	}

	/**
	 * @return bool
	 */
	private function checkOrigin() {
		$origin = $this->headers->getHeader('Origin');
		if (!$origin || !Util::checkOrigin($origin)) {
			$this->connection->sendHttpResponse(401);
			return false;
		}
		return true;
	}
}
