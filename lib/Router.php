<?php

namespace House;

class Router {
	
	protected $groups = array();
	protected $routes = array();
	protected $matchedRoutes = array();

	protected static function createRoute($config = array()) {
		return new Route($config);
	}

	function __call($method, $args) {
		$callback = array_pop($args);
		$match = array_merge($this->groups, [array_shift($args)]);
		$this->routes[] = $route = $this::createRoute(['match' => $match]);
		$route->bind($method, $callback);
		return $this;
	}

	/**
	 * @return array of matched/executed routes
	 */
	protected function handleRequest(Request $request, Response $response, $method, array $config = array()) {
		$config = array_merge(['limit' => false], $config);
		$routes = array();

		// Match and execute callbacks
		foreach ($this->matchedRoutes as list($route, $matches)) {
			if ($callbacks = $route->getCallbacks($method)) {
				$routes[] = $route;
				$request->matches = $matches;
				foreach ($callbacks as $callback) {
					if ($result = $callback($request, $response)) {
						$response->write($result);
						if (is_numeric($config['limit']) && (count($routes) >= $config['limit'])) {
							return $routes;
						}
					}
				}
			}
		}

		return $routes;
	}

	protected function matchRoutes(Request $request) {
		$this->matchedRoutes = array();
		foreach ($this->routes as $index => $route) {
			if ($matches = $route->match($request)) {
				$this->matchedRoutes[] = [$route, $matches];
			}
		}
	}

	public function request(Request $request, Response $response = null) {
		
		// Response information
		$response || ($response = new Response());

		try {

	 		// Do the main request
	 		$this->matchRoutes($request);
	 		$this->handleRequest($request, $response, 'before');
	 		$this->handleRequest($request, $response, $request->method, ['limit' => 1]);
	 		$this->handleRequest($request, $response, 'after');

	 	// Optional route-based error handling
	 	} catch (Exception $e) {
	 		$request->exception = $e;
	 		if (!$this->handleRequest($request, $response, 'error', ['limit' => 1])) {
	 			throw $e;
	 		}
	 	}

	 	return $response;
	}

	public function group($match, $callback = null) {
		$this->groups[] = $match;
		if ($callback) {
			$callback($this);
			$this->end();
		}
		return $this;
	}

	public function end() {
		array_pop($this->groups);
		return $this;
	}

	public function route($match) {
		$this->routes[] = $route = $this::createRoute(compact('match'));
		return $route;
	}
}

?>