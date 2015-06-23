<?php

namespace House;

abstract class Database {

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