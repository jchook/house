<?php

require 'autoload.php';

(new House\Router)
	
	// Before filters
	->before('*', function($req, $resp){
		// $resp->code(200);
		// $resp->write('Request: ' . $req);
	})

	// Hello World!
	->get('/', function($req, $resp){
		return 'Hello World!';
	})

	// Hello, <name>!
	->get('/hello/:name', function($req, $resp){
		return 'Hello, ' . $req->param('name') . '!';
	})

	// Grouped routes
	->group('/api/:version')
		->group('/user')
			->put(function(){})
			->get('/:id', function(){})
			->post('/:id', function(){})
			->delete('/:id', function(){})
		->end()
	->end()

	// Perform request
	->request(new House\Request([ 
		'method' => isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'get',
		'path' => isset($_SERVER['DOCUMENT_URI']) ? $_SERVER['DOCUMENT_URI'] : '/',
		'params' => array_merge($_GET, $_POST),
	]))

	// Output response
	->respond()
;