<?php
/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 6/30/13
 * Time: 12:16 PM
 * To change this template use File | Settings | File Templates.
 */
class YiiWebSocket extends CApplicationComponent {

	/**
	 * @var string
	 */
	protected $_assetUrl;

	public function init() {
		parent::init();
	}

	public function registerClientScript() {
		$this->_assetUrl = Yii::app()->assetManager->publish(__DIR__ . DIRECTORY_SEPARATOR . 'assets');
		Yii::app()->clientScript->registerScriptFile($this->_assetUrl . '/js/yii-web-socket.js', CClientScript::POS_END);
	}
}