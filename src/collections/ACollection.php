<?php
namespace YiiWebSocket\Collections;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/26/13
 * Time: 10:24 PM
 * To change this template use File | Settings | File Templates.
 *
 */
abstract class ACollection extends \YiiWebSocket\Component implements \YiiWebSocket\IClientEmitter {

	/**
	 * @return mixed
	 */
	abstract public function delete();

	protected function emitAdd($item) {
		$this->_emit('add', $item);
	}

	protected function emitRemove($item) {
		$this->_emit('remove', $item);
	}

	protected function _emit() {
		return call_user_func_array(array($this->getEventEmitter(), 'emit'), func_get_args());
	}
}
