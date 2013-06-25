<?php
namespace YiiWebSocket\Connection;
/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/20/13
 * Time: 12:35 PM
 * To change this template use File | Settings | File Templates.
 */
class DefaultDataConverter extends ADataConverter {

	/**
	 * @return mixed|void
	 */
	public function encode() {
		return func_get_arg(0);
	}

	/**
	 * @return mixed|void
	 */
	public function decode() {
		$this->data = func_get_arg(0);
		return self::RETURN_STATE_SUCCESS;
	}
}
