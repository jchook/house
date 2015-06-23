<?php

namespace House;

class Config {

	protected static $config = array();

	public static function get($var) {
		if (isset(static::$config[$var])) {
			return static::$config[$var];
		}
	}

	public static function set($var, $val = null) {
		if (!is_array($var)) {
			static::$config[$var] = $val;
		} else {
			foreach ($var as $subvar => $subval) {
				static::$config[$subvar] = $subval;
			}
		}
	}
}