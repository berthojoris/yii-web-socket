<?php
namespace YiiWebSocket;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/24/13
 * Time: 2:50 PM
 * To change this template use File | Settings | File Templates.
 */
class AccessTokenManager {

	/**
	 * @var array
	 */
	private $_tokens = array();

	/**
	 * @param string $accessToken
	 *
	 * @return AccessTokenManager
	 */
	public function add($accessToken) {
		$this->_tokens[] = $accessToken;
		return $this;
	}

	/**
	 * @param $accessToken
	 *
	 * @return bool
	 */
	public function isValid($accessToken) {
		return in_array($accessToken, $this->_tokens);
	}
}
