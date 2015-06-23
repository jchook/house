<?php

namespace House;

class Router {
	
	protected $methods = array();

	public function __construct() {
		$this->addMethod('error', ['aux' => true]);

		// Some defaults
		$this->addMethod('get');
		$this->addMethod('post');
		$this->addMethod('delete');
	}

	public function addMethod($name, array $config = array()) {
		$this->methods[$name] = array_merge(['aux' => false], $config);
		return $this;
 	}

 	public function addRoute($method, $match, $callback) {
 		$this->methods[$method]['routes'][] = new Route(compact('method', 'match', 'callback'));
 	}

 	protected function handleRequest(Request $request, Response $response, $method = null) {
 		
 		$matched = array();
 		$method or ($method = $request->method);

 		// Explicitly allowed methods only
 		if ((!isset($this->methods[$method])) || ! $this->methods[$method]['routes']) {
 			throw new Invalid('Unsupported method');
 		}
 		
 		// Match and execute callbacks
 		foreach ($this->methods[$method]['routes'] as $route) {
 			$result = null;
 			if ($params = $route->match($request->path)) {
 				$request->addParams($params);
 				$cb = $route->getCallback();
 				$result = $cb($request, $response);
 				if ($result !== false) {
 					$response->write($result);
 					if (!$this->methods[$method]['aux']) {
 						$request->routes = $matched;
 						return;
 					}
 					$matched[] = $route;
 				}
 			}
 		}

 		// Always returns an array of matched routes
 		$request->routes = $matched;
 	}

 	public function request(Request $request, Response $response = null) {
 		
 		// Response information
 		$response || ($response = new Response());

 		try {

	 		// Do the main request
	 		$this->handleRequest($request, $response, $request->method);

	 	} catch (Exception $e) {
	 		$request->error = $e;
	 		if ($this->methods['error']['routes']) {
	 			$this->handleRequest($request, $response, 'error');
	 		} else {
	 			throw $e;
	 		}
	 	}

	 	return $response;
 	}

	function __call($fn, $args) {

		// addMethod shortcut
		if (isset($this->methods[$fn])) {
			$this->addRoute($fn, $args[0], $args[1]);
			return $this;
		}

		// Obligitory
		$trace = debug_backtrace();
		trigger_error(
			'Undefined method via __call(): ' . $fn .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE
		);

		return $this;
	}
}

class Route {
	
	protected $match;
	protected $callback;
	protected $names = array();

	function __construct(array $config = array()) {
		$this->config($config);
	}

	public function getCallback() {
		return $this->callback;
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

	function setMatchRegExp($regexp) {
		$this->match = function($path) use ($regexp) {
			$matches = array();
			$matched = preg_match($regexp, $path, $matches);
			if ($this->names) {
				foreach ($this->names as $index => $name) {
					if (isset($matches[$index + 1])) {
						$matches[$name] = $matches[$index + 1];
					}
				}
			}
			return $matches ?: $matched;
		};
	}

	function setMatch($path) {

		$route = $this;

		// regex
		if ($path[0] == '#') {
			$this->setMatchRegExp($path);
		}

		// quasi-regex
		elseif (stripos(':', $path)) {
			$route->names = array();
			$this->setMatchRegExp('#^' . preg_replace_callback('#/:([a-zA-Z_]+)#', function($matches) use ($route) {
				$route->names[] = $matches[1];
				return '/' . '([^/?]+)';
			}, $path) . '$#');
		}

		// splat
		elseif ($path == '*') {
			$this->match = true;
		}

		// everything else
		else {
			$this->match = $path;
		}
	}

	function match($path) {
		if (is_string($this->match)) {
			return strcmp($this->match, $path) == 0;
		}
		if (is_callable($this->match)) {
			$cb = $this->match;
			return $cb($path);
		}
		return ($this->match === true);
	}
}

?>