<?php

namespace House;

class Autoloader {

	protected static $resgistered;

	public static function register() {

		// Only register once
		if (static::$resgistered) {
			return;
		}

		// PHP autoload
		spl_autoload_register(function($className){

			// Only load for House
			if (strncmp($className, 'House', 5) !== 0) {
				return;
			}

			// Simple directory structure based on namespace hierarchy
			if (file_exists($file = __DIR__ . '/' . strtr(substr($className, 6), '\\', '/') . '.php')) {
				require $file;
			}
		});
		
		static::$resgistered = true;
	}
}

?>