<?php

namespace House;

class Request {

	public $matches;
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

	function __toString() {
		return implode('', [
			$this->method ? strtoupper($this->method) . ' ' : '', 
			$this->scheme ? $this->scheme . '://' : '',
			$this->host ? $this->host : '',
			$this->path ? $this->path : '',
			$this->params ? '?' . http_build_query($this->params) : '',
		]);
	}
}

?>