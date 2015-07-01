<?php

namespace House;

class Request {

	public $method;
	public $scheme;
	public $host;
	public $path;
	public $params = array();
	public $matches = array();
	public $exception;

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

	public function params(array $permit = array()) {
		$params = array_merge($this->matches, $this->params);
		if (!$permit) {
			return $params;
		}
		$permitted = array();
		foreach ($permit as $param) {
			if (isset($params[$param])) {
				$permitted[] = $params[$param];
			}
		}
		return $permitted;
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