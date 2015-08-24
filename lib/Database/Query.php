<?php

namespace House\Database;

use House\NotFound;

class Query {

	protected $model;
	protected $table;

	protected $delete;
	protected $select;
	protected $update;
	protected $insert;
	protected $truncate;

	protected $group;
	protected $limit;
	protected $name;
	protected $offset;
	protected $order;
	protected $values = array();
	protected $where = array();
	protected $whereParams = array();

	// default constructor
	public function __construct(array $config = array()) {
		foreach ($config as $var => $val) {
			$this->{$var} = $val;
		}
	}

	// Readable properties, but not writeable
	public function __get($var) {
		return $this->{$var};
	}

	// Every property has a method
	public function __call($fn, $args) {
		$this->{$fn} = isset($args[0]) ? $args[0] : true;
		return $this;
	}

	public function all() {
		return $this->run();
	}

	public function find($db = null) {
		if ($obj = $this->one()) {
			return $obj;
		}
		throw new NotFound;
	}

	public function one() {
		$this->limit(1);
		$result = $this->run();
		if (count($result)) {
			return current($result);
		}
	}

	public function run() {
		return Cluster::nodeFor($this->model)->query($this);
	}

	public function where(/* polymorphic */) {
		$args = func_get_args();
		$cond = array_shift($args);
		$this->where[] = $cond;
		$this->whereParams[] = $args;
		return $this;
	}

	public function andWhere($cond, $p1 = null) {
		if ($this->where) {
			$this->where[] = 'AND';
		}
		return is_null($p1) 
			? $this->where($cond) 
			: call_user_func_array(array($this, 'where'), func_get_args());
	}

	public function orWhere($cond, $p1 = null) {
		if ($this->where) {
			$this->where[] = 'OR';
		}
		return is_null($p1) 
			? $this->where($cond) 
			: call_user_func_array(array($this, 'where'), func_get_args());
	}

	public function limit($limit, $offset = null) {
		$this->limit = $limit;
		if ($offset) {
			$this->offset = $offset;
		}
	}
}

?>