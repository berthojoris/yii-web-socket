<?php
namespace YiiWebSocket\Helper;
/**
 * Created by JetBrains PhpStorm.
 * User: once
 * Date: 7/1/13
 * Time: 11:46 PM
 * To change this template use File | Settings | File Templates.
 */
class Console {

	/**
	 * @var Console
	 */
	private static $_instance;

	/**
	 * @return Console
	 */
	public static function getInstance() {
		if (self::$_instance === null) {
			self::$_instance = new Console();
		}
		return self::$_instance;
	}

	/**
	 * @var Colors
	 */
	private $_consoleColor;

	public $debug = true;
	public $level = 3;

	public function __construct() {
		$this->_consoleColor = new Colors();
	}

	/**
	 * @return Colors
	 */
	public function getColors() {
		return $this->_consoleColor;
	}

	/**
	 * @param $message
	 */
	public function error($message) {
		if (!$this->debug || $this->level < 3) {
			return;
		}
		echo $this->_consoleColor->getColoredString('ERROR: ', 'red');
		echo $message . "\n";
	}

	/**
	 * @param $message
	 */
	public function warn($message) {
		if (!$this->debug || $this->level < 2) {
			return;
		}
		echo $this->_consoleColor->getColoredString('WARN: ', 'yellow');
		echo $message . "\n";
	}

	public function info($message) {

	}

	/**
	 * @param $message
	 */
	public function log($message) {
		if (!$this->debug || $this->level < 1) {
			return;
		}
		echo $message . "\n";
	}
}

class Colors {

	private $foreground_colors = array();

	private $background_colors = array();

	public function __construct() {
		// Set up shell colors
		$this->foreground_colors['black'] = '0;30';
		$this->foreground_colors['dark_gray'] = '1;30';
		$this->foreground_colors['blue'] = '0;34';
		$this->foreground_colors['light_blue'] = '1;34';
		$this->foreground_colors['green'] = '0;32';
		$this->foreground_colors['light_green'] = '1;32';
		$this->foreground_colors['cyan'] = '0;36';
		$this->foreground_colors['light_cyan'] = '1;36';
		$this->foreground_colors['red'] = '0;31';
		$this->foreground_colors['light_red'] = '1;31';
		$this->foreground_colors['purple'] = '0;35';
		$this->foreground_colors['light_purple'] = '1;35';
		$this->foreground_colors['brown'] = '0;33';
		$this->foreground_colors['yellow'] = '1;33';
		$this->foreground_colors['light_gray'] = '0;37';
		$this->foreground_colors['white'] = '1;37';

		$this->background_colors['black'] = '40';
		$this->background_colors['red'] = '41';
		$this->background_colors['green'] = '42';
		$this->background_colors['yellow'] = '43';
		$this->background_colors['blue'] = '44';
		$this->background_colors['magenta'] = '45';
		$this->background_colors['cyan'] = '46';
		$this->background_colors['light_gray'] = '47';
	}

	// Returns colored string
	public function getColoredString($string, $foreground_color = null, $background_color = null) {
		$colored_string = "";

		// Check if given foreground color found
		if (isset($this->foreground_colors[$foreground_color])) {
			$colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
		}
		// Check if given background color found
		if (isset($this->background_colors[$background_color])) {
			$colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
		}

		// Add string and end coloring
		$colored_string .=  $string . "\033[0m";

		return $colored_string;
	}

	// Returns all foreground color names
	public function getForegroundColors() {
		return array_keys($this->foreground_colors);
	}

	// Returns all background color names
	public function getBackgroundColors() {
		return array_keys($this->background_colors);
	}
}