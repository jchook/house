<?php

namespace House;

class Response {
	
	protected $body = array();
	protected $code = 200;
	protected $head = array();

	public function body($iterable) {
		$this->body = $iterable;
		return $this;
	}

	public function code($code = null) {
		if (is_numeric($code)) {
			$this->code = $code;
		}
		return $this;
	}

	public function flush() {
		$this->body = array();
		return $this;
	}

	public function getCode() {
		return $this->code;
	}

	public function head($head) {
		$this->head = $head;
		return $this;
	}

	public function header($header) {
		if (is_array($header)) {
			$this->head = array_merge($this->head, $header);
		} else {
			$this->head[] = $header;
		}
		return $this;
	}

	public function respond() {

		// Requires PHP >= 5.4
		http_response_code($this->code);

		// Additional headers
		foreach ($this->head as $header) {
			header($header);
		}

		// Write the body
		foreach ($this->body as $toStringable) {
			echo $toStringable;
		}
	}

	public function write($toStringable) {
		if (is_array($toStringable)) {
			if ($body = array_pop($toStringable)) {
				$this->body[] = $body;
			}
			if ($code = array_shift($toStringable)) {
				$this->code($code);
			}
			if ($head = array_pop($toStringable)) {
				$this->header($head);
			}
		} else {
			$this->body[] = $toStringable;
		}
		return $this;
	}
}

?>