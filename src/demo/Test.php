<?php
namespace YiiWebSocket\Demo;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 9:19 PM
 * To change this template use File | Settings | File Templates.
 */
class Test extends \YiiWebSocket\Application\AProtocol {

	/**
	 * @var string
	 */
	protected $_namespace = 'test';

	public function pong() {

	}
}
