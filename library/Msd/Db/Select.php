<?php

/**
 * Fucking Zend_Db_Select ...
 * 
 * @author pang
 *
 */

class Msd_Db_Select extends Zend_Db_Select
{
	protected function _join($type, $name, $cond, $cols, $schema = null)
	{
		if (!in_array($type, self::$_joinTypes) && $type != self::FROM) {
			throw new Zend_Db_Select_Exception("Invalid join type '$type'");
		}
	
		if (count($this->_parts[self::UNION])) {
			throw new Zend_Db_Select_Exception("Invalid use of table with " . self::SQL_UNION);
		}

		if (empty($name)) {
			$correlationName = $tableName = '';
		} else if (is_array($name)) {
			// Must be array($correlationName => $tableName) or array($ident, ...)
			foreach ($name as $_correlationName => $_tableName) {
				if (is_string($_correlationName)) {
					// We assume the key is the correlation name and value is the table name
					$tableName = $_tableName;
					$correlationName = $_correlationName;
				} else {
					// We assume just an array of identifiers, with no correlation name
					$tableName = $_tableName;
					$correlationName = $this->_uniqueCorrelation($tableName);
				}
				break;
			}
		} else if ($name instanceof Zend_Db_Expr|| $name instanceof Zend_Db_Select) {
			$tableName = $name;
			$correlationName = $this->_uniqueCorrelation('t');
		} else if (preg_match('/^(.+)\s+AS\s+(.+)$/i', $name, $m)) {
			$tableName = $m[1];
			$correlationName = $m[2];
		} else {
			$tableName = $name;
			$correlationName = $this->_uniqueCorrelation($tableName);
		}

		// Schema from table name overrides schema argument
		if (!is_object($tableName) && false !== strpos($tableName, '.')) {
			$tmp = explode('.', $tableName);
			if (count($tmp)==2) {
				list($schema, $tableName) = explode('.', $tableName);
			} else if (count($tmp)==3) {
				list($a, $b, $tableName) = explode('.', $tableName);
				$schema = $a.'.'.$b;
			}
		}

		$lastFromCorrelationName = null;
		if (!empty($correlationName)) {
			if (array_key_exists($correlationName, $this->_parts[self::FROM])) {
				throw new Zend_Db_Select_Exception("You cannot define a correlation name '$correlationName' more than once");
			}
	
			if ($type == self::FROM) {
				// append this from after the last from joinType
				$tmpFromParts = $this->_parts[self::FROM];
				$this->_parts[self::FROM] = array();
				// move all the froms onto the stack
				while ($tmpFromParts) {
					$currentCorrelationName = key($tmpFromParts);
					if ($tmpFromParts[$currentCorrelationName]['joinType'] != self::FROM) {
						break;
					}
					$lastFromCorrelationName = $currentCorrelationName;
					$this->_parts[self::FROM][$currentCorrelationName] = array_shift($tmpFromParts);
				}
			} else {
				$tmpFromParts = array();
			}
			$this->_parts[self::FROM][$correlationName] = array(
					'joinType'      => $type,
					'schema'        => $schema,
					'tableName'     => $tableName,
					'joinCondition' => $cond
			);
			while ($tmpFromParts) {
				$currentCorrelationName = key($tmpFromParts);
				$this->_parts[self::FROM][$currentCorrelationName] = array_shift($tmpFromParts);
			}
		}

		// add to the columns from this joined table
		if ($type == self::FROM && $lastFromCorrelationName == null) {
			$lastFromCorrelationName = true;
		}
		$this->_tableCols($correlationName, $cols, $lastFromCorrelationName);
	
		return $this;
	}	

    protected function _renderFrom($sql)
    {
        if (empty($this->_parts[self::FROM])) {
            $this->_parts[self::FROM] = $this->_getDummyTable();
        }

        $from = array();

        foreach ($this->_parts[self::FROM] as $correlationName => $table) {
            $tmp = '';

            $joinType = ($table['joinType'] == self::FROM) ? self::INNER_JOIN : $table['joinType'];

            if (! empty($from)) {
                $tmp .= ' ' . strtoupper($joinType) . ' ';
            }

            $tmp .= ($table['schema'] ? $table['schema'].'.' : '');
            $tmp .= ($table['tableName']).($correlationName ? ' AS '.$correlationName : '');

            if (!empty($from) && ! empty($table['joinCondition'])) {
                $tmp .= ' ' . self::SQL_ON . ' ' . $table['joinCondition'];
            }

            $from[] = $tmp;
        }

        // Add the list of all joins
        if (!empty($from)) {
            $sql .= ' ' . self::SQL_FROM . ' ' . implode("\n", $from);
        }

        return $sql;
    }

    private function _uniqueCorrelation($name)
    {
        if (is_array($name)) {
            $c = end($name);
        } else {
            $dot = strrpos($name,'.');
            $c = ($dot === false) ? $name : substr($name, $dot+1);
        }
        for ($i = 2; array_key_exists($c, $this->_parts[self::FROM]); ++$i) {
            $c = $name . '_' . (string) $i;
        }
        return $c;
    }
    
}