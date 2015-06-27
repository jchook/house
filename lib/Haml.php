<?php

namespace House;

use MtHaml\Environment;
use MtHaml\Support\Php\Executor;

class Haml {

	protected $path;
	protected $vars;

	protected static $config = array(
		'views' => '',
	);
	protected static $executor;

	protected static function init() {
		if (static::$executor) return;
		$haml = new Environment('php');
		static::$executor = new Executor($haml, static::$config);
	}

	public static function config(array $config) {
		static::$config = array_merge(static::$config, $config);
	}

	public static function fullPath($path) {
		if (strpos('..', $path) !== false) throw new Invalid('Unsafe template path');
		$root = static::$config['views'] ? rtrim(static::$config['views'], '/') . '/' : '';
		return $root . ltrim($path, '/') . '.haml';
	}

	public function __construct($path, $vars = array(), $config = array()) {
		$this->path = $path;
		$this->vars = $vars;

		foreach ($config as $var => $val) {
			$this->{$var} = $val;
		}
		
		$this->vars['__argv__'] = &$this->vars;
	}

	public function __toString() {
		$this::init();
		try {
			return $this::$executor->render($this::fullPath($this->path), $this->vars);
		} catch (\Exception $e) {
			Log::error($e);
			return 'HAML Render Error';
		}
		return '';
	}

}

?>