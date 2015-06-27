<?php

namespace House;

class DatabaseResponse implements \Iterator {

	// Important
	protected $db;
	protected $error;
	protected $query;
	protected $result;
	
	// Iterator stuffs
	protected $numRows = 0;
	protected $current;
	protected $currentPosition = -1;
	protected $currentPositionValid = false;

	public function __construct(array $config = array())
	{
		foreach ($config as $var => $val) {
			$this->{$var} = $val;
		}

		if (!($this->query instanceof Query)) {
			throw new \Exception(__CLASS__ . ' invalid Query object');	
		}

		if (is_resource($this->result)) {
			if ($this->numRows = $this->result->num_rows()) {
				$this->currentPositionValid = true;
				$this->next();
			}
		}
	}

	public function db()
	{
		return $this->db;
	}

	public function count()
	{
		return $this->numRows;
	}

	public function current()
	{
		return $this->current;
	}

	public function freeResult()
	{
		$this->result->free_result();
	}

	public function key()
	{
		return $this->currentPosition >= 0 ? $this->currentPosition : null;
	}

	public function next()
	{
		if ((($this->currentPosition + 1) < $this->numRows) && ($objectValues = $this->result->fetch_assoc())) {
			if ($modelName = $this->query->model) {
				$objectValues['_stored'] = true;
				$this->current = new $modelName($objectValues);
			} else {
				$this->current = $objectValues;
			}
			$this->currentPosition++;
		} else {
			$this->currentPositionValid = false;
		}
	}
	
	public function rewind()
	{
		$this->seek(0);
	}

	public function seek($rowNumber)
	{
		if (($this->numRows > $rowNumber) && ($rowNumber != $this->currentPosition)) {
			$this->result->data_seek($rowNumber);
			$this->currentPosition = $rowNumber - 1;
			$this->next();
		}
		return $this;
	}

	/**
	 * checks if the current position is valid
	 */
	public function valid()
	{
		return $this->currentPositionValid;
	}
}

?>