<?php

namespace House\Database;

use mysqli;
use Exception;
use House\Log;

class MySql extends Adapter {

	protected $link;
	protected $whereParamsKey = 0;

	protected function link() {
		if ($this->link) return $this->link;
		$this->link = new mysqli($this->host, $this->user, $this->pass, $this->database, $this->port, $this->socket);
		if ($this->link->connect_errno) {
			Log::error('mysql connection error: ' . $this->link->connect_errno . ' ' . $this->link->connect_error);
			throw new Exception('Database Connection Error');
		}
		return $this->link;
	}

	protected function buildSet($values) {
		if (is_string($values)) {
			return $values;
		}
		if (is_array($values)) {
			$set = array();
			foreach ($values as $var => $val) {
				$set[] = '`' . $var . '`=' . $this->quote($val);
			}
			return implode(', ', $set);
		}
	}

	protected function buildQuery(Query $query) {
		
		$sql = '';
		
		// DELETE
		if ($query->delete) {
			$sql = 'DELETE FROM `' . $query->table . '`';
		}

		// UPDATE
		elseif ($query->update) {
			$sql = 'UPDATE `' . $query->table . '`';
			
			if ($query->values) {
				$sql .= ' SET ' . $this->buildSet($query->values);
			}
		}

		// INSERT
		elseif ($query->insert) {
			$sql = 'INSERT INTO `' . $query->table . '`';

			if ($query->values) {
				$sql .= ' (`' . implode('`, `', array_keys($query->values)) . '`) VALUES (' . implode(', ', array_map(array($this, 'quote'), $query->values)) . ')'; 
			}

			return $sql;
		}

		// SELECT
		else {
			// build our SQL query
			$sql = 'SELECT ' . ($query->select ?: '*');

			// table name is pluralized from the object name
			$sql .= ' FROM `' . $query->table . '`';
		
			if ($query->index) {
				$sql .= ' USE INDEX (`' . $query->index . '`)';
			}
		}

		if ($query->where) {
			$sql .= ' WHERE ' . implode(' ', array_map(array($this, 'buildWhere'), (array)$query->where));
		}

		if ($query->having) {
			$sql .= ' HAVING ' . $query->having;
		}
		
		if ($query->group) {
			$sql .= ' GROUP BY ' . $query->group;
		}
		
		if ($query->order) {
			$sql .= ' ORDER BY ' . $query->order;
		}

		if ($query->limit > 0) {
			$sql .= ' LIMIT ' . $query->limit;

			if ($query->offset > 0) {
				$sql .= ' OFFSET ' . $query->offset;
			}
		}
		
		return $sql;
	}

	/**
	 * SQL and field names are NOT santitized. Only associated values are escaped.
	 */
	protected function buildWhere($input, &$params = array()) {
		
		if (!is_array($input)) {
			return $input;
		}
		
		$where = array();
		$conjunction = null;
		$defaultConjunction = 'AND';
		
		foreach ($input as $key => $value) {
			$condition = null;

			// Subquery
			if ($value instanceof Query) {
				$condition = '(' . $this->buildQuery($value) . ') ' . $query->name;
			} 

			// Compound condition
			elseif (is_array($value)) {
				$condition = '(' . $this->buildWhere($value, $params) . ')';
			} 

			// Special cases
			elseif (strpos($key, 'NOT LIKE') !== false) {
				$condition = $key . ' \'' . $value . '\'';
			} 

			// Simple comparison
			elseif (is_string($key)) {
				list($field, $comparison) = explode(' ', $key . ' =');
				
				if (is_array($value)) {
					$comparison = ($comparison == '!=' || $comparison == 'NOT') ? 'NOT IN' : 'IN';
					if (strpos($field, ')') !== false) {
						$condition = $field . " $comparison (" . implode(', ', array_map(array($this, 'quote'), $value)) . ')';
					}
					else {
						$condition = '`' . $field . "` $comparison (" . implode(', ', array_map(array($this, 'quote'), $value)) . ')';
					}
				} else {
					//check the condition for MySQL Function applied to field
					if (strpos($field, ')') !== false) {
						$condition = $field . ' ' . $comparison . ' ' . $this->quote($value);
					}
					else {
						$condition = '`' . $field . '` ' . $comparison . ' ' . $this->quote($value);
					}
				}
			} 

			// Explicit SQL
			else if (is_string($value)) {
				if (in_array(strtoupper($value), array('AND', 'OR'))) {
					$conjunction = strtoupper($value);
				} else {
					$strap = array_reverse(explode('?', $value));
					$smarap = array_reverse($params);
					$condition = '';
					while (count($smarap) > 1) {
						$condition = array_pop($smarap) . $this->quote(array_pop($strap));
					}
					$condition .= array_pop($smarap);
				}
			}
			
			if ($condition) {
				if ($where) {
					$where[] = ($conjunction) ? $conjunction : $defaultConjunction;
					$conjunction = null;
				}
				$where[] = $condition;
			}
		}
		
		return implode(' ', $where);
	}

	public function escape($value) {
		return $this->link()->escape_string($value);
	}

	public function quote($string) {
		$unwrapped = array('NOW()', 'NULL');
		if ($string === 0) {
			return '0';
		} elseif (is_null($string)) {
			return 'NULL';
		} elseif (!$string) {
			return '\'\'';
		} elseif (is_numeric($string) || in_array($string, $unwrapped)) {
			return $this->quote($string);
		} else {
			return '\'' . $this->quote($string) . '\'';
		}
	}

	public function insertId() {
		return $this->link()->insert_id;
	}
	
	public function query(Query $query) {
		$db = $this;
		$sql = $this->buildQuery($query);
		$link = $this->link();
		Log::query($sql);
		if ($result = $link->query($sql)) {
			return new Result(compact('db', 'query', 'result'));
		}

		// Error handling
		$error = $link->error_list;
		foreach ($link->error_list as $error) {
			Log::error(implode(' ', $error));
		}

		throw new Exception('Query Error');
	}

}

?>