<?php

namespace House;

/**
 * To prevent name collisions (and name-calling),
 * this class is abstract. It may be extended for
 * your particular application.
 */
abstract class Assets {
	
	protected static $assets = array();

	public static function assets(array $assets) {
		static::$assets = $assets;
	}
	
	function js($name) {
		$name = array_shift($args);
		if (isset(static::$assets[$fn][$name])) {
			foreach (static::$assets[$fn][$name] as $jsFile) {
				echo '<script type="text/javascript" src="' . $jsFile . '"></script>';
			}
		}
	}

	function css($name) {
		$name = array_shift($args);
		if (isset(static::$assets[$fn][$name])) {
			foreach (static::$assets[$fn][$name] as $cssFile) {
				echo '<link rel="stylesheet" href="' . $cssFile . '">';
			}
		}
	}
}

?>