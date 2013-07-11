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
	 * @var \SplPriorityQueue
	 */
	protected $_connectionTypes;

	public function __construct() {
		$this->_connectionTypes = new \SplPriorityQueue();
	}

	/**
	 * @param Type $type
	 *
	 * @return Resolver
	 */
	public function setConnectionType(Type $type) {
		if ($type->isValid()) {
			$this->_connectionTypes->insert($type, - $type->getPriority());
		}
		return $this;
	}

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
				$this->_connectionTypes->top();
				$resolved = false;
				/** @var Type $type */
				foreach ($this->_connectionTypes as $type) {
					if ($type->getConnectionDeterminer()->determine($headers)) {
						$connection->setType($type);
						$connection->setHeaders($headers);
						$resolved = true;
						break;
					}
				}
				return $resolved;
			}
		}
		return null;
	}
}
