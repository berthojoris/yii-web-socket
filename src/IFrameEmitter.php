<?php
namespace YiiWebSocket;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 7/9/13
 * Time: 4:22 PM
 * To change this template use File | Settings | File Templates.
 */
interface IFrameEmitter {

	/**
	 * @param \YiiWebSocket\Package\Frame $frame
	 *
	 * @return mixed
	 */
	public function emitFrame(\YiiWebSocket\Package\Frame $frame);

	/**
	 * @param \YiiWebSocket\Package\Frame $frame
	 *
	 * @return mixed
	 */
	public function broadcastFrame(\YiiWebSocket\Package\Frame $frame);
}
