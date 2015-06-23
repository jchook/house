<?php

namespace House;

class Log {

	protected static $config = array('path' => null, 'whitelist' => array());

	public static function config(array $config = array()) {
		foreach ($config as $var => $val) {
			$this->{$var} = $val;
		}
	}

	static function __callStatic($fn, $args) {
		if (static::$config['whitelist'] && !in_array($fn, static::$config['whitelist'])) {
			return;
		}
		
		// Write information to the nginx error log
		error_log($fn . ' ' . implode(' ', $args), 4);
	}
}

?>