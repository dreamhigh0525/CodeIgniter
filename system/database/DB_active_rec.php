<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2006, EllisLab, Inc.
 * @license		http://www.codeigniter.com/user_guide/license.html
 * @link		http://www.codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Active Record Class
 *
 * This is the platform-independent base Active Record implementation class.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://www.codeigniter.com/user_guide/database/
 */
class CI_DB_active_record extends CI_DB_driver {

	var $ar_select		= array();
	var $ar_distinct	= FALSE;
	var $ar_from		= array();
	var $ar_join		= array();
	var $ar_where		= array();
	var $ar_like		= array();
	var $ar_groupby		= array();
	var $ar_having		= array();
	var $ar_limit		= FALSE;
	var $ar_offset		= FALSE;
	var $ar_order		= FALSE;
	var $ar_orderby		= array();
	var $ar_set			= array();	
	var $ar_wherein		= array();
	var $ar_aliased_tables		= array();

	/**
	 * DB Prefix
	 *
	 * Prepends a database prefix if one exists in configuration
	 *
	 * @access	public
	 * @param	string	the table
	 * @return	string
	 */	
	function dbprefix($table = '')
	{
		if ($table == '')
		{
			$this->display_error('db_table_name_required');
		}
		
		return $this->dbprefix.$table;
	}

	// --------------------------------------------------------------------

	/**
	 * Select
	 *
	 * Generates the SELECT portion of the query
	 *
	 * @access	public
	 * @param	string
	 * @return	object
	 */
	function select($select = '*', $protect_identifiers = TRUE)
	{
		if (is_string($select))
		{
			$select = explode(',', $select);
		}
	
		foreach ($select as $val)
		{
			$val = trim($val);

			if ($val != '*' && $protect_identifiers !== FALSE)
			{
				$val = $this->_protect_identifiers($val);
			}
		
			if ($val != '')
			{
				$this->ar_select[] = $val;
			}
		}
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Select Max
	 *
	 * Generates a SELECT MAX(field) portion of a query
	 *
	 * @access	public
	 * @param	string	the field
	 * @param	string	an alias
	 * @return	object
	 */
	function select_max($select = '', $alias='')
	{
		if (!is_string($select) || $select == '')
		{
			$this->display_error('db_invalid_query');
		}
	
		$alias = ($alias != '') ? $alias : $select;
	
		$sql = 'MAX('.$this->_protect_identifiers(trim($select)).') AS '.$this->_protect_identifiers(trim($alias));

		$this->ar_select[] = $sql;
		
		return $this;
		
		return $this;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Select Min
	 *
	 * Generates a SELECT MIN(field) portion of a query
	 *
	 * @access	public
	 * @param	string	the field
	 * @param	string	an alias
	 * @return	object
	 */
	function select_min($select = '', $alias='')
	{
		if (!is_string($select) || $select == '')
		{
			$this->display_error('db_invalid_query');
		}
	
		$alias = ($alias != '') ? $alias : $select;
	
		$sql = 'MIN('.$this->_protect_identifiers(trim($select)).') AS '.$this->_protect_identifiers(trim($alias));

		$this->ar_select[] = $sql;
		
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Select Average
	 *
	 * Generates a SELECT AVG(field) portion of a query
	 *
	 * @access	public
	 * @param	string	the field
	 * @param	string	an alias
	 * @return	object
	 */
	function select_avg($select = '', $alias='')
	{
		if (!is_string($select) || $select == '')
		{
			$this->display_error('db_invalid_query');
		}

		$alias = ($alias != '') ? $alias : $select;
	
		$sql = 'AVG('.$this->_protect_identifiers(trim($select)).') AS '.$this->_protect_identifiers(trim($alias));

		$this->ar_select[] = $sql;
		
		return $this;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Select Sum
	 *
	 * Generates a SELECT SUM(field) portion of a query
	 *
	 * @access	public
	 * @param	string	the field
	 * @param	string	an alias
	 * @return	object
	 */
	function select_sum($select = '', $alias='')
	{
		if (!is_string($select) || $select == '')
		{
			$this->display_error('db_invalid_query');
		}

		$alias = ($alias != '') ? $alias : $select;
	
		$sql = 'SUM('.$this->_protect_identifiers(trim($select)).') AS '.$this->_protect_identifiers(trim($alias));

		$this->ar_select[] = $sql;
		
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * DISTINCT
	 *
	 * Sets a flag which tells the query string compiler to add DISTINCT
	 *
	 * @access	public
	 * @param	bool
	 * @return	object
	 */
	function distinct($val = TRUE)
	{
		$this->ar_distinct = (is_bool($val)) ? $val : TRUE;
		return $this;
	}
	
	// --------------------------------------------------------------------

	/**
	 * From
	 *
	 * Generates the FROM portion of the query
	 *
	 * @access	public
	 * @param	mixed	can be a string or array
	 * @return	object
	 */
	function from($from)
	{
		foreach ((array)$from as $val)
		{
			$this->ar_from[] = $this->_protect_identifiers($this->_track_aliases($val));
		}

		return $this;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Join
	 *
	 * Generates the JOIN portion of the query
	 *
	 * @access	public
	 * @param	string
	 * @param	string	the join condition
	 * @param	string	the type of join
	 * @return	object
	 */
	function join($table, $cond, $type = '')
	{		
		if ($type != '')
		{
			$type = strtoupper(trim($type));

			if ( ! in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'), TRUE))
			{
				$type = '';
			}
			else
			{
				$type .= ' ';
			}
		}

		// If a DB prefix is used we might need to add it to the column names
		if ($this->dbprefix)
		{
			$this->_track_aliases($table);

			// First we remove any existing prefixes in the condition to avoid duplicates
			$cond = preg_replace('|('.$this->dbprefix.')([\w\.]+)([\W\s]+)|', "$2$3", $cond);
			
			// Next we add the prefixes to the condition
			$cond = preg_replace('|([\w\.]+)([\W\s]+)(.+)|', $this->dbprefix . "$1$2" . $this->dbprefix . "$3", $cond);
		}	

		$this->ar_join[] = $type.'JOIN '.$this->_protect_identifiers($this->dbprefix.$table, TRUE).' ON '.$cond;
		return $this;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Where
	 *
	 * Generates the WHERE portion of the query. Separates
	 * multiple calls with AND
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @return	object
	 */
	function where($key, $value = NULL, $escape = TRUE)
	{
		return $this->_where($key, $value, 'AND ', $escape);
	}
	
	// --------------------------------------------------------------------

	/**
	 * OR Where
	 *
	 * Generates the WHERE portion of the query. Separates
	 * multiple calls with OR
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @return	object
	 */
	function or_where($key, $value = NULL, $escape = TRUE)
	{
		return $this->_where($key, $value, 'OR ', $escape);
	}

	// --------------------------------------------------------------------

	/**
	 * orwhere() is an alias of or_where()
	 * this function is here for backwards compatibility, as
	 * orwhere() has been deprecated
	 */
	function orwhere($key, $value = NULL, $escape = TRUE)
	{
		return $this->or_where($key, $value, $escape);
	}

	// --------------------------------------------------------------------

	/**
	 * Where
	 *
	 * Called by where() or orwhere()
	 *
	 * @access	private
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @return	object
	 */
	function _where($key, $value = NULL, $type = 'AND ', $escape = TRUE)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}
 	 	
		foreach ($key as $k => $v)
		{
			$prefix = (count($this->ar_where) == 0) ? '' : $type;

			if ( ! $this->_has_operator($k) && is_null($key[$k]))
			{
				// value appears not to have been set, assign the test to IS NULL
				$k .= ' IS NULL';
			}
			
			if ( ! is_null($v))
			{

				if ($escape === TRUE)
				{
					// exception for "field<=" keys
					if ($this->_has_operator($k))
					{
						$k =  preg_replace("/([A-Za-z_0-9]+)/", $this->_protect_identifiers('$1'), $k);
					}
					else
					{
						$k = $this->_protect_identifiers($k);
					}
				}

				if ( ! $this->_has_operator($k))
				{
					$k .= ' =';
				}
			
				$v = ' '.$this->escape($v);

			}
						
			$this->ar_where[] = $prefix.$k.$v;
		}
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Where_in
	 *
	 * Generates a WHERE field IN ('item', 'item') SQL query joined with
	 * AND if appropriate
	 *
	 * @access	public
	 * @param	string	The field to search
	 * @param	array	The values searched on

	 * @return	object
	 */
	function where_in($key = NULL, $values = NULL)
	{	 	
		return $this->_where_in($key, $values);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Where_in_or
	 *
	 * Generates a WHERE field IN ('item', 'item') SQL query joined with
	 * OR if appropriate
	 *
	 * @access	public
	 * @param	string	The field to search
	 * @param	array	The values searched on

	 * @return	object
	 */
	function or_where_in($key = NULL, $values = NULL)
	{	 	
		return $this->_where_in($key, $values, FALSE, 'OR ');
	}

	// --------------------------------------------------------------------

	/**
	 * Where_not_in
	 *
	 * Generates a WHERE field NOT IN ('item', 'item') SQL query joined
	 * with AND if appropriate
	 *
	 * @access	public
	 * @param	string	The field to search
	 * @param	array	The values searched on

	 * @return	object
	 */
	function where_not_in($key = NULL, $values = NULL)
	{	 	
		return $this->_where_in($key, $values, TRUE);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Where_not_in_or
	 *
	 * Generates a WHERE field NOT IN ('item', 'item') SQL query joined
	 * with OR if appropriate
	 *
	 * @access	public
	 * @param	string	The field to search
	 * @param	array	The values searched on

	 * @return	object
	 */
	function or_where_not_in($key = NULL, $values = NULL)
	{	 	
		return $this->_where_in($key, $values, FALSE, 'OR ');
	}

	// --------------------------------------------------------------------

	/**
	 * Where_in
	 *
	 * Called by where_in, where_in_or, where_not_in, where_not_in_or
	 *
	 * @access	public
	 * @param	string	The field to search
	 * @param	array	The values searched on
	 * @param	boolean	If the statement whould be IN or NOT IN
	 * @param	string	
	 * @return	object
	 */
	function _where_in($key = NULL, $values = NULL, $not = FALSE, $type = 'AND ')
	{	 	
		if ($key === NULL || !is_array($values))
		{
			return;
		}

		$not = ($not) ? ' NOT ' : '';

		foreach ($values as $value)
		{
			$this->ar_wherein[] = $this->escape($value);
		}

		$prefix = (count($this->ar_where) == 0) ? '' : $type;
 	 			
		$this->ar_where[] = $prefix . $this->_protect_identifiers($key) . $not . " IN (" . implode(", ", $this->ar_wherein) . ") ";

		// reset the array for multiple calls
		$this->ar_wherein = array();
		return $this;
	}
		
	// --------------------------------------------------------------------

	/**
	 * Like
	 *
	 * Generates a %LIKE% portion of the query. Separates
	 * multiple calls with AND
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @return	object
	 */
	function like($field, $match = '', $side = 'both')
	{
		return $this->_like($field, $match, 'AND ', $side);
	}

	// --------------------------------------------------------------------

	/**
	 * Not Like
	 *
	 * Generates a NOT LIKE portion of the query. Separates
	 * multiple calls with AND
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @return	object
	 */
	function not_like($field, $match = '', $side = 'both')
	{
		return $this->_like($field, $match, 'AND ', $side, ' NOT');
	}
		
	// --------------------------------------------------------------------

	/**
	 * OR Like
	 *
	 * Generates a %LIKE% portion of the query. Separates
	 * multiple calls with OR
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @return	object
	 */
	function or_like($field, $match = '', $side = 'both')
	{
		return $this->_like($field, $match, 'OR ', $side);
	}

	// --------------------------------------------------------------------

	/**
	 * OR Not Like
	 *
	 * Generates a NOT LIKE portion of the query. Separates
	 * multiple calls with OR
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @return	object
	 */
	function or_not_like($field, $match = '', $side = 'both')
	{
		return $this->_like($field, $match, 'OR ', $side, 'NOT ');
	}
	
	// --------------------------------------------------------------------

	/**
	 * orlike() is an alias of or_like()
	 * this function is here for backwards compatibility, as
	 * orlike() has been deprecated
	 */
	function orlike($field, $match = '', $side = 'both')
	{
		return $this->orlike($field, $match, $side);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Like
	 *
	 * Called by like() or orlike()
	 *
	 * @access	private
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @return	object
	 */
	function _like($field, $match = '', $type = 'AND ', $side = 'both', $not = '')
	{
		if ( ! is_array($field))
		{
			$field = array($field => $match);
		}
 	
		foreach ($field as $k => $v)
		{		

			$k = $this->_protect_identifiers($k);

			$prefix = (count($this->ar_like) == 0) ? '' : $type;

			$v = $this->escape_str($v);

			if ($side == 'before')
			{
				$this->ar_like[] = $prefix." $k $not LIKE '%{$v}'";
			}
			elseif ($side == 'after')
			{
				$this->ar_like[] = $prefix." $k $not LIKE '{$v}%'";
			}
			else
			{
				$this->ar_like[] = $prefix." $k $not LIKE '%{$v}%'";
			}
		}
		return $this;
	}
	
	// --------------------------------------------------------------------

	/**
	 * GROUP BY
	 *
	 * @access	public
	 * @param	string
	 * @return	object
	 */
	function group_by($by)
	{
		if (is_string($by))
		{
			$by = explode(',', $by);
		}
	
		foreach ($by as $val)
		{
			$val = trim($val);
		
			if ($val != '')
				$this->ar_groupby[] = $this->_protect_identifiers($val);
		}
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * groupby() is an alias of group_by()
	 * this function is here for backwards compatibility, as
	 * groupby() has been deprecated
	 */
	function groupby($by)
	{
		return $this->group_by($by);
	}	

	// --------------------------------------------------------------------

	/**
	 * Sets the HAVING value
	 *
	 * Separates multiple calls with AND
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	function having($key, $value = '')
	{
		return $this->_having($key, $value, 'AND ');
	}
	
	// --------------------------------------------------------------------

	/**
	 * Sets the OR HAVING value
	 *
	 * Separates multiple calls with OR
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	function orhaving($key, $value = '')
	{
		return $this->_having($key, $value, 'OR ');
	}
	
	// --------------------------------------------------------------------

	/**
	 * Sets the HAVING values
	 *
	 * Called by having() or orhaving()
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	function _having($key, $value = '', $type = 'AND ')
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}
	
		foreach ($key as $k => $v)
		{
			$prefix = (count($this->ar_having) == 0) ? '' : $type;
			
			if ($v != '')
			{
				$v = ' '.$this->escape($v);
			}
			
			$this->ar_having[] = $prefix.$k.$v;
		}
		return $this;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Sets the ORDER BY value
	 *
	 * @access	public
	 * @param	string
	 * @param	string	direction: asc or desc
	 * @return	object
	 */
	function order_by($orderby, $direction = '')
	{
		if (strtolower($direction) == 'random')
		{
			$orderby = ''; // Random results want or don't need a field name
			$direction = $this->_random_keyword;
		}
		elseif (trim($direction) != '')
		{
			$direction = (in_array(strtoupper(trim($direction)), array('ASC', 'DESC'), TRUE)) ? ' '.$direction : ' ASC';
		}
		
		$this->ar_orderby[] = $this->_protect_identifiers($orderby, TRUE).$direction;
		return $this;
	}
	
	// --------------------------------------------------------------------

	/**
	 * orderby() is an alias of order_by()
	 * this function is here for backwards compatibility, as
	 * orderby() has been deprecated
	 */
	function orderby($orderby, $direction = '')
	{
		return $this->order_by($orderby, $direction);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Sets the LIMIT value
	 *
	 * @access	public
	 * @param	integer	the limit value
	 * @param	integer	the offset value
	 * @return	object
	 */
	function limit($value, $offset = '')
	{
		$this->ar_limit = $value;
		
		if ($offset != '')
			$this->ar_offset = $offset;
		
		return $this;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Sets the OFFSET value
	 *
	 * @access	public
	 * @param	integer	the offset value
	 * @return	object
	 */
	function offset($value)
	{
		$this->ar_offset = $value;
		return $this;
	}
	
	// --------------------------------------------------------------------

	/**
	 * The "set" function.  Allows key/value pairs to be set for inserting or updating
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @param	boolean
	 * @return	object
	 */
	function set($key, $value = '', $escape = TRUE)
	{
		$key = $this->_object_to_array($key);
	
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}	

		foreach ($key as $k => $v)
		{
			if ($escape === FALSE)
			{
				$this->ar_set[$this->_protect_identifiers($k)] = $v;
			}
			else
			{
				$this->ar_set[$this->_protect_identifiers($k)] = $this->escape($v);
			}

		}
		
		return $this;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Get
	 *
	 * Compiles the select statement based on the other functions called
	 * and runs the query
	 *
	 * @access	public
	 * @param	string	the table
	 * @param	string	the limit clause
	 * @param	string	the offset clause
	 * @return	object
	 */
	function get($table = '', $limit = null, $offset = null)
	{
		if ($table != '')
		{
			$this->_track_aliases($table);
			$this->from($table);
		}
		
		if ( ! is_null($limit))
		{
			$this->limit($limit, $offset);
		}
			
		$sql = $this->_compile_select();

		$result = $this->query($sql);
		$this->_reset_select();
		return $result;
	}

	/**
	 * "Count All Results" query
	 *
	 * Generates a platform-specific query string that counts all records 
	 * returned by an Active Record query.
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function count_all_results($table = '')
	{
		if ($table != '')
		{
			$this->_track_aliases($table);
			$this->from($table);
		}
		
		$sql = $this->_compile_select($this->_count_string . $this->_protect_identifiers('numrows'));

		$query = $this->query($sql);
		$this->_reset_select();
	
		if ($query->num_rows() == 0)
		{
			return '0';
		}

		$row = $query->row();
		return $row->numrows;
	}

	// --------------------------------------------------------------------

	/**
	 * Get_Where
	 *
	 * Allows the where clause, limit and offset to be added directly
	 *
	 * @access	public
	 * @param	string	the where clause
	 * @param	string	the limit clause
	 * @param	string	the offset clause
	 * @return	object
	 */
	function get_where($table = '', $where = null, $limit = null, $offset = null)
	{
		if ($table != '')
		{
			$this->_track_aliases($table);
			$this->from($table);
		}

		if ( ! is_null($where))
		{
			$this->where($where);
		}
		
		if ( ! is_null($limit))
		{
			$this->limit($limit, $offset);
		}
			
		$sql = $this->_compile_select();

		$result = $this->query($sql);
		$this->_reset_select();
		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * getwhere() is an alias of get_where()
	 * this function is here for backwards compatibility, as
	 * getwhere() has been deprecated
	 */
	function getwhere($table = '', $where = null, $limit = null, $offset = null)
	{
		return $this->get_where($table, $where, $limit, $offset);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Insert
	 *
	 * Compiles an insert string and runs the query
	 *
	 * @access	public
	 * @param	string	the table to retrieve the results from
	 * @param	array	an associative array of insert values
	 * @return	object
	 */
	function insert($table = '', $set = NULL)
	{
		if ( ! is_null($set))
		{
			$this->set($set);
		}
	
		if (count($this->ar_set) == 0)
		{
			if ($this->db_debug)
			{
				return $this->display_error('db_must_use_set');
			}
			return FALSE;
		}

		if ($table == '')
		{
			if ( ! isset($this->ar_from[0]))
			{
				if ($this->db_debug)
				{
					return $this->display_error('db_must_set_table');
				}
				return FALSE;
			}
			
			$table = $this->ar_from[0];
		}

		$sql = $this->_insert($this->_protect_identifiers($this->dbprefix.$table), array_keys($this->ar_set), array_values($this->ar_set));
		
		$this->_reset_write();
		return $this->query($sql);		
	}
	
	// --------------------------------------------------------------------

	/**
	 * Update
	 *
	 * Compiles an update string and runs the query
	 *
	 * @access	public
	 * @param	string	the table to retrieve the results from
	 * @param	array	an associative array of update values
	 * @param	mixed	the where clause
	 * @return	object
	 */
	function update($table = '', $set = NULL, $where = NULL, $limit = NULL)
	{
		if ( ! is_null($set))
		{
			$this->set($set);
		}
	
		if (count($this->ar_set) == 0)
		{
			if ($this->db_debug)
			{
				return $this->display_error('db_must_use_set');
			}
			return FALSE;
		}

		if ($table == '')
		{
			if ( ! isset($this->ar_from[0]))
			{
				if ($this->db_debug)
				{
					return $this->display_error('db_must_set_table');
				}
				return FALSE;
			}
			
			$table = $this->ar_from[0];
		}
		
		if ($where != NULL)
		{
			$this->where($where);
		}

		if ($limit != NULL)
		{
			$this->limit($limit);
		}
		
		$sql = $this->_update($this->_protect_identifiers($this->dbprefix.$table), $this->ar_set, $this->ar_where, $this->ar_orderby, $this->ar_limit);
		
		$this->_reset_write();
		return $this->query($sql);
	}

	// --------------------------------------------------------------------

	/**
	 * Empty Table
	 *
	 * Compiles a delete string and runs "DELETE FROM table"
	 *
	 * @access	public
	 * @param	string	the table to empty
	 * @return	object
	 */
	function empty_table($table = '')
	{
		if ($table == '')
		{
			if ( ! isset($this->ar_from[0]))
			{
				if ($this->db_debug)
				{
					return $this->display_error('db_must_set_table');
				}
				return FALSE;
			}

			$table = $this->ar_from[0];
		}
		else
		{
			$table = $this->_protect_identifiers($this->dbprefix.$table);
		}


		$sql = $this->_delete($table);

		$this->_reset_write();
		
		return $this->query($sql);
	}

	// --------------------------------------------------------------------

	/**
	 * Truncate
	 *
	 * Compiles a truncate string and runs the query
	 * If the database does not support the truncate() command
	 * This function maps to "DELETE FROM table"
	 *
	 * @access	public
	 * @param	string	the table to truncate
	 * @return	object
	 */
	function truncate($table = '')
	{
		if ($table == '')
		{
			if ( ! isset($this->ar_from[0]))
			{
				if ($this->db_debug)
				{
					return $this->display_error('db_must_set_table');
				}
				return FALSE;
			}

			$table = $this->ar_from[0];
		}
		else
		{
			$table = $this->_protect_identifiers($this->dbprefix.$table);
		}


		$sql = $this->_truncate($table);

		$this->_reset_write();
		
		return $this->query($sql);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Delete
	 *
	 * Compiles a delete string and runs the query
	 *
	 * @access	public
	 * @param	mixed	the table(s) to delete from. String or array
	 * @param	mixed	the where clause
	 * @param	mixed	the limit clause
	 * @param	boolean
	 * @return	object
	 */
	function delete($table = '', $where = '', $limit = NULL, $reset_data = TRUE)
	{
		if ($table == '')
		{
			if ( ! isset($this->ar_from[0]))
			{
				if ($this->db_debug)
				{
					return $this->display_error('db_must_set_table');
				}
				return FALSE;
			}

			$table = $this->ar_from[0];
		}
		elseif (is_array($table))
		{
			foreach($table as $single_table)
			{
				$this->delete($single_table, $where, $limit, FALSE);
			}

			$this->_reset_write();
			return;
		}
		else
		{
			$table = $this->_protect_identifiers($this->dbprefix.$table);
		}

		if ($where != '')
		{
			$this->where($where);
		}

		if ($limit != NULL)
		{
			$this->limit($limit);
		}

		if (count($this->ar_where) == 0 && count($this->ar_like) == 0)
		{
			if ($this->db_debug)
			{
				return $this->display_error('db_del_must_use_where');
			}

			return FALSE;
		}		

		$sql = $this->_delete($table, $this->ar_where, $this->ar_like, $this->ar_limit);

		if ($reset_data)
		{
			$this->_reset_write();
		}
		
		return $this->query($sql);
	}

	// --------------------------------------------------------------------

	/**
	 * Use Table - DEPRECATED
	 *
	 * @deprecated	use $this->db->from instead
	 */
	function use_table($table)
	{
		return $this->from($table);
		return $this;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Tests whether the string has an SQL operator
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 */
	function _has_operator($str)
	{
		$str = trim($str);
		if ( ! preg_match("/(\s|<|>|!|=|is null|is not null)/i", $str))
		{
			return FALSE;
		}

		return TRUE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Track Aliases
	 *
	 * Used to track SQL statements written with aliased tables.
	 *
	 * @access	private
	 * @param	string	The table to inspect
	 * @return	string
	 */	
	function _track_aliases($table)
	{
		// if a table alias is used we can recognize it by a space
		if (strpos($table, " ") !== FALSE)
		{
			// if the alias is written with the AS keyowrd, get it out
			$table = preg_replace('/ AS /i', ' ', $table); 

			$this->ar_aliased_tables[] = trim(strrchr($table, " "));
		}

		return $this->dbprefix.$table;
	}

	// --------------------------------------------------------------------

	/**
	 * Filter Table Aliases
	 *
	 * Intelligently removes database prefixes from aliased tables
	 *
	 * @access	private
	 * @param	array	An array of compiled SQL
	 * @return	array	Cleaned up statement with aliases accounted for
	 */	
	function _filter_table_aliases($statements)
	{
		$statements_without_aliases = array();

		foreach ($statements as $k => $v)
		{
			foreach ($this->ar_aliased_tables as $table)
			{
				$statement = preg_replace('/(\w+\.\w+)/', $this->_protect_identifiers('$0'), $v); // makes `table.field`
				$statement = str_replace(array($this->dbprefix.$table, '.'), array($table, $this->_protect_identifiers('.')), $statement);
			}

			$statements[$k] = $statement;
		}

		return $statements;
	}

	// --------------------------------------------------------------------

	/**
	 * Compile the SELECT statement
	 *
	 * Generates a query string based on which functions were used.
	 * Should not be called directly.  The get() function calls it.
	 *
	 * @access	private
	 * @return	string
	 */
	function _compile_select($select_override = FALSE)
	{
		$sql = ( ! $this->ar_distinct) ? 'SELECT ' : 'SELECT DISTINCT ';
	
		$sql .= (count($this->ar_select) == 0) ? '*' : implode(', ', $this->ar_select);

		if ($select_override !== FALSE)
		{
			$sql = $select_override;
		}

		if (count($this->ar_from) > 0)
		{
			$sql .= "\nFROM ";
			$sql .= '(' . implode(', ', $this->ar_from) . ')';
		}

		if (count($this->ar_join) > 0)
		{
			$sql .= "\n";

			// special consideration for table aliases
			if (count($this->ar_aliased_tables) > 0 && $this->dbprefix)
			{
				$sql .= implode("\n", $this->_filter_table_aliases($this->ar_join));
			}
			else
			{
				$sql .= implode("\n", $this->ar_join);
			}

		}

		if (count($this->ar_where) > 0 OR count($this->ar_like) > 0)
		{
			$sql .= "\nWHERE ";
		}

		$sql .= implode("\n", $this->ar_where);
		
		if (count($this->ar_like) > 0)
		{
			if (count($this->ar_where) > 0)
			{
				$sql .= " AND ";
			}

			$sql .= implode("\n", $this->ar_like);
		}
		
		if (count($this->ar_groupby) > 0)
		{

			$sql .= "\nGROUP BY ";
			
			// special consideration for table aliases
			if (count($this->ar_aliased_tables) > 0 && $this->dbprefix)
			{
				$sql .= implode(", ", $this->_filter_table_aliases($this->ar_groupby));
			}
			else
			{
				$sql .= implode(', ', $this->ar_groupby);
			}
		}
		
		if (count($this->ar_having) > 0)
		{
			$sql .= "\nHAVING ";
			$sql .= implode("\n", $this->ar_having);
		}

		if (count($this->ar_orderby) > 0)
		{
			$sql .= "\nORDER BY ";
			$sql .= implode(', ', $this->ar_orderby);
			
			if ($this->ar_order !== FALSE)
			{
				$sql .= ($this->ar_order == 'desc') ? ' DESC' : ' ASC';
			}		
		}
		
		if (is_numeric($this->ar_limit))
		{
			$sql .= "\n";
			$sql = $this->_limit($sql, $this->ar_limit, $this->ar_offset);
		}

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Object to Array
	 *
	 * Takes an object as input and converts the class variables to array key/vals
	 *
	 * @access	public
	 * @param	object
	 * @return	array
	 */
	function _object_to_array($object)
	{
		if ( ! is_object($object))
		{
			return $object;
		}
		
		$array = array();
		foreach (get_object_vars($object) as $key => $val)
		{
			// There are some built in keys we need to ignore for this conversion
			if ( ! is_object($val) && ! is_array($val) && $key != '_parent_name' && $key != '_ci_scaffolding' && $key != '_ci_scaff_table')
  
			{
				$array[$key] = $val;
			}
		}
	
		return $array;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Resets the active record values.  Called by the get() function
	 *
	 * @access	private
	 * @return	void
	 */
	function _reset_select()
	{
		$this->ar_select	= array();
		$this->ar_distinct	= FALSE;
		$this->ar_from		= array();
		$this->ar_join		= array();
		$this->ar_where		= array();
		$this->ar_like		= array();
		$this->ar_groupby	= array();
		$this->ar_having	= array();
		$this->ar_limit		= FALSE;
		$this->ar_offset	= FALSE;
		$this->ar_order		= FALSE;
		$this->ar_orderby	= array();
		$this->ar_wherein	= array();
		$this->ar_aliased_tables = array();
	}
	
	// --------------------------------------------------------------------

	/**
	 * Resets the active record "write" values.
	 *
	 * Called by the insert() update() and delete() functions
	 *
	 * @access	private
	 * @return	void
	 */
	function _reset_write()
	{
		$this->ar_set		= array();
		$this->ar_from		= array();
		$this->ar_where		= array();
		$this->ar_like		= array();
		$this->ar_limit		= FALSE;
		$this->ar_order		= FALSE;
		$this->ar_orderby	= array();
	}
	
}

?>