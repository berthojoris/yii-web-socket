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
	 * Connection is a web socket connection
	 */
	const CONNECTION_TYPE_WEB_SOCKET = 'WebSocket';

	/**
	 * Connection is a php event
	 */
	const CONNECTION_TYPE_PHP_EVENT = 'PHPEvent';

	/**
	 * Undefined connection
	 */
	const CONNECTION_TYPE_UNDEFINED = null;

	/**
	 * @var AHandshake[]
	 */
	private $_handshakeHandlers = array();

	/**
	 * @param            $data
	 * @param Connection $connection
	 *
	 * @return AHandshake
	 */
	abstract public function resolve($data, Connection $connection);

	/**
	 * @param $type
	 *
	 * @return AHandshake
	 */
	final public function getHandshakeHandler($type) {
		return array_key_exists($type, $this->_handshakeHandlers) ? $this->_handshakeHandlers[$type] : null;
	}

	/**
	 * @param AHandshake $handler
	 *
	 * @return AResolver
	 */
	final public function setHandshakeHandler(AHandshake $handler) {
		$this->_handshakeHandlers[$handler->getType()] = $handler;
		return $this;
	}
}
