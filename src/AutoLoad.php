<?php
namespace YiiWebSocket;

/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 5/31/13
 * Time: 11:07 AM
 * To change this template use File | Settings | File Templates.
 */
class AutoLoad {

	/**
	 * @var string
	 */
	private $baseDir;

	public function __construct() {
		$this->baseDir = __DIR__ . DIRECTORY_SEPARATOR;
	}

	public static function create() {
		$classLoader = new AutoLoad();

		\Yii::registerAutoloader(array($classLoader, 'load'));
	}

	public function load($class) {
		if (strpos($class, 'YiiWebSocket') === 0) {
			$class = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 13));
			$chunk = explode(DIRECTORY_SEPARATOR, $class);
			if (count($chunk) > 1) {
				$class = array_pop($chunk);
				array_walk($chunk, function (&$value) {
					$value = strtolower($value);
				});
				$class = implode(DIRECTORY_SEPARATOR, $chunk) . DIRECTORY_SEPARATOR . $class;
			}
			if (file_exists($this->baseDir . $class . '.php')) {
				include $this->baseDir . $class . '.php';
				return true;
			}
		}
		return false;
	}
}