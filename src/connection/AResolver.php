<?php
namespace YiiWebSocket\Connection;
/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/14/13
 * Time: 2:03 PM
 * To change this template use File | Settings | File Templates.
 */
abstract class AResolver extends \YiiWebSocket\Component {

	/**
	 * @param            $data
	 * @param Connection $connection
	 *
	 * @return AHandshake
	 */
	abstract public function resolve($data, Connection $connection);
}
