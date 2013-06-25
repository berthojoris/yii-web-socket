<?php
namespace YiiWebSocket\Package;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/20/13
 * Time: 11:13 PM
 * To change this template use File | Settings | File Templates.
 */
class Server {

	/**
	 * JSON code
	 *
	 * @return string
	 */
	public function wrap(array $arguments, \YiiWebSocket\Socket $socket = null) {
		$container = array();
		$container['event'] = array_shift($arguments);
		$container['arguments'] = array();
		foreach ($arguments as $argument) {
			$container['arguments'][] = $this->prepareArguments($container['event'], $argument, $socket);
		}
		return json_encode($container);
	}

	/**
	 * @param                      $id
	 * @param array                $arguments
	 * @param \YiiWebSocket\Socket $socket
	 *
	 * @return string
	 */
	public function wrapCallback($id, array $arguments, \YiiWebSocket\Socket $socket = null) {
		$container = array(
			'callback' => $id,
			'arguments' => array()
		);
		foreach ($arguments as $argument) {
			$container['arguments'][] = $this->prepareArguments($id, $argument, $socket);
		}
		return json_encode($container);
	}

	/**
	 * @param string $event
	 * @param mixed $argument
	 * @param \YiiWebSocket\Socket $socket
	 */
	protected function prepareArguments($event, $argument, \YiiWebSocket\Socket $socket = null) {
		$details = array(
			'type' => gettype($argument),
			'value' => $argument
		);
		if (is_int($argument) || is_numeric($argument)) {
			$details['type'] = 'number';
		} else if (is_callable($argument) || $argument instanceof \Closure) {
			$details['type'] = 'callback';
			$details['value'] = \YiiWebSocket\Package::getClient()->registerCallback($event, $socket, $argument);
		}
		return $details;
	}
}
