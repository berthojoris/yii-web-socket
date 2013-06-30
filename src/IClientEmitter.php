<?php
namespace YiiWebSocket;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/26/13
 * Time: 10:48 PM
 * To change this template use File | Settings | File Templates.
 */
interface IClientEmitter {

	/**
	 * @return mixed
	 */
	public function emit();

	/**
	 * Send to all clients
	 *
	 * @return mixed
	 */
	public function broadcast();
}
