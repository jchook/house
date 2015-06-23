<?php

namespace House;

class Request {

	public $method;
	public $scheme;
	public $host;
	public $path;
	public $params = array();

	function __construct(array $config = array()) {
		foreach ($config as $var => $val) {
			$this->{$var} = $val;
		}
	}

	function addParams($params) {
		if (is_array($params)) {
			$this->params = array_merge($this->params, $params);
		}
	}
}

?>