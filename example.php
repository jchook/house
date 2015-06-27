<?php

require 'autoload.php';

(new House\Router)
	
	// Before filters
	->before('*', function($req, $resp){
		House\Log::info('Request: ' . $req);
	})

	// Controller
	->get('/', function($req, $resp){
		return 'Hello World';
	})

	// Resource-ish
	->group('/user')
		->get(function(){ /* .. */ })
		->post(function(){ /* .. */ })
		->delete(function(){ /* .. */ })
	->end()

	->group('/api/:version')
		->group('/user')
			->put(function(){})
			->get('/:id', function(){})
			->post('/:id', function(){})
			->delete('/:id', function(){})
		->end()
	->end()

	// Do it.
	->request(new House\Request([ 'method' => 'get', 'path' => '/' ]))
	->respond()
;