<?php

namespace House\Database;

abstract class Model {

	protected static $autoIncrement = 'id';
	protected static $primaryKey = ['id'];
	protected static $table;
	protected static $db;

	// Update? (vs insert)
	protected $_stored;
	
	public function __construct(array $attr = array()) {
		$this->updateAttributes($attr);
	}

	public static function create($attr = array()) {
		$className = get_class($this);
		$obj = new $className($attr);
		$obj->save();
		return $obj;
	}

	public static function find($where) {
		return static::where($where)->find();
	}

	public static function fetch($id) {
		return static::where([ 'id' => $id ])->find();
	}

	public static function query($config) {
		$config = array_merge([
			'model' => get_called_class(),
			'table' => static::table(),
		], $config);
		return new Query($config);
	}

	public static function table() {
		if (!static::$table) {
			static::$table = lcfirst(get_called_class()) . 's';
		}
		return static::$table;
	}

	public static function where($where) {
		return static::query([
			'where' => $where,
		]);
	}

	public function attributes($set = null) {
		return get_public_object_vars($this);
	}

	public function clusterNode() {
		return $this::$db;
	}

	public function delete() {
		return $this::query([
			'delete' => true,
			'where' => $this->primaryKey(), 
		])->run();
	}

	public function primaryKey() {
		$pk = [];
		foreach (static::$primaryKey as $column) {
			$pk[$column] = isset($this->$column) ? $this->$column : null;
		}
		return $pk;
	}

	public function save($validate = true) {

		// Validate
		if ($validate) {
			$this->validate();
		}

		// Update
		if ($this->_stored) {
			$result = $this::query([
				'update' => true,
				'values' => $this->attributes(),
			])->run();
		}

		// Insert
		else {
			$result = $this::query([
				'insert' => true,
				'values' => $this->attributes(),
			])->run();

			// Update the ID if needed
			// (isn't this kind of dumb overall? guid?)
			if ($result && $this::$autoIncrement) {
				$column = $this::$autoIncrement;
				$this->$column = $result->db()->insertId();
			}
		}

		return $result;
	}


	public function updateAttributes($attributes = array()) {
		foreach ($attributes as $var => $val) {
			$this->{$var} = $val;
		}
		return $this;
	}

	/**
	 * @throws Invalid
	 */
	public function validate() {}
}

if (!function_exists('get_public_object_vars')) {
	function get_public_object_vars($obj) {
		return get_object_vars($obj);
	}
}

?>