<?php

namespace House;

class Request {

	public $method;
	public $scheme;
	public $host;
	public $path;
	public $params = array();
	public $matches = array();

	public function __construct(array $config = array()) {
		foreach ($config as $var => $val) {
			$this->{$var} = $val;
		}
	}

	public function param($param) {
		if (isset($this->params[$param])) {
			return $this->params[$param];
		}
		if (isset($this->matches[$param])) {
			return $this->matches[$param];
		}
	}

	public function params() {
		return array_merge($this->matches, $this->params);
	}

	public function __toString() {
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