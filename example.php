<?php

require 'autoload.php';

(new House\Router)
	
	// Controller
	->get('/', function(){
		return 'Hello World';
	})

	// Do it.
	->request(new House\Request([ 'method' => 'get', 'path' => '/' ]))
	->respond()
;