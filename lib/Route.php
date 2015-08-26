<?php

namespace House;

class Route {
	
	protected $callbacks = array();
	protected $match = array();

	function __construct(array $config = array()) {
		$this->config($config);
	}

	function __call($fn, $args) {
		$this->bind($fn, current($args));
	}

	public function bind($method, $callback) {
		$this->callbacks[] = compact('method', 'callback');
	}

	public function config($config) {
		foreach ($config as $var => $val) {
			switch ($var):
				case 'match':
					$this->setMatch($val);
					break;
				default:
					$this->{$var} = $val;
					break;
			endswitch;
		}
	}

	protected function compileMatchRegExp($regexp) {
		return function($path) use ($regexp) {
			$matches = array();
			$matched = preg_match($regexp, $path, $matches);
			return $matches ?: $matched;
		};
	}

	protected function compileMatchExpression($path) {
		$route = $this;
		$path = str_replace('*', '(.*?)', $path);
		$route->names = array();
		return $this->compileMatchRegExp('#^' . preg_replace_callback('#/:([a-zA-Z][a-zA-Z0-9_]*)#', function($matches) use ($route) {
			// http://www.regular-expressions.info/named.html
			return '/' . '(?P<' . $matches[1] . '>[^/?]+)';
		}, $path) . '#');
	}

	protected function compileMatch($path) {

		$match = array();
		
		// Simple match
		if (is_string($path)) {

			// regex
			if ($path[0] == '#') {
				$match[] = $this->compileMatchRegExp($path);
			}

			// splat
			elseif ($path == '*') {
				$match[] = true;
			}

			// quasi-regex
			elseif ((false !== stripos($path, ':')) || (false !== stripos($path, '*'))) {
				$match[] = $this->compileMatchExpression($path);
			}

			// exact match
			else {
				$match[] = $path;
			}
		} 
		
		// Composite match
		elseif (is_array($path)) {
			$match = array();
			foreach ($path as $matchComponent) {
				$match = array_merge($match, $this->compileMatch($matchComponent));
			}
		}

		return $match;
	}

	public function getCallbacks($method) {
		$callbacks = array();
		foreach ($this->callbacks as $cb) {
			if (is_array($cb['method']) ? in_array($method, $cb['method']) : ($cb['method'] == $method)) {
				$callbacks[] = $cb['callback'];
			}
		}
		return $callbacks;
	}

	public function match(Request $request) {
		$matches = array();
		$superMatches = array('');
		$matchablePortion = $request->path;

		foreach ($this->match as $pattern) {

			// Always-on match
			if ($pattern === true) {
				$matches = [$matchablePortion];
			}

			// Pure string match
			elseif (is_string($pattern)) {
				$matches = strncmp($pattern, $matchablePortion, strlen($pattern)) == 0 ? [$pattern] : false;
			}

			// Regular expressions are compiled to callbacks
			// but you can also supply your own callback for
			// matching the request. Notice the param order.
			elseif (is_callable($pattern)) {
				$matches = $pattern($matchablePortion, $request);
			}

			// If the pattern didn't match, it's all a wash
			if (!$matches) {
				return false;
			}

			// Merge in matches
			elseif (is_array($matches) && isset($matches[0])) {
				$matchablePortion = substr($matchablePortion, strlen($matches[0]));
				$matches[0] = $superMatches[0] . $matches[0];
				$superMatches = array_merge($superMatches, $matches);
			}
		}

		// If there's any string left-over, it didn't match.
		if ($matchablePortion) {
			return false;
		}

		// This might be a little funny for multi-match scenarios
		// But right now it's kind of the best solution I have so
		return $superMatches;
	}

	public function setMatch($match) {
		$this->match = $this->compileMatch($match);
	}
}

?>