<?php

namespace House;

class Response {
	
	protected $body = array();
	protected $code = 200;
	protected $head = array();

	public function body($body) {
		$this->body = is_string($body) ? [$body] : $body;
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

	public function getBody() {
		return $this->body;
	}

	public function getBodyString() {
		$body = '';
		if ($this->body) foreach ($this->body as $toStringable) {
			$body .= $toStringable;
		}
		return $body;
	}

	public function getCode() {
		return $this->code;
	}

	public function getHeaders() {
		return $this->head;
	}

	public function getHeaderString() {
		$header = '';
		if ($this->head) foreach ($this->head as $headerName => $headerValue) {
			$header .= $headerName . ': ' . $headerValue . "\n";
		}
		return rtrim($header);
	}

	public function head($head) {
		$this->head = $head;
		return $this;
	}

	public function header($header) {
		if (is_array($header)) {
			$this->head = array_merge($this->head, $header);
		} elseif (is_string($header)) {
			$this->head[] = $header;
		}
		return $this;
	}

	public function respond() {

		// PHP >= 5.4
		if ($this->code) {
			http_response_code($this->code);
		}

		// Additional headers
		if ($this->head) foreach ($this->head as $header) {
			header($header);
		}

		// Write the body
		if ($this->body) foreach ($this->body as $toStringable) {
			echo $toStringable;
		}
	}

	public function getCodePhrase() {
		switch ($this->code) {
			case 100: return 'Continue';
			case 101: return 'Switching Protocols';
			case 200: return 'OK';
			case 201: return 'Created';
			case 202: return 'Accepted';
			case 203: return 'Non-Authoritative Information';
			case 204: return 'No Content';
			case 205: return 'Reset Content';
			case 206: return 'Partial Content';
			case 300: return 'Multiple Choices';
			case 301: return 'Moved Permanently';
			case 302: return 'Moved Temporarily';
			case 303: return 'See Other';
			case 304: return 'Not Modified';
			case 305: return 'Use Proxy';
			case 400: return 'Bad Request';
			case 401: return 'Unauthorized';
			case 402: return 'Payment Required';
			case 403: return 'Forbidden';
			case 404: return 'Not Found';
			case 405: return 'Method Not Allowed';
			case 406: return 'Not Acceptable';
			case 407: return 'Proxy Authentication Required';
			case 408: return 'Request Time-out';
			case 409: return 'Conflict';
			case 410: return 'Gone';
			case 411: return 'Length Required';
			case 412: return 'Precondition Failed';
			case 413: return 'Request Entity Too Large';
			case 414: return 'Request-URI Too Large';
			case 415: return 'Unsupported Media Type';
			case 500: return 'Internal Server Error';
			case 501: return 'Not Implemented';
			case 502: return 'Bad Gateway';
			case 503: return 'Service Unavailable';
			case 504: return 'Gateway Time-out';
			case 505: return 'HTTP Version not supported';
		}
	}

	public function httpResponseHeader() {
		$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
		return implode(' ', array_filter([$protocol, $this->getCode(), $this->getCodePhrase()]));
	}

	public function httpResponseString() {
		return implode("\n", array_filter([$this->httpResponseHeader(), $this->getHeaderString()])) . "\n\n" . implode('', $this->getBody());
	}

	public function write($toStringable) {

		// Full rack response overwrites
		if (is_array($toStringable)) {
			if ($body = array_pop($toStringable)) {
				$this->body($body); 
			}
			if ($code = array_shift($toStringable)) {
				$this->code($code);
			}
			if ($head = array_pop($toStringable)) {
				$this->header($head);
			}
		}

		// Integers are response codes
		elseif (is_int($toStringable)) {
			$this->code($toStringable);
		}

		// Non-array writes to the body
		else {
			$this->body[] = $toStringable;
		}

		// Chainable
		return $this;
	}
}

?>