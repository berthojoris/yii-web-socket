<?php
namespace YiiWebSocket\Connection;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/20/13
 * Time: 12:24 PM
 * To change this template use File | Settings | File Templates.
 */
abstract class ADataConverter extends \YiiWebSocket\Component {

	const RETURN_STATE_WAITING_DATA = 'wait-data';
	const RETURN_STATE_SUCCESS = 'success';
	const RETURN_STATE_NO_ACTION = 'no_actions';

	/**
	 * @var mixed
	 */
	public $data;

	public function encode() {

	}

	public function decode() {

	}
}
