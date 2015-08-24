<?php

namespace House\Database;

abstract class Adapter {

	public $host;
	public $port;
	public $user;
	public $pass;
	public $database;
	public $socket;

	// default constructor
	public function __construct(array $config = array()) {
		foreach ($config as $var => $val) {
			$this->{$var} = $val;
		}
	}

	abstract public function query(Query $query);
}


?>