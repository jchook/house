<?php

namespace House;

use Exception;

class Cluster {

	const DEFAULT_NODE = 'default';

	protected static $config = array();
	protected static $assignments = array();

	public static function config(array $config = array()) {
		static::$config = $config;
	}

	public static function assign(array $assignments = array()) {
		static::$assignments = array_merge(static::$assignments, $assignments);
	}

	public static function nodeFor($model) {
		$className = get_class($model);		
		$nodeName = isset(static::$assignments[$className]) ? static::$assignments[$className] : null;
		return static::node($nodeName);
	}

	public static function node($name = null) {
		$name or ($name = static::DEFAULT_NODE);
		if (isset(static::$config[$name])) {
			if (is_string(static::$config[$name])) {
				// beware of recursion
				return static::node(static::$config[$name]);
			}
			if (!isset(static::$config[$name]['obj'])) {
				$config = static::$config[$name];
				$class = $config['class'];
				unset($config['class']);
				static::$config[$name]['obj'] = new $class($config);
			}
			return static::$config[$name]['obj'];
		}
		throw new Exception('Cluster node ' . $name . ' was not found');
	}

}

?>